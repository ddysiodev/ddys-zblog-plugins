<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';

$zbp->Load();
require_once dirname(__FILE__) . '/function.php';
DdysOpen_RequireAdmin();

$notice = '';
$noticeType = 'good';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('CheckIsRefererValid')) {
        CheckIsRefererValid();
    }
    $action = DdysOpen_Post('ddys_action', '');
    if ($action === 'save_settings') {
        DdysOpen_SaveSettingsFromPost();
        $zbp->SetHint('good');
        Redirect('main.php?tab=settings');
    }
    if ($action === 'flush_cache') {
        DdysOpen_CacheFlush();
        $zbp->SetHint('good');
        Redirect('main.php?tab=cache');
    }
    if ($action === 'test_api') {
        $client = DdysOpen_Client();
        $result = $client->get('/types', array(), array('no_cache' => true));
        if (DdysOpen_IsError($result)) {
            $zbp->SetHint('bad');
        } else {
            $zbp->SetHint('good');
        }
        Redirect('main.php?tab=diagnostics');
    }
}

$tab = DdysOpen_Choice(DdysOpen_Get('tab', 'settings'), array('settings', 'shortcodes', 'pages', 'cache', 'diagnostics'), 'settings');
$settings = DdysOpen_GetSettings();
$definitions = DdysOpen_ShortcodeDefinitions();
$blogtitle = 'DDYS';

