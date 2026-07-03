import assert from 'node:assert/strict';
import test from 'node:test';
import { readFile, readdir } from 'node:fs/promises';
import { join } from 'node:path';

const root = process.cwd();

test('repository root exposes php and asp plugin directories', async () => {
  const names = (await readdir(root, { withFileTypes: true })).map((entry) => entry.name);
  assert.ok(names.includes('zblogphp'));
  assert.ok(names.includes('zblogasp'));
  assert.ok(!names.includes('DdysOpen'));
});

test('Z-BlogPHP plugin remains installable as DdysOpen', async () => {
  const manifest = await read('zblogphp/DdysOpen/plugin.xml');
  const include = await read('zblogphp/DdysOpen/include.php');
  assert.match(manifest, /<id>DdysOpen<\/id>/);
  assert.match(manifest, /<include>include\.php<\/include>/);
  assert.match(include, /RegisterPlugin\('DdysOpen'/);
});

test('Z-BlogASP plugin remains installable as DdysOpen', async () => {
  const manifest = await read('zblogasp/DdysOpen/plugin.xml');
  const include = await read('zblogasp/DdysOpen/include.asp');
  assert.match(manifest, /<id>DdysOpen<\/id>/);
  assert.match(manifest, /<include>include\.asp<\/include>/);
  assert.match(include, /RegisterPlugin\("DdysOpen"/);
});

test('Z-BlogASP has server-side proxy and request form protection', async () => {
  const fn = await read('zblogasp/DdysOpen/function.asp');
  const request = await read('zblogasp/DdysOpen/request.asp');
  assert.match(fn, /DdysOpen_AllowedRoute/);
  assert.match(fn, /DdysOpen_ProxyResponse/);
  assert.match(fn, /Authorization/);
  assert.match(fn, /DdysOpen_CheckRateLimit/);
  assert.match(request, /REQUEST_METHOD/);
});

test('both plugins carry copied DDYS icon sizes', async () => {
  for (const prefix of ['zblogphp', 'zblogasp']) {
    for (const size of ['16', '32', '192', '512']) {
      const file = await readBinary(`${prefix}/DdysOpen/assets/images/icon-${size}.png`);
      assert.ok(file.length > 0, `${prefix} icon-${size}`);
    }
  }
});

async function read(file) {
  return readFile(join(root, file), 'utf8');
}

async function readBinary(file) {
  return readFile(join(root, file));
}
