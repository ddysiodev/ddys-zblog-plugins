import assert from 'node:assert/strict';
import test from 'node:test';
import { readFile, readdir } from 'node:fs/promises';
import { join } from 'node:path';

const root = process.cwd();

test('plugin manifest matches Z-BlogPHP application structure', async () => {
  const manifest = await read('DdysOpen/plugin.xml');
  assert.match(manifest, /<id>DdysOpen<\/id>/);
  assert.match(manifest, /<include>include\.php<\/include>/);
  assert.match(manifest, /<path>main\.php<\/path>/);
  assert.match(manifest, /<phpver>5\.6<\/phpver>/);
});

test('plugin registers Z-BlogPHP hooks', async () => {
  const include = await read('DdysOpen/include.php');
  assert.match(include, /RegisterPlugin\('DdysOpen'/);
  assert.match(include, /Filter_Plugin_Index_Begin/);
  assert.match(include, /Filter_Plugin_ViewPost_Template/);
  assert.match(include, /Filter_Plugin_Admin_Header/);
  assert.match(include, /Filter_Plugin_Admin_SettingMng_SubMenu/);
});

test('all DDYS display shortcodes are implemented', async () => {
  const fn = await read('DdysOpen/function.php');
  for (const shortcode of [
    'ddys_movies',
    'ddys_latest',
    'ddys_hot',
    'ddys_search',
    'ddys_suggest',
    'ddys_calendar',
    'ddys_movie',
    'ddys_sources',
    'ddys_related',
    'ddys_comments',
    'ddys_collections',
    'ddys_collection',
    'ddys_shares',
    'ddys_share',
    'ddys_requests',
    'ddys_activities',
    'ddys_user',
    'ddys_types',
    'ddys_genres',
    'ddys_regions',
    'ddys_request_form'
  ]) {
    assert.ok(fn.includes(`'${shortcode}'`), shortcode);
  }
});

test('server-side request form is guarded and rate limited', async () => {
  const request = await read('DdysOpen/request.php');
  const fn = await read('DdysOpen/function.php');
  assert.match(request, /DdysOpen_HandleRequestForm/);
  assert.match(fn, /enable_auth_features/);
  assert.match(fn, /enable_request_form/);
  assert.match(fn, /DdysOpen_CheckRateLimit/);
  assert.match(fn, /Authorization: Bearer/);
});

test('docs use correct official website link text', async () => {
  const en = await read('README.md');
  const zh = await read('README.zh-CN.md');
  assert.ok(en.includes('[DDYS](https://ddys.io/)'));
  assert.ok(zh.includes('[低端影视](https://ddys.io/)'));
});

test('repository has no temporary development files', async () => {
  const files = await listFiles(root);
  for (const file of files) {
    assert.doesNotMatch(file.replace(/\\/g, '/'), /(^|\/)(\.env|开发分析|开发文档)|\.(zip|log|bak)$/i);
  }
});

async function read(file) {
  return readFile(join(root, file), 'utf8');
}

async function listFiles(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const out = [];
  for (const entry of entries) {
    if (entry.name === '.git' || entry.name === 'node_modules' || entry.name === 'vendor') {
      continue;
    }
    const full = join(dir, entry.name);
    if (entry.isDirectory()) {
      out.push(...await listFiles(full));
    } else {
      out.push(full);
    }
  }
  return out;
}
