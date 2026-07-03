import { readdir, readFile } from 'node:fs/promises';
import { join, relative } from 'node:path';

const root = process.cwd();
const failures = [];

const required = [
  'README.md',
  'README.zh-CN.md',
  'LICENSE',
  '.gitignore',
  'zblogphp/DdysOpen/plugin.xml',
  'zblogphp/DdysOpen/include.php',
  'zblogphp/DdysOpen/function.php',
  'zblogphp/DdysOpen/main.php',
  'zblogasp/DdysOpen/plugin.xml',
  'zblogasp/DdysOpen/include.asp',
  'zblogasp/DdysOpen/function.asp',
  'zblogasp/DdysOpen/main.asp',
  'zblogasp/DdysOpen/savesetting.asp',
  'zblogasp/DdysOpen/api.asp',
  'zblogasp/DdysOpen/request.asp',
  'zblogasp/DdysOpen/page.asp',
  'zblogasp/DdysOpen/logo.png',
  'zblogasp/DdysOpen/assets/css/frontend.css',
  'zblogasp/DdysOpen/assets/css/admin.css',
  'zblogasp/DdysOpen/assets/js/frontend.js',
  'zblogasp/DdysOpen/assets/js/admin.js',
  'zblogasp/DdysOpen/assets/images/icon-16.png',
  'zblogasp/DdysOpen/assets/images/icon-32.png',
  'zblogasp/DdysOpen/assets/images/icon-192.png',
  'zblogasp/DdysOpen/assets/images/icon-512.png'
];

for (const file of required) {
  await mustExist(file);
}

await checkRootShape();
await checkPhpPlugin();
await checkAspPlugin();
await checkForbiddenFiles();
await checkForbiddenText();

if (failures.length) {
  for (const failure of failures) {
    console.error(`- ${failure}`);
  }
  process.exit(1);
}

console.log(JSON.stringify({ ok: true, files: (await listFiles(root)).length }, null, 2));

async function checkRootShape() {
  const names = new Set((await readdir(root, { withFileTypes: true })).map((entry) => entry.name));
  if (!names.has('zblogphp') || !names.has('zblogasp')) {
    failures.push('Repository root must contain zblogphp and zblogasp directories.');
  }
  if (names.has('DdysOpen')) {
    failures.push('Repository root must not contain a direct DdysOpen plugin directory.');
  }
}

async function checkPhpPlugin() {
  const manifest = await read('zblogphp/DdysOpen/plugin.xml');
  const include = await read('zblogphp/DdysOpen/include.php');
  const fn = await read('zblogphp/DdysOpen/function.php');
  for (const text of ['<id>DdysOpen</id>', '<include>include.php</include>', '<path>main.php</path>']) {
    if (!manifest.includes(text)) failures.push(`Z-BlogPHP plugin.xml missing ${text}`);
  }
  for (const text of ['RegisterPlugin', 'ActivePlugin_DdysOpen', 'Filter_Plugin_ViewPost_Template', 'Filter_Plugin_Admin_Header']) {
    if (!include.includes(text)) failures.push(`Z-BlogPHP include.php missing ${text}`);
  }
  for (const text of ['DdysOpen_ApiClient', 'DdysOpen_Cache', 'DdysOpen_Renderer', 'DdysOpen_HandleRequestForm']) {
    if (!fn.includes(text)) failures.push(`Z-BlogPHP function.php missing ${text}`);
  }
  await checkBalancedText('zblogphp/DdysOpen/function.php');
  await checkBalancedText('zblogphp/DdysOpen/main.php');
  await checkBalancedText('zblogphp/DdysOpen/page.php');
  await checkBalancedText('zblogphp/DdysOpen/request.php');
}

