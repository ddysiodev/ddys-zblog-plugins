import assert from 'node:assert/strict';
import test from 'node:test';
import { readFile } from 'node:fs/promises';
import { join } from 'node:path';

const root = process.cwd();

test('manifest follows Z-BlogASP plugin structure', async () => {
  const manifest = await read('DdysOpen/plugin.xml');
  assert.match(manifest, /<id>DdysOpen<\/id>/);
  assert.match(manifest, /<include>include\.asp<\/include>/);
  assert.match(manifest, /<path>main\.asp<\/path>/);
});

test('include registers admin menu and template shortcode filter', async () => {
  const include = await read('DdysOpen/include.asp');
  assert.match(include, /RegisterPlugin\("DdysOpen"/);
  assert.match(include, /Response_Plugin_Admin_Left/);
  assert.match(include, /Response_Plugin_SettingMng_SubMenu/);
  assert.match(include, /Filter_Plugin_TArticle_Export_TemplateTags/);
});

test('server-side proxy caches public DDYS routes', async () => {
  const fn = await read('DdysOpen/function.asp');
  assert.match(fn, /DdysOpen_AllowedRoute/);
  assert.match(fn, /DdysOpen_ProxyResponse/);
  assert.match(fn, /DdysOpen_CacheRead/);
  assert.match(fn, /DdysOpen_CacheWrite/);
});

test('request form keeps API key server-side', async () => {
  const fn = await read('DdysOpen/function.asp');
  const request = await read('DdysOpen/request.asp');
  assert.match(fn, /Authorization/);
  assert.match(fn, /DdysOpen_CheckRateLimit/);
  assert.match(request, /REQUEST_METHOD/);
  assert.match(request, /DdysOpen_RequestResponse/);
});

test('frontend renders widgets without CDN dependencies', async () => {
  const js = await read('DdysOpen/assets/js/frontend.js');
  assert.match(js, /data-ddys-widget/);
  assert.match(js, /fetch\(/);
  assert.doesNotMatch(js, /unpkg|jsdelivr|npm/);
});

async function read(file) {
  return readFile(join(root, file), 'utf8');
}