require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain" class="ddys-zbp-admin">
  <div id="ShowBlogHint"><?php if (function_exists('GetBlogHint')) { GetBlogHint(); } ?></div>
  <div class="divHeader"><img src="assets/images/icon-32.png" width="24" height="24" alt=""> DDYS</div>
  <div class="SubMenu">
    <?php DdysOpen_AdminTab('settings', '鍩虹璁剧疆', $tab); ?>
    <?php DdysOpen_AdminTab('shortcodes', 'Shortcodes', $tab); ?>
    <?php DdysOpen_AdminTab('pages', '鍏紑椤甸潰', $tab); ?>
    <?php DdysOpen_AdminTab('cache', '缂撳瓨', $tab); ?>
    <?php DdysOpen_AdminTab('diagnostics', '璇婃柇', $tab); ?>
    <a class="m-right" href="https://ddys.io/" target="_blank" rel="noopener"><span>DDYS</span></a>
  </div>
  <div id="divMain2">
    <?php if ($tab === 'settings') : ?>
      <form method="post" action="main.php?tab=settings">
        <?php DdysOpen_CsrfInput(); ?>
        <input type="hidden" name="ddys_action" value="save_settings">
        <table class="tableFull tableBorder ddys-zbp-settings">
          <tr><th colspan="2">鎺ュ彛閰嶇疆</th></tr>
          <?php DdysOpen_AdminTextRow('api_base_url', 'API Base URL', $settings['api_base_url'], 'Default: https://ddys.io/api/v1. You can also use your own Worker proxy URL.'); ?>
          <?php DdysOpen_AdminTextRow('site_base_url', 'Source Site URL', $settings['site_base_url'], 'Default source links point to https://ddys.io.'); ?>
          <?php DdysOpen_AdminNumberRow('timeout', 'Request Timeout', $settings['timeout'], 1, 30, 'Recommended: 8 to 15 seconds.'); ?>
          <tr><th colspan="2">缂撳瓨绛栫暐</th></tr>
          <?php DdysOpen_AdminNumberRow('default_cache_ttl', 'Default Cache TTL', $settings['default_cache_ttl'], 0, 604800, 'Use 0 to disable caching.'); ?>
          <?php DdysOpen_AdminNumberRow('dictionary_cache_ttl', 'Dictionary and Calendar Cache', $settings['dictionary_cache_ttl'], 0, 604800, 'Types, genres, regions, and calendar data.'); ?>
          <?php DdysOpen_AdminNumberRow('fresh_cache_ttl', 'Latest and Hot Cache', $settings['fresh_cache_ttl'], 0, 604800, 'Short cache is recommended for fresh content.'); ?>
          <?php DdysOpen_AdminNumberRow('list_cache_ttl', 'List Cache', $settings['list_cache_ttl'], 0, 604800, 'Movies, search, shares, collections, and requests.'); ?>
          <?php DdysOpen_AdminNumberRow('detail_cache_ttl', 'Detail Cache', $settings['detail_cache_ttl'], 0, 604800, 'Movie details, sources, related movies, and share details.'); ?>
          <?php DdysOpen_AdminNumberRow('community_cache_ttl', 'Community Cache', $settings['community_cache_ttl'], 0, 604800, 'Comments, requests, and activities.'); ?>
          <tr><th colspan="2">灞曠ず璁剧疆</th></tr>
          <?php DdysOpen_AdminSelectRow('theme', '涓婚', $settings['theme'], array('auto' => '璺熼殢绯荤粺', 'light' => '娴呰壊', 'dark' => '娣辫壊')); ?>
          <?php DdysOpen_AdminSelectRow('layout', '榛樿甯冨眬', $settings['layout'], array('grid' => '缃戞牸', 'list' => '鍒楄〃', 'compact' => '绱у噾')); ?>
          <?php DdysOpen_AdminNumberRow('columns', 'Default Columns', $settings['columns'], 1, 6, 'Used by the built-in grid layout.'); ?>
          <?php DdysOpen_AdminSelectRow('target', 'Link Target', $settings['target'], array('_blank' => 'New window', '_self' => 'Current window')); ?>
          <?php DdysOpen_AdminCheckRow('show_source_link', '鏄剧ず鏉ユ簮閾炬帴', $settings['show_source_link']); ?>
          <?php DdysOpen_AdminCheckRow('enable_styles', 'Load Frontend CSS and JS', $settings['enable_styles']); ?>
          <tr><th colspan="2">鍐欏叆鍔熻兘</th></tr>
          <?php DdysOpen_AdminCheckRow('enable_auth_features', 'Enable API Key Features', $settings['enable_auth_features']); ?>
          <?php DdysOpen_AdminCheckRow('enable_request_form', '鍚敤鍓嶅彴姹傜墖琛ㄥ崟', $settings['enable_request_form']); ?>
          <?php DdysOpen_AdminPasswordRow('api_key', 'DDYS API Key', $settings['api_key'], 'Stored only in the site backend and used for server-side request submission.'); ?>
          <?php DdysOpen_AdminNumberRow('request_interval', 'Request Form Interval', $settings['request_interval'], 10, 3600, 'Basic IP rate limit in seconds.'); ?>
          <?php DdysOpen_AdminCheckRow('debug', '璋冭瘯妯″紡', $settings['debug']); ?>
        </table>
        <p><input type="submit" class="button" value="淇濆瓨璁剧疆"></p>
      </form>
    <?php elseif ($tab === 'shortcodes') : ?>
      <div class="ddys-zbp-admin-grid">
        <section class="ddys-zbp-panel">
          <h3>鐢熸垚鐭唬鐮?/h3>
          <label>鐭唬鐮?            <select id="ddys-zbp-shortcode-kind">
              <?php foreach ($definitions as $code => $label) : ?>
                <option value="<?php echo DdysOpen_Attr($code); ?>"><?php echo DdysOpen_Html($code . ' - ' . $label); ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>slug <input id="ddys-zbp-shortcode-slug" type="text" placeholder="this-tempting-madness"></label>
          <label>id <input id="ddys-zbp-shortcode-id" type="number" min="1"></label>
          <label>type <input id="ddys-zbp-shortcode-type" type="text" placeholder="movie"></label>
          <label>limit <input id="ddys-zbp-shortcode-limit" type="number" min="1" max="50" value="12"></label>
          <label>per_page <input id="ddys-zbp-shortcode-per-page" type="number" min="1" max="50" value="10"></label>
          <label>layout
            <select id="ddys-zbp-shortcode-layout"><option value="grid">grid</option><option value="list">list</option><option value="compact">compact</option></select>
          </label>
          <p><button type="button" class="button" id="ddys-zbp-shortcode-build">鐢熸垚</button></p>
        </section>
        <section class="ddys-zbp-panel">
          <h3>澶嶅埗浣跨敤</h3>
          <textarea id="ddys-zbp-shortcode-output" rows="6" readonly>[ddys_latest limit="12"]</textarea>
          <p><button type="button" class="button" id="ddys-zbp-shortcode-copy">澶嶅埗</button></p>
          <pre>[ddys_latest type="movie" limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_calendar year="2026" month="7"]