async function checkAspPlugin() {
  const manifest = await read('zblogasp/DdysOpen/plugin.xml');
  const include = await read('zblogasp/DdysOpen/include.asp');
  const fn = await read('zblogasp/DdysOpen/function.asp');
  const api = await read('zblogasp/DdysOpen/api.asp');
  const request = await read('zblogasp/DdysOpen/request.asp');
  const js = await read('zblogasp/DdysOpen/assets/js/frontend.js');
  for (const text of ['<id>DdysOpen</id>', '<include>include.asp</include>', '<path>main.asp</path>']) {
    if (!manifest.includes(text)) failures.push(`Z-BlogASP plugin.xml missing ${text}`);
  }
  for (const text of ['RegisterPlugin("DdysOpen"', 'ActivePlugin_DdysOpen', 'Response_Plugin_Admin_Left', 'Filter_Plugin_TArticle_Export_TemplateTags']) {
    if (!include.includes(text)) failures.push(`Z-BlogASP include.asp missing ${text}`);
  }
  for (const text of [
    'DdysOpen_ProxyResponse',
    'DdysOpen_AllowedRoute',
    'DdysOpen_RequestResponse',
    'Authorization',
    'DdysOpen_ParseShortcodes',
    'DdysOpen_FrontendAssets',
    'Invalid route parameters',
    'InStr(response, """success"":false")',
    'If lastTime > nowTime Then lastTime = 0'
  ]) {
    if (!fn.includes(text)) failures.push(`Z-BlogASP function.asp missing ${text}`);
  }
  if (!api.includes('Response.ContentType = "application/json"') || !api.includes('DdysOpen_ProxyResponse')) {
    failures.push('Z-BlogASP api.asp must return JSON through the proxy response.');
  }
  if (!request.includes('REQUEST_METHOD') || !request.includes('DdysOpen_RequestResponse')) {
    failures.push('Z-BlogASP request.asp must guard POST and call DdysOpen_RequestResponse.');
  }
  for (const text of ['data-ddys-widget', 'data-ddys-request-form', 'URLSearchParams', 'fetch(', 'renderResourceGroups', 'safeResourceUrl']) {
    if (!js.includes(text)) failures.push(`Z-BlogASP frontend.js missing ${text}`);
  }
  const aspFiles = (await listFiles(join(root, 'zblogasp'))).filter((file) => file.endsWith('.asp'));
  for (const full of aspFiles) {
    const rel = relative(root, full).replace(/\\/g, '/');
    const text = await read(rel);
    if ((text.match(/<%/g) || []).length !== (text.match(/%>/g) || []).length) {
      failures.push(`${rel} has unbalanced ASP delimiters`);
    }
    if (text.includes('IIf(')) {
      failures.push(`${rel} uses IIf, which is avoided for VBScript compatibility`);
    }
  }
}

async function checkBalancedText(file) {
  const text = await read(file);
  const lines = text.split(/\r?\n/);
  lines.forEach((line, index) => {
    const single = countUnescaped(line, "'");
    const double = countUnescaped(line, '"');
    if (single % 2 !== 0 || double % 2 !== 0) {
      failures.push(`${file}:${index + 1} has an unbalanced quote`);
    }
  });
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
    if (/(^|\/)(\.env|node_modules|vendor|cache)(\/|$)/i.test(rel) || /\.(zip|log|bak)$/i.test(rel)) {
      failures.push(`Forbidden file in repository: ${rel}`);
    }
  }
}

async function checkForbiddenText() {
  const files = await listFiles(root);
  const patterns = ['ghp' + '_', 'npm' + '_', '2026' + 'facai', 'x9k' + 'Nx', 'OpenAI', 'AI Agent', 'GPT'];
  for (const full of files) {
    const rel = relative(root, full).replace(/\\/g, '/');
    if (rel.endsWith('.png') || rel === 'tools/check.mjs' || rel.endsWith('/tools/check.mjs')) continue;
    const text = await read(rel);
    for (const pattern of patterns) {
      if (text.includes(pattern)) {
        failures.push(`${rel} contains restricted text pattern ${pattern}`);
      }
    }
  }
}

async function mustExist(file) {
  try {
    await read(file);
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
    if (entry.name === '.git' || entry.name === 'node_modules' || entry.name === 'vendor' || entry.name === 'cache') continue;
    const full = join(dir, entry.name);
    if (entry.isDirectory()) {
      out.push(...await listFiles(full));
    } else {
      out.push(full);
    }
  }
  return out;
}
