import { readdir, readFile } from 'node:fs/promises';
import { join, relative } from 'node:path';

const root = process.cwd();
const failures = [];
const required = [
  'README.md',
  'README.zh-CN.md',
  'LICENSE',
  'DdysOpen/plugin.xml',
  'DdysOpen/include.asp',
  'DdysOpen/function.asp',
  'DdysOpen/main.asp',
  'DdysOpen/savesetting.asp',
  'DdysOpen/api.asp',
  'DdysOpen/request.asp',
  'DdysOpen/page.asp',
  'DdysOpen/logo.png',
  'DdysOpen/assets/css/frontend.css',
  'DdysOpen/assets/css/admin.css',
  'DdysOpen/assets/js/frontend.js',
  'DdysOpen/assets/js/admin.js',
  'DdysOpen/assets/images/icon-16.png',
  'DdysOpen/assets/images/icon-32.png',
  'DdysOpen/assets/images/icon-192.png',
  'DdysOpen/assets/images/icon-512.png'
];

for (const file of required) await mustExist(file);

const manifest = await read('DdysOpen/plugin.xml');
const include = await read('DdysOpen/include.asp');
const fn = await read('DdysOpen/function.asp');
const js = await read('DdysOpen/assets/js/frontend.js');

for (const text of ['<id>DdysOpen</id>', '<include>include.asp</include>', '<path>main.asp</path>']) {
  if (!manifest.includes(text)) failures.push(`plugin.xml missing ${text}`);
}
for (const text of ['RegisterPlugin("DdysOpen"', 'ActivePlugin_DdysOpen', 'Response_Plugin_Admin_Left', 'Filter_Plugin_TArticle_Export_TemplateTags']) {
  if (!include.includes(text)) failures.push(`include.asp missing ${text}`);
}
for (const text of ['DdysOpen_ProxyResponse', 'DdysOpen_RequestResponse', 'DdysOpen_ParseShortcodes', 'Authorization', 'DdysOpen_CheckRateLimit']) {
  if (!fn.includes(text)) failures.push(`function.asp missing ${text}`);
}
for (const text of ['data-ddys-widget', 'data-ddys-request-form', 'fetch(', 'URLSearchParams']) {
  if (!js.includes(text)) failures.push(`frontend.js missing ${text}`);
}

const files = await listFiles(root);
for (const full of files) {
  const rel = relative(root, full).replace(/\\/g, '/');
  if (/\.(zip|log|bak)$/i.test(rel) || rel.startsWith('cache/') || rel.includes('/cache/')) {
    failures.push(`Forbidden file: ${rel}`);
  }
  if (rel.endsWith('.asp')) {
    const text = await read(rel);
    if ((text.match(/<%/g) || []).length !== (text.match(/%>/g) || []).length) {
      failures.push(`${rel} has unbalanced ASP delimiters`);
    }
    if (text.includes('IIf(')) failures.push(`${rel} uses IIf`);
  }
}

if (failures.length) {
  for (const failure of failures) console.error(`- ${failure}`);
  process.exit(1);
}

console.log(JSON.stringify({ ok: true, files: files.length }, null, 2));

async function mustExist(file) {
  try {
    await readFile(join(root, file));
  } catch {
    failures.push(`Missing required file: ${file}`);
  }
}

async function read(file) {
  return readFile(join(root, file), 'utf8');
}

async function listFiles(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const out = [];
  for (const entry of entries) {
    if (entry.name === '.git' || entry.name === 'node_modules' || entry.name === 'cache') continue;
    const full = join(dir, entry.name);
    if (entry.isDirectory()) out.push(...await listFiles(full));
    else out.push(full);
  }
  return out;
}