[ddys_movie slug="this-tempting-madness"]
[ddys_sources slug="this-tempting-madness"]
[ddys_collection slug="best-sci-fi" per_page="12"]</pre>
        </section>
      </div>
      <h3>宸叉敮鎸佺煭浠ｇ爜</h3>
      <table class="tableFull tableBorder">
        <?php foreach ($definitions as $code => $label) : ?>
          <tr><td><code>[<?php echo DdysOpen_Html($code); ?>]</code></td><td><?php echo DdysOpen_Html($label); ?></td></tr>
        <?php endforeach; ?>
      </table>
    <?php elseif ($tab === 'pages') : ?>
      <?php $pageBase = $zbp->host . 'zb_users/plugin/DdysOpen/page.php'; ?>
      <p>杩欎簺鍏紑椤甸潰鍙互鐩存帴鏀惧埌瀵艰埅銆佹ā鍧楁垨鏂囩珷閾炬帴閲岋紝涔熷彲浠ョ户缁娇鐢ㄧ煭浠ｇ爜宓屽叆鏂囩珷銆?/p>
      <table class="tableFull tableBorder">
        <tr><th>椤甸潰</th><th>閾炬帴</th></tr>
        <?php DdysOpen_PageRow('Latest Movies', $pageBase . '?view=latest'); ?>
        <?php DdysOpen_PageRow('鐑棬褰辩墖', $pageBase . '?view=hot'); ?>
        <?php DdysOpen_PageRow('Search Page', $pageBase . '?view=search'); ?>
        <?php DdysOpen_PageRow('褰辩墖鏃ュ巻', $pageBase . '?view=calendar'); ?>
        <?php DdysOpen_PageRow('鐗囧崟鍒楄〃', $pageBase . '?view=collections'); ?>
        <?php DdysOpen_PageRow('姹傜墖鍒楄〃', $pageBase . '?view=requests'); ?>
      </table>
    <?php elseif ($tab === 'cache') : ?>
      <p>褰撳墠缂撳瓨鏂囦欢鏁伴噺锛?strong><?php echo DdysOpen_Html((string) DdysOpen_CacheCount()); ?></strong></p>
      <form method="post" action="main.php?tab=cache">
        <?php DdysOpen_CsrfInput(); ?>
        <input type="hidden" name="ddys_action" value="flush_cache">
        <p><input type="submit" class="button" value="Clear DDYS Cache" onclick="return confirm('Clear DDYS cache?');"></p>
      </form>
    <?php elseif ($tab === 'diagnostics') : ?>
      <table class="tableFull tableBorder">
        <tr><th>鎻掍欢鐗堟湰</th><td><?php echo DDYSOPEN_VERSION; ?></td></tr>
        <tr><th>Z-BlogPHP</th><td><?php echo DdysOpen_Html(isset($zbp->version) ? $zbp->version : 'unknown'); ?></td></tr>
        <tr><th>PHP</th><td><?php echo DdysOpen_Html(PHP_VERSION); ?></td></tr>
        <tr><th>cURL</th><td><?php echo function_exists('curl_init') ? '鍙敤' : '涓嶅彲鐢紝灏嗗皾璇?PHP stream'; ?></td></tr>
        <tr><th>API Base URL</th><td><?php echo DdysOpen_Html($settings['api_base_url']); ?></td></tr>
        <tr><th>缂撳瓨鐩綍</th><td><?php echo DdysOpen_Html(DdysOpen_CacheDir()); ?></td></tr>
        <tr><th>缂撳瓨鏂囦欢</th><td><?php echo DdysOpen_Html((string) DdysOpen_CacheCount()); ?></td></tr>
      </table>
      <form method="post" action="main.php?tab=diagnostics">
        <?php DdysOpen_CsrfInput(); ?>
        <input type="hidden" name="ddys_action" value="test_api">
        <p><input type="submit" class="button" value="娴嬭瘯 DDYS 鎺ュ彛"></p>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';

