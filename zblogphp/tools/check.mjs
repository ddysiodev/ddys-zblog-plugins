import { readdir, readFile } from 'node:fs/promises';
import { join, relative } from 'node:path';

const root = process.cwd();
const required = [
  'README.md',
  'README.zh-CN.md',
  'LICENSE',
  '.gitignore',
  'DdysOpen/plugin.xml',
  'DdysOpen/include.php',
  'DdysOpen/function.php',
  'DdysOpen/main.php',
  'DdysOpen/request.php',
  'DdysOpen/page.php',
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

const shortcodes = [
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
];

const failures = [];

for (const file of required) {
  await mustExist(file);
}

await checkManifest();
await checkHooks();
await checkShortcodes();
await checkDocs();
await checkPhpShape();
await checkForbiddenFiles();
await checkForbiddenText();

if (failures.length) {
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log(JSON.stringify({ ok: true, files: (await listFiles(root)).length, shortcodes: shortcodes.length }, null, 2));

async function mustExist(file) {
  try {
    await read(file);
  } catch {
    failures.push(`Missing required file: ${file}`);
  }
}

async function checkManifest() {
  const xml = await read('DdysOpen/plugin.xml');
  for (const text of ['<id>DdysOpen</id>', '<name>DDYS</name>', '<include>include.php</include>', '<path>main.php</path>', '<phpver>5.6</phpver>']) {
    if (!xml.includes(text)) {
      failures.push(`plugin.xml missing ${text}`);
    }
  }
}

async function checkHooks() {
  const include = await read('DdysOpen/include.php');
  const fn = await read('DdysOpen/function.php');
  for (const text of [
    "RegisterPlugin('DdysOpen'",
    'Filter_Plugin_Index_Begin',
    'Filter_Plugin_ViewPost_Template',
    'Filter_Plugin_Admin_Header',
    'Filter_Plugin_Admin_SettingMng_SubMenu'
  ]) {
    if (!include.includes(text)) {
      failures.push(`include.php missing hook ${text}`);
    }
  }
  for (const text of ['DdysOpen_ApiClient', 'DdysOpen_Cache', 'DdysOpen_Renderer', 'DdysOpen_HandleRequestForm', 'DdysOpen_ParseShortcodes']) {
    if (!fn.includes(text)) {
      failures.push(`function.php missing ${text}`);
    }
  }
}

async function checkShortcodes() {
  const fn = await read('DdysOpen/function.php');
  for (const shortcode of shortcodes) {
    if (!fn.includes(`'${shortcode}'`)) {
      failures.push(`Missing shortcode ${shortcode}`);
    }
  }
}

async function checkDocs() {
  const en = await read('README.md');
  const zh = await read('README.zh-CN.md');
  if (!en.includes('[DDYS](https://ddys.io/)')) {
    failures.push('English README must use DDYS as official website link text.');
  }
  if (!zh.includes('[低端影视](https://ddys.io/)')) {
    failures.push('Chinese README must use 低端影视 as official website link text.');
  }
  for (const text of [en, zh]) {
    if (text.includes('npm install') || text.includes('unpkg.com') || text.includes('jsdelivr')) {
      failures.push('Z-BlogPHP plugin docs should not mention npm/CDN install.');
    }
  }
}

async function checkPhpShape() {
  const files = (await listFiles(root)).filter((file) => file.endsWith('.php'));
  const pairs = { '}': '{', ')': '(', ']': '[' };
  for (const full of files) {
    const rel = relative(root, full).replace(/\\/g, '/');
    const text = await read(rel);
    const lines = text.split(/\r?\n/);
    lines.forEach((line, index) => {
      const single = countUnescaped(line, "'");
      const double = countUnescaped(line, '"');
      if (single % 2 !== 0 || double % 2 !== 0) {
        failures.push(`${rel}:${index + 1} has an unbalanced quote`);
      }
    });
    const stack = [];
    let quote = '';
    let escape = false;
    for (let i = 0; i < text.length; i++) {
      const char = text[i];
      if (quote) {
        if (escape) {
          escape = false;
          continue;
        }
        if (char === '\\') {
          escape = true;
          continue;
        }
        if (char === quote) {
          quote = '';
        }
        continue;
      }
      if (char === '"' || char === "'") {
        quote = char;
        continue;
      }
      if (char === '{' || char === '(' || char === '[') {
        stack.push(char);
      } else if (char === '}' || char === ')' || char === ']') {
        const opener = stack.pop();
        if (opener !== pairs[char]) {
          failures.push(`${rel} has mismatched bracket near offset ${i}`);
          break;
        }
      }
    }
    if (stack.length) {
      failures.push(`${rel} has unclosed bracket(s): ${stack.slice(-5).join('')}`);
    }
  }
}

function countUnescaped(line, quote) {
  let count = 0;
  let escape = false;
  for (const char of line) {
    if (escape) {
      escape = false;
      continue;
    }
    if (char === '\\') {
      escape = true;
      continue;
    }
    if (char === quote) {
      count++;
    }
  }
  return count;
}

async function checkForbiddenFiles() {
  const files = await listFiles(root);
  for (const full of files) {
    const rel = relative(root, full).replace(/\\/g, '/');
    if (/(^|\/)(\.env|开发分析|开发文档)/i.test(rel) || /\.(zip|log|bak)$/i.test(rel) || rel.startsWith('node_modules/')) {
      failures.push(`Forbidden file in repository: ${rel}`);
    }
  }
}

async function checkForbiddenText() {
  const files = await listFiles(root);
  const patterns = ['ghp' + '_', 'npm' + '_', '2026' + 'facai', 'x9k' + 'Nx', 'OpenAI', 'AI Agent', 'GPT', 'Open API', 'Do not ' + 'bundle'];
  for (const full of files) {
    const rel = relative(root, full).replace(/\\/g, '/');
    if (rel === 'tools/check.mjs' || /\.(png|jpg|jpeg|webp|gif)$/i.test(rel)) {
      continue;
    }
    const text = await read(rel);
    for (const pattern of patterns) {
      if (text.includes(pattern)) {
        failures.push(`${rel} contains restricted text pattern ${pattern}`);
      }
    }
  }
}

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