function DdysOpen_AdminTab($id, $label, $active)
{
    echo '<a href="main.php?tab=' . DdysOpen_Attr($id) . '"><span class="m-left' . ($active === $id ? ' m-now' : '') . '">' . DdysOpen_Html($label) . '</span></a>';
}

function DdysOpen_CsrfInput()
{
    global $zbp;
    if (method_exists($zbp, 'GetCSRFToken')) {
        echo '<input type="hidden" name="csrfToken" value="' . DdysOpen_Attr($zbp->GetCSRFToken()) . '">';
    }
}

function DdysOpen_AdminTextRow($key, $label, $value, $note)
{
    echo '<tr><td class="td25"><label for="' . DdysOpen_Attr($key) . '">' . DdysOpen_Html($label) . '</label></td><td><input class="ddys-zbp-input-wide" id="' . DdysOpen_Attr($key) . '" name="' . DdysOpen_Attr($key) . '" type="url" value="' . DdysOpen_Attr($value) . '"><p class="note">' . DdysOpen_Html($note) . '</p></td></tr>';
}

function DdysOpen_AdminPasswordRow($key, $label, $value, $note)
{
    echo '<tr><td class="td25"><label for="' . DdysOpen_Attr($key) . '">' . DdysOpen_Html($label) . '</label></td><td><input class="ddys-zbp-input-wide" id="' . DdysOpen_Attr($key) . '" name="' . DdysOpen_Attr($key) . '" type="password" autocomplete="off" value="' . DdysOpen_Attr($value) . '"><p class="note">' . DdysOpen_Html($note) . '</p></td></tr>';
}

function DdysOpen_AdminNumberRow($key, $label, $value, $min, $max, $note)
{
    echo '<tr><td class="td25"><label for="' . DdysOpen_Attr($key) . '">' . DdysOpen_Html($label) . '</label></td><td><input id="' . DdysOpen_Attr($key) . '" name="' . DdysOpen_Attr($key) . '" type="number" min="' . DdysOpen_Attr((string) $min) . '" max="' . DdysOpen_Attr((string) $max) . '" value="' . DdysOpen_Attr((string) $value) . '"><p class="note">' . DdysOpen_Html($note) . '</p></td></tr>';
}

function DdysOpen_AdminSelectRow($key, $label, $value, $choices)
{
    echo '<tr><td class="td25"><label for="' . DdysOpen_Attr($key) . '">' . DdysOpen_Html($label) . '</label></td><td><select id="' . DdysOpen_Attr($key) . '" name="' . DdysOpen_Attr($key) . '">';
    foreach ($choices as $choice => $choiceLabel) {
        echo '<option value="' . DdysOpen_Attr($choice) . '"' . ($value === $choice ? ' selected="selected"' : '') . '>' . DdysOpen_Html($choiceLabel) . '</option>';
    }
    echo '</select></td></tr>';
}

function DdysOpen_AdminCheckRow($key, $label, $value)
{
    echo '<tr><td class="td25">' . DdysOpen_Html($label) . '</td><td><label><input name="' . DdysOpen_Attr($key) . '" type="checkbox" value="1"' . (DdysOpen_Bool($value) ? ' checked="checked"' : '') . '> 鍚敤</label></td></tr>';
}

function DdysOpen_PageRow($label, $url)
{
    echo '<tr><td>' . DdysOpen_Html($label) . '</td><td><a href="' . DdysOpen_Attr($url) . '" target="_blank" rel="noopener">' . DdysOpen_Html($url) . '</a></td></tr>';
}
