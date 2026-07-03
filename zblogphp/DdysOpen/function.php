<?php
if (!defined('ZBP_PATH')) {
    exit;
}

define('DDYSOPEN_ID', 'DdysOpen');
define('DDYSOPEN_VERSION', '1.0.0');
define('DDYSOPEN_API_DEFAULT', 'https://ddys.io/api/v1');
define('DDYSOPEN_SITE_DEFAULT', 'https://ddys.io');

function DdysOpen_Defaults()
{
    return array(
        'api_base_url' => DDYSOPEN_API_DEFAULT,
        'site_base_url' => DDYSOPEN_SITE_DEFAULT,
        'timeout' => 12,
        'default_cache_ttl' => 300,
        'dictionary_cache_ttl' => 86400,
        'fresh_cache_ttl' => 300,
        'list_cache_ttl' => 600,
        'detail_cache_ttl' => 1800,
        'community_cache_ttl' => 120,
        'theme' => 'auto',
        'layout' => 'grid',
        'columns' => 4,
        'target' => '_blank',
        'show_source_link' => '1',
        'enable_styles' => '1',
        'enable_auth_features' => '0',
        'enable_request_form' => '0',
        'api_key' => '',
        'request_interval' => 60,
        'debug' => '0',
    );
}

function DdysOpen_Install()
{
    global $zbp;
    $defaults = DdysOpen_Defaults();
    if (!$zbp->HasConfig(DDYSOPEN_ID)) {
        foreach ($defaults as $key => $value) {
            $zbp->Config(DDYSOPEN_ID)->$key = $value;
        }
        $zbp->SaveConfig(DDYSOPEN_ID);
    } else {
        $changed = false;
        foreach ($defaults as $key => $value) {
            if (!$zbp->Config(DDYSOPEN_ID)->HasKey($key)) {
                $zbp->Config(DDYSOPEN_ID)->$key = $value;
                $changed = true;
            }
        }
        if ($changed) {
            $zbp->SaveConfig(DDYSOPEN_ID);
        }
    }
    DdysOpen_CacheDir();
}

function DdysOpen_Uninstall()
{
    DdysOpen_CacheFlush();
}

function DdysOpen_GetSettings()
{
    global $zbp;
    DdysOpen_Install();
    $defaults = DdysOpen_Defaults();
    $settings = array();
    foreach ($defaults as $key => $value) {
        $settings[$key] = $zbp->Config(DDYSOPEN_ID)->HasKey($key) ? $zbp->Config(DDYSOPEN_ID)->$key : $value;
    }
    $settings['api_base_url'] = DdysOpen_NormalizeBaseUrl($settings['api_base_url'], DDYSOPEN_API_DEFAULT);
    $settings['site_base_url'] = DdysOpen_NormalizeBaseUrl($settings['site_base_url'], DDYSOPEN_SITE_DEFAULT);
    $settings['timeout'] = DdysOpen_IntRange($settings['timeout'], 12, 1, 30);
    $settings['columns'] = DdysOpen_IntRange($settings['columns'], 4, 1, 6);
    $settings['request_interval'] = DdysOpen_IntRange($settings['request_interval'], 60, 10, 3600);
    $settings['theme'] = DdysOpen_Choice($settings['theme'], array('auto', 'light', 'dark'), 'auto');
    $settings['layout'] = DdysOpen_Choice($settings['layout'], array('grid', 'list', 'compact'), 'grid');
    $settings['target'] = DdysOpen_Choice($settings['target'], array('_blank', '_self'), '_blank');
    return $settings;
}

function DdysOpen_SaveSettingsFromPost()
{
    global $zbp;
    $defaults = DdysOpen_Defaults();
    $data = array();
    foreach ($defaults as $key => $value) {
        $data[$key] = DdysOpen_Post($key, $value);
    }

    $data['api_base_url'] = DdysOpen_NormalizeBaseUrl($data['api_base_url'], DDYSOPEN_API_DEFAULT);
    $data['site_base_url'] = DdysOpen_NormalizeBaseUrl($data['site_base_url'], DDYSOPEN_SITE_DEFAULT);
    $data['timeout'] = DdysOpen_IntRange($data['timeout'], 12, 1, 30);
    $data['default_cache_ttl'] = DdysOpen_IntRange($data['default_cache_ttl'], 300, 0, 604800);
    $data['dictionary_cache_ttl'] = DdysOpen_IntRange($data['dictionary_cache_ttl'], 86400, 0, 604800);
    $data['fresh_cache_ttl'] = DdysOpen_IntRange($data['fresh_cache_ttl'], 300, 0, 604800);
    $data['list_cache_ttl'] = DdysOpen_IntRange($data['list_cache_ttl'], 600, 0, 604800);
    $data['detail_cache_ttl'] = DdysOpen_IntRange($data['detail_cache_ttl'], 1800, 0, 604800);
    $data['community_cache_ttl'] = DdysOpen_IntRange($data['community_cache_ttl'], 120, 0, 604800);
    $data['columns'] = DdysOpen_IntRange($data['columns'], 4, 1, 6);
    $data['request_interval'] = DdysOpen_IntRange($data['request_interval'], 60, 10, 3600);
    $data['theme'] = DdysOpen_Choice($data['theme'], array('auto', 'light', 'dark'), 'auto');
    $data['layout'] = DdysOpen_Choice($data['layout'], array('grid', 'list', 'compact'), 'grid');
    $data['target'] = DdysOpen_Choice($data['target'], array('_blank', '_self'), '_blank');
    foreach (array('show_source_link', 'enable_styles', 'enable_auth_features', 'enable_request_form', 'debug') as $key) {
        $data[$key] = DdysOpen_Post($key, '') === '1' ? '1' : '0';
    }
    $data['api_key'] = trim((string) DdysOpen_Post('api_key', ''));

    foreach ($data as $key => $value) {
        $zbp->Config(DDYSOPEN_ID)->$key = $value;
    }
    $zbp->SaveConfig(DDYSOPEN_ID);
}

function DdysOpen_IndexBegin()
{
    global $zbp;
    $settings = DdysOpen_GetSettings();
    $base = $zbp->host . 'zb_users/plugin/DdysOpen/';
    if (DdysOpen_Bool($settings['enable_styles'])) {
        $zbp->header .= "\n" . '<link rel="stylesheet" href="' . DdysOpen_Attr($base . 'assets/css/frontend.css?v=' . DDYSOPEN_VERSION) . '" />';
        $zbp->header .= "\n" . '<script defer src="' . DdysOpen_Attr($base . 'assets/js/frontend.js?v=' . DDYSOPEN_VERSION) . '"></script>';
    }
    if (!defined('DDYSOPEN_BUFFER_STARTED')) {
        define('DDYSOPEN_BUFFER_STARTED', 1);
        ob_start('DdysOpen_OutputBuffer');
    }
}

function DdysOpen_OutputBuffer($html)
{
    if (strpos($html, '[ddys_') === false) {
        return $html;
    }
    return DdysOpen_ParseShortcodes($html);
}

function DdysOpen_ViewPostTemplate($template)
{
    global $article;
    if (is_object($article)) {
        if (isset($article->Content) && strpos($article->Content, '[ddys_') !== false) {
            $article->Content = DdysOpen_ParseShortcodes($article->Content);
        }
        if (isset($article->Intro) && strpos($article->Intro, '[ddys_') !== false) {
            $article->Intro = DdysOpen_ParseShortcodes($article->Intro);
        }
    }
    return $template;
}

function DdysOpen_AdminHeader()
{
    global $zbp;
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (stripos($uri, 'zb_users/plugin/DdysOpen') === false) {
        return;
    }
    echo '<link rel="stylesheet" href="' . DdysOpen_Attr($zbp->host . 'zb_users/plugin/DdysOpen/assets/css/admin.css?v=' . DDYSOPEN_VERSION) . '" />';
    echo '<script defer src="' . DdysOpen_Attr($zbp->host . 'zb_users/plugin/DdysOpen/assets/js/admin.js?v=' . DDYSOPEN_VERSION) . '"></script>';
}

function DdysOpen_SettingSubMenu()
{
    global $zbp;
    echo '<a href="' . DdysOpen_Attr($zbp->host . 'zb_users/plugin/DdysOpen/main.php') . '"><span class="m-left">DDYS</span></a>';
}

function DdysOpen_LeftMenu(&$leftmenus)
{
    global $zbp;
    if (function_exists('MakeLeftMenu')) {
        $leftmenus['nav_DdysOpen'] = MakeLeftMenu(1, 'DDYS', $zbp->host . 'zb_users/plugin/DdysOpen/main.php', 'nav_DdysOpen', 'aDdysOpen', $zbp->host . 'zb_users/plugin/DdysOpen/assets/images/icon-32.png');
    }
}

function DdysOpen_RequireAdmin()
{
    global $zbp;
    if (!$zbp->CheckRights('root')) {
        $zbp->ShowError(6);
        die();
    }
    if (!$zbp->CheckPlugin(DDYSOPEN_ID)) {
        $zbp->ShowError(48);
        die();
    }
}

function DdysOpen_Client()
{
    return new DdysOpen_ApiClient(DdysOpen_GetSettings(), new DdysOpen_Cache());
}

class DdysOpen_ApiClient
{
    var $settings;
    var $cache;

    function __construct($settings, $cache)
    {
        $this->settings = $settings;
        $this->cache = $cache;
    }

    function get($path, $params, $options)
    {
        return $this->request('GET', $path, $params, null, $options);
    }

    function post($path, $body, $options)
    {
        return $this->request('POST', $path, array(), $body, $options);
    }

    function request($method, $path, $params, $body, $options)
    {
        global $zbp;
        $method = strtoupper($method);
        $path = '/' . ltrim((string) $path, '/');
        $params = DdysOpen_BuildQuery($params);
        $base = rtrim($this->settings['api_base_url'], '/');
        $url = $base . $path;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params, '', '&');
        }

        $ttl = isset($options['cache_ttl']) ? DdysOpen_IntRange($options['cache_ttl'], 0, 0, 604800) : $this->ttlForPath($path);
        $useCache = $method === 'GET' && empty($options['no_cache']);
        $cacheKey = $this->cache->key($method, $base, $path, $params);
        if ($useCache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }

        $headers = array(
            'Accept: application/json',
            'User-Agent: ddys-zblogphp-plugin/' . DDYSOPEN_VERSION . '; ' . (isset($zbp->host) ? $zbp->host : 'zblogphp'),
        );
        if (!empty($options['auth'])) {
            if (empty($this->settings['api_key'])) {
                return DdysOpen_Error('DDYS API Key is not configured.', 403, array());
            }
            $headers[] = 'Authorization: Bearer ' . $this->settings['api_key'];
        }

        $raw = false;
        $status = 0;
        $error = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->settings['timeout']);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->settings['timeout']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if ($method !== 'GET') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body ? $body : array()));
            }
            $raw = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($raw === false) {
                $error = curl_error($ch);
            }
            curl_close($ch);
        } else {
            $contextHeaders = implode("\r\n", $headers);
            $opts = array('http' => array('method' => $method, 'header' => $contextHeaders, 'timeout' => $this->settings['timeout']));
            if ($method !== 'GET') {
                $opts['http']['header'] .= "\r\nContent-Type: application/json";
                $opts['http']['content'] = json_encode($body ? $body : array());
            }
            $context = stream_context_create($opts);
            $raw = @file_get_contents($url, false, $context);
            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $line) {
                    if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $m)) {
                        $status = (int) $m[1];
                    }
                }
            }
        }

        if ($raw === false || $raw === '') {
            return DdysOpen_Error('DDYS API request failed: ' . $error, $status, array('url' => $url));
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            return DdysOpen_Error('DDYS API returned invalid JSON.', $status, array('raw' => $raw));
        }

        if ($status < 200 || $status >= 300 || !DdysOpen_IsSuccessResponse($json)) {
            $message = isset($json['message']) ? (string) $json['message'] : ('DDYS API request failed: HTTP ' . $status . '.');
            return DdysOpen_Error($message, $status, $json);
        }

        if ($useCache && $ttl > 0) {
            $this->cache->set($cacheKey, $json, $ttl);
        }
        return $json;
    }

    function ttlForPath($path)
    {
        if (preg_match('#^/(types|genres|regions|calendar)$#', $path)) {
            return (int) $this->settings['dictionary_cache_ttl'];
        }
        if (preg_match('#^/(latest|hot)$#', $path)) {
            return (int) $this->settings['fresh_cache_ttl'];
        }
        if (preg_match('#^/(movies/[^/]+|movies/[^/]+/sources|movies/[^/]+/related|collections/[^/]+|shares/[0-9]+)$#', $path)) {
            return (int) $this->settings['detail_cache_ttl'];
        }
        if (preg_match('#^/(movies/[^/]+/comments|suggest|shares|requests|activities|user/)#', $path)) {
            return (int) $this->settings['community_cache_ttl'];
        }
        if (preg_match('#^/(movies|search|collections)#', $path)) {
            return (int) $this->settings['list_cache_ttl'];
        }
        return (int) $this->settings['default_cache_ttl'];
    }
}

class DdysOpen_Cache
{
    function key($method, $base, $path, $params)
    {
        return sha1($method . '|' . $base . '|' . $path . '|' . json_encode($params));
    }

    function get($key)
    {
        $file = DdysOpen_CacheDir() . $key . '.json';
        if (!is_file($file)) {
            return false;
        }
        $data = json_decode((string) @file_get_contents($file), true);
        if (!is_array($data) || !isset($data['expires']) || time() > (int) $data['expires']) {
            @unlink($file);
            return false;
        }
        return isset($data['payload']) ? $data['payload'] : false;
    }

    function set($key, $payload, $ttl)
    {
        $file = DdysOpen_CacheDir() . $key . '.json';
        $data = array('expires' => time() + (int) $ttl, 'payload' => $payload);
        @file_put_contents($file, json_encode($data));
    }
}

function DdysOpen_CacheDir()
{
    $dir = ZBP_PATH . 'zb_users/cache/ddysopen/';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

function DdysOpen_CacheFlush()
{
    $dir = DdysOpen_CacheDir();
    $count = 0;
    foreach (glob($dir . '*.json') as $file) {
        if (is_file($file) && @unlink($file)) {
            $count++;
        }
    }
    return $count;
}

function DdysOpen_CacheCount()
{
    $files = glob(DdysOpen_CacheDir() . '*.json');
    return is_array($files) ? count($files) : 0;
}

function DdysOpen_ParseShortcodes($content)
{
    return preg_replace_callback('/\[(ddys_[a-z_]+)([^\]]*)\]/i', 'DdysOpen_ShortcodeCallback', $content);
}

function DdysOpen_ShortcodeCallback($matches)
{
    $tag = strtolower($matches[1]);
    $atts = DdysOpen_ParseAttributes(isset($matches[2]) ? $matches[2] : '');
    return DdysOpen_RenderShortcode($tag, $atts);
}

function DdysOpen_ParseAttributes($text)
{
    $atts = array();
    if (preg_match_all('/([a-zA-Z0-9_:-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\']+))/', $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $atts[strtolower($m[1])] = html_entity_decode($m[2] !== '' ? $m[2] : ($m[3] !== '' ? $m[3] : $m[4]), ENT_QUOTES, 'UTF-8');
        }
    }
    return $atts;
}

function DdysOpen_ShortcodeDefinitions()
{
    return array(
        'ddys_movies' => '褰辩墖鍒楄〃',
        'ddys_latest' => 'Latest movies',
        'ddys_hot' => '鐑棬褰辩墖',
        'ddys_search' => 'Search box',
        'ddys_suggest' => '鎼滅储寤鸿',
        'ddys_calendar' => '褰辩墖鏃ュ巻',
        'ddys_movie' => '褰辩墖璇︽儏',
        'ddys_sources' => 'Movie sources',
        'ddys_related' => '鐩稿叧褰辩墖',
        'ddys_comments' => '褰辩墖璇勮',
        'ddys_collections' => '鐗囧崟鍒楄〃',
        'ddys_collection' => '鐗囧崟璇︽儏',
        'ddys_shares' => '鍒嗕韩鍒楄〃',
        'ddys_share' => '鍒嗕韩璇︽儏',
        'ddys_requests' => '姹傜墖鍒楄〃',
        'ddys_activities' => 'Activity list',
        'ddys_user' => '鐢ㄦ埛璧勬枡',
        'ddys_types' => '绫诲瀷瀛楀吀',
        'ddys_genres' => '棰樻潗瀛楀吀',
        'ddys_regions' => '鍦板尯瀛楀吀',
        'ddys_request_form' => '姹傜墖琛ㄥ崟',
    );
}

function DdysOpen_RenderShortcode($tag, $atts)
{
    $settings = DdysOpen_GetSettings();
    $client = DdysOpen_Client();
    $renderer = new DdysOpen_Renderer($settings);
    $atts = DdysOpen_MergeAtts($atts, array(
        'layout' => $settings['layout'],
        'theme' => $settings['theme'],
        'columns' => $settings['columns'],
        'target' => $settings['target'],
        'show_poster' => '1',
        'show_rating' => '1',
        'cache_ttl' => '',
    ));

    if ($tag === 'ddys_movies') {
        $atts = DdysOpen_MergeAtts($atts, array('type' => '', 'genre' => '', 'region' => '', 'year' => '', 'sort' => 'latest', 'page' => 1, 'per_page' => 24));
        return DdysOpen_RenderGet($client, $renderer, '/movies', DdysOpen_Query($atts, array('type', 'genre', 'region', 'year', 'sort', 'page', 'per_page')), $atts);
    }
    if ($tag === 'ddys_latest') {
        $atts = DdysOpen_MergeAtts($atts, array('type' => '', 'genre' => '', 'region' => '', 'year' => '', 'limit' => 12));
        return DdysOpen_RenderGet($client, $renderer, '/latest', DdysOpen_Query($atts, array('type', 'genre', 'region', 'year', 'limit')), $atts);
    }
    if ($tag === 'ddys_hot') {
        $atts = DdysOpen_MergeAtts($atts, array('limit' => 10, 'type' => '', 'genre' => '', 'region' => ''));
        return DdysOpen_RenderGet($client, $renderer, '/hot', DdysOpen_Query($atts, array('limit', 'type', 'genre', 'region')), $atts);
    }
    if ($tag === 'ddys_search') {
        $atts = DdysOpen_MergeAtts($atts, array('q' => '', 'type' => 'movie', 'page' => 1, 'per_page' => 10, 'show_form' => '1'));
        $q = DdysOpen_Get('ddys_q', $atts['q']);
        $type = DdysOpen_Choice(DdysOpen_Get('ddys_type', $atts['type']), array('movie', 'share', 'request'), 'movie');
        $html = DdysOpen_Bool($atts['show_form']) ? $renderer->searchForm($q, $type) : '';
        if ($q === '') {
            return $renderer->wrap($html, $atts);
        }
        $payload = $client->get('/search', DdysOpen_Query(array_merge($atts, array('q' => $q, 'type' => $type)), array('q', 'type', 'page', 'per_page')), DdysOpen_CacheOptions($atts));
        return $html . (DdysOpen_IsError($payload) ? $renderer->error($payload) : $renderer->listItems($payload, $atts));
    }
    if ($tag === 'ddys_suggest') {
        $atts = DdysOpen_MergeAtts($atts, array('q' => '', 'limit' => 8));
        return DdysOpen_RenderGet($client, $renderer, '/suggest', DdysOpen_Query($atts, array('q', 'limit')), $atts);
    }
    if ($tag === 'ddys_calendar') {
        $atts = DdysOpen_MergeAtts($atts, array('year' => '', 'month' => ''));
        $payload = $client->get('/calendar', DdysOpen_Query($atts, array('year', 'month')), DdysOpen_CacheOptions($atts));
        return DdysOpen_IsError($payload) ? $renderer->error($payload) : $renderer->calendar($payload, $atts);
    }
    if ($tag === 'ddys_movie') {
        $atts = DdysOpen_MergeAtts($atts, array('slug' => ''));
        if ($atts['slug'] === '') {
            return $renderer->error(DdysOpen_Error('Missing movie slug.', 400, array()));
        }
        $payload = $client->get('/movies/' . rawurlencode($atts['slug']), array(), DdysOpen_CacheOptions($atts));
        return DdysOpen_IsError($payload) ? $renderer->error($payload) : $renderer->movieDetail($payload, $atts);
    }
    if ($tag === 'ddys_sources') {
        $atts = DdysOpen_MergeAtts($atts, array('slug' => ''));
        if ($atts['slug'] === '') {
            return $renderer->error(DdysOpen_Error('Missing movie slug.', 400, array()));
        }
        $payload = $client->get('/movies/' . rawurlencode($atts['slug']) . '/sources', array(), DdysOpen_CacheOptions($atts));
        return DdysOpen_IsError($payload) ? $renderer->error($payload) : $renderer->sources($payload, $atts);
    }
    if ($tag === 'ddys_related') {
        $atts = DdysOpen_MergeAtts($atts, array('slug' => ''));
        if ($atts['slug'] === '') {
            return $renderer->error(DdysOpen_Error('Missing movie slug.', 400, array()));
        }
        return DdysOpen_RenderGet($client, $renderer, '/movies/' . rawurlencode($atts['slug']) . '/related', array(), $atts);
    }
    if ($tag === 'ddys_comments') {
        $atts = DdysOpen_MergeAtts($atts, array('slug' => '', 'page' => 1, 'per_page' => 20));
        if ($atts['slug'] === '') {
            return $renderer->error(DdysOpen_Error('Missing movie slug.', 400, array()));
        }
        return DdysOpen_RenderGet($client, $renderer, '/movies/' . rawurlencode($atts['slug']) . '/comments', DdysOpen_Query($atts, array('page', 'per_page')), $atts);
    }
    if ($tag === 'ddys_collections') {
        $atts = DdysOpen_MergeAtts($atts, array('page' => 1, 'per_page' => 10));
        return DdysOpen_RenderGet($client, $renderer, '/collections', DdysOpen_Query($atts, array('page', 'per_page')), $atts);
    }
    if ($tag === 'ddys_collection') {
        $atts = DdysOpen_MergeAtts($atts, array('slug' => '', 'id' => '', 'page' => 1, 'per_page' => 12));
        $slug = $atts['slug'] !== '' ? $atts['slug'] : $atts['id'];
        if ($slug === '') {
            return $renderer->error(DdysOpen_Error('Missing collection slug.', 400, array()));
        }
        $payload = $client->get('/collections/' . rawurlencode($slug), DdysOpen_Query($atts, array('page', 'per_page')), DdysOpen_CacheOptions($atts));
        return DdysOpen_IsError($payload) ? $renderer->error($payload) : $renderer->collectionDetail($payload, $atts);
    }
    if ($tag === 'ddys_shares') {
        $atts = DdysOpen_MergeAtts($atts, array('page' => 1, 'per_page' => 10));
        return DdysOpen_RenderGet($client, $renderer, '/shares', DdysOpen_Query($atts, array('page', 'per_page')), $atts);
    }
    if ($tag === 'ddys_share') {
        $atts = DdysOpen_MergeAtts($atts, array('id' => 0));
        $id = (int) $atts['id'];
        if ($id < 1) {
            return $renderer->error(DdysOpen_Error('Missing share ID.', 400, array()));
        }
        $payload = $client->get('/shares/' . $id, array(), DdysOpen_CacheOptions($atts));
        return DdysOpen_IsError($payload) ? $renderer->error($payload) : $renderer->shareDetail($payload, $atts);
    }
    if ($tag === 'ddys_requests') {
        $atts = DdysOpen_MergeAtts($atts, array('page' => 1, 'per_page' => 10));
        return DdysOpen_RenderGet($client, $renderer, '/requests', DdysOpen_Query($atts, array('page', 'per_page')), $atts);
    }
    if ($tag === 'ddys_activities') {
        $atts = DdysOpen_MergeAtts($atts, array('type' => '', 'page' => 1, 'per_page' => 10));
        return DdysOpen_RenderGet($client, $renderer, '/activities', DdysOpen_Query($atts, array('type', 'page', 'per_page')), $atts);
    }
    if ($tag === 'ddys_user') {
        $atts = DdysOpen_MergeAtts($atts, array('username' => ''));
        if ($atts['username'] === '') {
            return $renderer->error(DdysOpen_Error('Missing username.', 400, array()));
        }
        return DdysOpen_RenderGet($client, $renderer, '/user/' . rawurlencode($atts['username']), array(), $atts);
    }
    if ($tag === 'ddys_types' || $tag === 'ddys_genres' || $tag === 'ddys_regions') {
        $path = '/' . str_replace('ddys_', '', $tag);
        $payload = $client->get($path, array(), DdysOpen_CacheOptions($atts));
        return DdysOpen_IsError($payload) ? $renderer->error($payload) : $renderer->dictionaries($payload, $atts);
    }
    if ($tag === 'ddys_request_form') {
        return $renderer->requestForm($atts);
    }
    return '';
}

function DdysOpen_RenderGet($client, $renderer, $path, $params, $atts)
{
    $payload = $client->get($path, $params, DdysOpen_CacheOptions($atts));
    return DdysOpen_IsError($payload) ? $renderer->error($payload) : $renderer->listItems($payload, $atts);
}

class DdysOpen_Renderer
{
    var $settings;

    function __construct($settings)
    {
        $this->settings = $settings;
    }

    function wrap($html, $args)
    {
        $theme = DdysOpen_Choice(DdysOpen_ArrayValue($args, 'theme', $this->settings['theme']), array('auto', 'light', 'dark'), 'auto');
        $layout = DdysOpen_Choice(DdysOpen_ArrayValue($args, 'layout', $this->settings['layout']), array('grid', 'list', 'compact'), 'grid');
        $classes = 'ddys-zbp ddys-zbp-theme-' . $theme . ' ddys-zbp-layout-' . $layout;
        return '<div class="' . DdysOpen_Attr($classes) . '">' . $html . '</div>';
    }

    function error($error)
    {
        $message = DdysOpen_IsError($error) ? $error['message'] : (string) $error;
        return $this->wrap('<div class="ddys-zbp-error">' . DdysOpen_Html($message) . '</div>', array());
    }

    function emptyState($message)
    {
        return '<div class="ddys-zbp-empty">' . DdysOpen_Html($message ? $message : 'No content found.') . '</div>';
    }

    function listItems($payload, $args)
    {
        $data = DdysOpen_PayloadData($payload);
        $items = $this->normalizeListItems(is_array($data) ? $data : array());
        if (empty($items)) {
            return $this->wrap($this->emptyState('No content found.'), $args);
        }
        $html = '<div class="ddys-zbp-items">';
        foreach ($items as $item) {
            if (is_array($item)) {
                $html .= $this->card($item, $args);
            }
        }
        $html .= '</div>';
        $html .= $this->paginationMeta(DdysOpen_PayloadMeta($payload));
        $html .= $this->sourceLink('');
        return $this->wrap($html, $args);
    }

    function movieDetail($payload, $args)
    {
        $data = DdysOpen_PayloadData($payload);
        if (!is_array($data)) {
            return $this->wrap($this->emptyState('Movie not found.'), $args);
        }
        $html = '<article class="ddys-zbp-detail">';
        $html .= $this->card($data, array_merge($args, array('detail' => '1')));
        $intro = DdysOpen_ArrayValue($data, 'description', DdysOpen_ArrayValue($data, 'intro', ''));
        if ($intro !== '') {
            $html .= '<div class="ddys-zbp-description">' . nl2br(DdysOpen_Html($intro)) . '</div>';
        }
        $html .= '</article>';
        $html .= $this->sourceLink(DdysOpen_ArrayValue($data, 'url', ''));
        return $this->wrap($html, $args);
    }

    function sources($payload, $args)
    {
        $data = DdysOpen_PayloadData($payload);
        if (!is_array($data) || empty($data)) {
            return $this->wrap($this->emptyState('No sources found.'), $args);
        }
        $groups = $this->normalizeSourceGroups($data);
        if (empty($groups)) {
            return $this->wrap($this->emptyState('No sources found.'), $args);
        }
        $html = '<div class="ddys-zbp-sources">';
        foreach ($groups as $name => $resources) {
            $html .= '<section class="ddys-zbp-source-group"><h3>' . DdysOpen_Html($name) . '</h3><ul>';
            if (is_array($resources)) {
                foreach ($resources as $resource) {
                    if (!is_array($resource)) {
                        continue;
                    }
                    $title = DdysOpen_ArrayValue($resource, 'title', DdysOpen_ArrayValue($resource, 'name', DdysOpen_ArrayValue($resource, 'download_type', '璧勬簮')));
                    $url = DdysOpen_ArrayValue($resource, 'url', DdysOpen_ArrayValue($resource, 'link', ''));
                    $meta = DdysOpen_FilterEmpty(array(DdysOpen_ArrayValue($resource, 'quality', ''), DdysOpen_ArrayValue($resource, 'format', ''), DdysOpen_ArrayValue($resource, 'size', '')));
                    $html .= '<li>' . $this->resourceLinks($title, $url);
                    if (!empty($meta)) {
                        $html .= ' <span class="ddys-zbp-card-meta">' . DdysOpen_Html(implode(' / ', $meta)) . '</span>';
                    }
                    $html .= '</li>';
                }
            }
            $html .= '</ul></section>';
        }
        $html .= '</div>';
        return $this->wrap($html, $args);
    }

    function collectionDetail($payload, $args)
    {
        $data = DdysOpen_PayloadData($payload);
        if (!is_array($data)) {
            return $this->wrap($this->emptyState('Collection not found.'), $args);
        }
        $html = '<article class="ddys-zbp-detail"><h2>' . DdysOpen_Html(DdysOpen_ArrayValue($data, 'title', '鐗囧崟')) . '</h2>';
        if (!empty($data['description'])) {
            $html .= '<div class="ddys-zbp-description">' . nl2br(DdysOpen_Html($data['description'])) . '</div>';
        }
        $html .= '</article>';
        $movies = isset($data['movies']) && is_array($data['movies']) ? $data['movies'] : array();
        if (!empty($movies)) {
            $html .= '<div class="ddys-zbp-items">';
            foreach ($movies as $movie) {
                if (is_array($movie)) {
                    $html .= $this->card($movie, $args);
                }
            }
            $html .= '</div>';
        } else {
            $html .= $this->emptyState('This collection has no movies yet.');
        }
        $html .= $this->paginationMeta(DdysOpen_PayloadMeta($payload));
        $html .= $this->sourceLink(DdysOpen_ArrayValue($data, 'url', ''));
        return $this->wrap($html, $args);
    }

    function shareDetail($payload, $args)
    {
        $data = DdysOpen_PayloadData($payload);
        if (!is_array($data)) {
            return $this->wrap($this->emptyState('Share not found.'), $args);
        }
        $html = '<article class="ddys-zbp-detail"><h2>' . DdysOpen_Html(DdysOpen_ArrayValue($data, 'title', '鍒嗕韩')) . '</h2>';
        $meta = DdysOpen_FilterEmpty(array(DdysOpen_ArrayValue($data, 'resource_type', ''), DdysOpen_ArrayValue($data, 'quality', ''), DdysOpen_ArrayValue($data, 'username', '')));
        if (!empty($meta)) {
            $html .= '<div class="ddys-zbp-card-meta">' . DdysOpen_Html(implode(' / ', $meta)) . '</div>';
        }
        if (!empty($data['note'])) {
            $html .= '<div class="ddys-zbp-description">' . nl2br(DdysOpen_Html($data['note'])) . '</div>';
        }
        if (!empty($data['resources']) && is_array($data['resources'])) {
            $html .= '<h3>璧勬簮</h3><ul class="ddys-zbp-resource-list">';
            foreach ($data['resources'] as $resource) {
                if (is_array($resource)) {
                    $html .= '<li>' . $this->resourceLinks(DdysOpen_ArrayValue($resource, 'type', '璧勬簮'), DdysOpen_ArrayValue($resource, 'url', '')) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</article>';
        $html .= $this->sourceLink(DdysOpen_ArrayValue($data, 'url', ''));
        return $this->wrap($html, $args);
    }

    function calendar($payload, $args)
    {
        $data = DdysOpen_PayloadData($payload);
        if (!is_array($data) || empty($data)) {
            return $this->wrap($this->emptyState('No calendar content found.'), $args);
        }
        $days = $this->extractCalendarDays($data);
        if (empty($days)) {
            return $this->wrap($this->emptyState('No calendar content found.'), $args);
        }
        $html = '<div class="ddys-zbp-calendar">';
        foreach ($days as $day => $items) {
            $html .= '<section class="ddys-zbp-calendar-day"><h3>' . DdysOpen_Html($day) . '</h3><div class="ddys-zbp-items">';
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $html .= $this->card($item, $args);
                    }
                }
            }
            $html .= '</div></section>';
        }
        $html .= '</div>';
        return $this->wrap($html, $args);
    }

    function dictionaries($payload, $args)
    {
        $data = DdysOpen_PayloadData($payload);
        if (!is_array($data) || empty($data)) {
            return $this->wrap($this->emptyState('No dictionary content found.'), $args);
        }
        $html = '<div class="ddys-zbp-taxonomy-list">';
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $name = DdysOpen_ArrayValue($item, 'name', '');
                $code = DdysOpen_ArrayValue($item, 'code', '');
            } else {
                $name = (string) $item;
                $code = is_string($key) ? $key : '';
            }
            if ($name === '') {
                continue;
            }
            $html .= '<span class="ddys-zbp-pill"><span>' . DdysOpen_Html($name) . '</span>';
            if ($code !== '') {
                $html .= '<code>' . DdysOpen_Html($code) . '</code>';
            }
            $html .= '</span>';
        }
        $html .= '</div>';
        return $this->wrap($html, $args);
    }

    function searchForm($q, $type)
    {
        $html = '<form class="ddys-zbp-search-form" method="get">';
        $html .= '<input type="search" name="ddys_q" value="' . DdysOpen_Attr($q) . '" placeholder="鎼滅储褰辩墖銆佸垎浜垨姹傜墖">';
        $html .= '<select name="ddys_type">';
        foreach (array('movie' => '褰辩墖', 'share' => '鍒嗕韩', 'request' => '姹傜墖') as $value => $label) {
            $html .= '<option value="' . DdysOpen_Attr($value) . '"' . ($type === $value ? ' selected="selected"' : '') . '>' . DdysOpen_Html($label) . '</option>';
        }
        $html .= '</select><button type="submit">鎼滅储</button></form>';
        return $html;
    }

    function requestForm($args)
    {
        global $zbp;
        if (!DdysOpen_Bool($this->settings['enable_auth_features']) || !DdysOpen_Bool($this->settings['enable_request_form'])) {
            return $this->wrap($this->emptyState('Request form is disabled.'), $args);
        }
        $action = $zbp->host . 'zb_users/plugin/DdysOpen/request.php';
        $html = '<form class="ddys-zbp-request-form" method="post" action="' . DdysOpen_Attr($action) . '" data-ddys-request-form>';
        if (method_exists($zbp, 'GetCSRFToken')) {
            $html .= '<input type="hidden" name="csrfToken" value="' . DdysOpen_Attr($zbp->GetCSRFToken()) . '">';
        }
        $html .= '<input type="hidden" name="redirect" value="' . DdysOpen_Attr(DdysOpen_CurrentUrl()) . '">';
        $html .= '<label>鐗囧悕<input type="text" name="title" maxlength="255" required></label>';
        $html .= '<label>骞翠唤<input type="number" name="year" min="1900" max="2099"></label>';
        $html .= '<label>绫诲瀷<select name="type"><option value=""></option><option value="movie">鐢靛奖</option><option value="series">鍓ч泦</option><option value="variety">缁艰壓</option><option value="anime">鍔ㄦ极</option></select></label>';
        $html .= '<label>璞嗙摚 ID<input type="text" name="douban_id" maxlength="30"></label>';
        $html .= '<label>澶囨敞<textarea name="description" maxlength="1000"></textarea></label>';
        $html .= '<button type="submit">鎻愪氦姹傜墖</button><p class="ddys-zbp-request-status" role="status"></p>';
        $html .= '</form>';
        return $this->wrap($html, $args);
    }

    function card($item, $args)
    {
        $siteBase = rtrim($this->settings['site_base_url'], '/');
        $title = DdysOpen_ArrayValue($item, 'title', DdysOpen_ArrayValue($item, 'name', DdysOpen_ArrayValue($item, 'username', 'Untitled')));
        $url = DdysOpen_ArrayValue($item, 'url', '');
        $poster = DdysOpen_ArrayValue($item, 'poster', DdysOpen_ArrayValue($item, 'avatar', ''));
        $rating = DdysOpen_ArrayValue($item, 'rating', '');
        $year = DdysOpen_ArrayValue($item, 'year', '');
        $type = DdysOpen_ArrayValue($item, 'type', DdysOpen_ArrayValue($item, 'type_code', ''));
        $href = $url ? $this->absoluteSiteUrl($siteBase, $url) : '';
        $showPoster = DdysOpen_Bool(DdysOpen_ArrayValue($args, 'show_poster', '1'));
        $showRating = DdysOpen_Bool(DdysOpen_ArrayValue($args, 'show_rating', '1'));
        $target = DdysOpen_Choice(DdysOpen_ArrayValue($args, 'target', $this->settings['target']), array('_blank', '_self'), '_blank');
        $html = '<article class="ddys-zbp-card">';
        if ($showPoster && $poster !== '') {
            $html .= '<div class="ddys-zbp-card-poster"><img src="' . DdysOpen_Attr($poster) . '" alt="' . DdysOpen_Attr($title) . '" loading="lazy"></div>';
        }
        $html .= '<div class="ddys-zbp-card-body"><h3 class="ddys-zbp-card-title">';
        if ($href !== '') {
            $html .= '<a href="' . DdysOpen_Attr($href) . '" target="' . DdysOpen_Attr($target) . '" rel="noopener">' . DdysOpen_Html($title) . '</a>';
        } else {
            $html .= DdysOpen_Html($title);
        }
        $html .= '</h3>';
        $meta = DdysOpen_FilterEmpty(array($year, $type, $showRating ? $rating : ''));
        if (!empty($meta)) {
            $html .= '<div class="ddys-zbp-card-meta">' . DdysOpen_Html(implode(' / ', $meta)) . '</div>';
        }
        $summary = DdysOpen_ArrayValue($item, 'description', DdysOpen_ArrayValue($item, 'content', ''));
        if ($summary !== '') {
            $html .= '<div class="ddys-zbp-card-summary">' . DdysOpen_Html(DdysOpen_TrimWords(strip_tags($summary), 90)) . '</div>';
        }
        $html .= '</div></article>';
        return $html;
    }

    function normalizeSourceGroups($data)
    {
        if (isset($data['online']) || isset($data['download'])) {
            return DdysOpen_FilterEmpty(array(
                '鍦ㄧ嚎鎾斁' => isset($data['online']) && is_array($data['online']) ? $data['online'] : array(),
                '涓嬭浇璧勬簮' => isset($data['download']) && is_array($data['download']) ? $data['download'] : array(),
            ));
        }
        if (DdysOpen_ArrayIsList($data)) {
            return array('璧勬簮' => $data);
        }
        $groups = array();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value['resources']) && is_array($value['resources'])) {
                $groups[(string) DdysOpen_ArrayValue($value, 'name', $key)] = $value['resources'];
            } elseif (is_array($value)) {
                $groups[(string) $key] = $value;
            }
        }
        return $groups;
    }

    function resourceLinks($title, $url)
    {
        if ($url === '') {
            return DdysOpen_Html($title);
        }
        $parts = DdysOpen_FilterEmpty(explode('#', $url));
        $links = array();
        foreach ($parts as $index => $part) {
            $label = $title;
            $href = $part;
            if (strpos($part, '$') !== false) {
                $pair = explode('$', $part, 2);
                $label = $pair[0] !== '' ? $pair[0] : $title;
                $href = isset($pair[1]) ? $pair[1] : '';
            } elseif (count($parts) > 1) {
                $label = $title . ' ' . ($index + 1);
            }
            if (DdysOpen_AllowedResourceUrl($href)) {
                $links[] = '<a href="' . DdysOpen_Attr($href) . '" target="' . DdysOpen_Attr($this->settings['target']) . '" rel="noopener">' . DdysOpen_Html($label) . '</a>';
            }
        }
        return empty($links) ? DdysOpen_Html($title) : implode(' ', $links);
    }

    function paginationMeta($meta)
    {
        if (!is_array($meta) || empty($meta['total'])) {
            return '';
        }
        $page = isset($meta['page']) ? (int) $meta['page'] : 1;
        return '<div class="ddys-zbp-meta">绗?' . DdysOpen_Html((string) $page) . ' 椤碉紝鍏?' . DdysOpen_Html((string) $meta['total']) . ' 鏉?/div>';
    }

    function sourceLink($path)
    {
        if (!DdysOpen_Bool($this->settings['show_source_link'])) {
            return '';
        }
        $href = $this->absoluteSiteUrl(rtrim($this->settings['site_base_url'], '/'), $path ? $path : '/');
        return '<div class="ddys-zbp-source-link"><a href="' . DdysOpen_Attr($href) . '" target="_blank" rel="noopener">鏌ョ湅 DDYS 鏉ユ簮</a></div>';
    }

    function absoluteSiteUrl($siteBase, $url)
    {
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        return rtrim($siteBase, '/') . '/' . ltrim($url, '/');
    }

    function normalizeListItems($data)
    {
        if ($this->looksLikeSingleItem($data)) {
            return array($data);
        }
        if (DdysOpen_ArrayIsList($data)) {
            return $data;
        }
        $items = array();
        foreach ($data as $value) {
            if (!is_array($value)) {
                continue;
            }
            if ($this->looksLikeSingleItem($value)) {
                $items[] = $value;
            } elseif (DdysOpen_ArrayIsList($value)) {
                foreach ($value as $nested) {
                    if (is_array($nested)) {
                        $items[] = $nested;
                    }
                }
            }
        }
        return $items;
    }

    function looksLikeSingleItem($data)
    {
        return is_array($data) && (isset($data['id']) || isset($data['slug']) || isset($data['title']) || isset($data['username']));
    }

    function extractCalendarDays($data)
    {
        if (isset($data['days']) && is_array($data['days'])) {
            return $data['days'];
        }
        $days = array();
        foreach ($data as $key => $value) {
            if (is_array($value) && preg_match('/^\d{4}-\d{2}-\d{2}$|^\d{1,2}$/', (string) $key)) {
                $days[(string) $key] = $value;
            }
        }
        return $days;
    }
}

function DdysOpen_HandleRequestForm()
{
    $settings = DdysOpen_GetSettings();
    if (!DdysOpen_Bool($settings['enable_auth_features']) || !DdysOpen_Bool($settings['enable_request_form'])) {
        return DdysOpen_Error('Request form is disabled.', 403, array());
    }
    if (function_exists('CheckIsRefererValid')) {
        CheckIsRefererValid();
    }
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    if (!DdysOpen_CheckRateLimit('request', $ip, (int) $settings['request_interval'])) {
        return DdysOpen_Error('Please wait before submitting again.', 429, array());
    }
    $title = trim((string) DdysOpen_Post('title', ''));
    if ($title === '') {
        return DdysOpen_Error('Please enter a title.', 400, array());
    }
    $body = array(
        'title' => $title,
        'year' => DdysOpen_Post('year', ''),
        'type' => DdysOpen_Post('type', ''),
        'description' => DdysOpen_Post('description', ''),
        'douban_id' => DdysOpen_Post('douban_id', ''),
        'imdb_id' => DdysOpen_Post('imdb_id', ''),
    );
    $client = DdysOpen_Client();
    return $client->post('/requests', DdysOpen_BuildQuery($body), array('auth' => true, 'no_cache' => true));
}

function DdysOpen_CheckRateLimit($scope, $key, $interval)
{
    $file = DdysOpen_CacheDir() . 'rate-' . sha1($scope . '|' . $key) . '.json';
    $last = 0;
    if (is_file($file)) {
        $data = json_decode((string) @file_get_contents($file), true);
        if (is_array($data) && isset($data['time'])) {
            $last = (int) $data['time'];
        }
    }
    if ($last > 0 && time() - $last < $interval) {
        return false;
    }
    @file_put_contents($file, json_encode(array('time' => time())));
    return true;
}

function DdysOpen_RenderRoute($view, $params)
{
    $view = DdysOpen_Choice($view, array('latest', 'hot', 'search', 'calendar', 'movie', 'collections', 'requests'), 'latest');
    if ($view === 'hot') {
        return DdysOpen_RenderShortcode('ddys_hot', array('limit' => DdysOpen_ArrayValue($params, 'limit', 12)));
    }
    if ($view === 'search') {
        return DdysOpen_RenderShortcode('ddys_search', array('q' => DdysOpen_ArrayValue($params, 'q', ''), 'type' => DdysOpen_ArrayValue($params, 'type', 'movie')));
    }
    if ($view === 'calendar') {
        return DdysOpen_RenderShortcode('ddys_calendar', array('year' => DdysOpen_ArrayValue($params, 'year', ''), 'month' => DdysOpen_ArrayValue($params, 'month', '')));
    }
    if ($view === 'movie') {
        return DdysOpen_RenderShortcode('ddys_movie', array('slug' => DdysOpen_ArrayValue($params, 'slug', '')));
    }
    if ($view === 'collections') {
        return DdysOpen_RenderShortcode('ddys_collections', array('page' => DdysOpen_ArrayValue($params, 'page', 1)));
    }
    if ($view === 'requests') {
        return DdysOpen_RenderShortcode('ddys_requests', array('page' => DdysOpen_ArrayValue($params, 'page', 1)));
    }
    return DdysOpen_RenderShortcode('ddys_latest', array('limit' => DdysOpen_ArrayValue($params, 'limit', 12)));
}

function DdysOpen_MergeAtts($atts, $defaults)
{
    foreach ($defaults as $key => $value) {
        if (!array_key_exists($key, $atts)) {
            $atts[$key] = $value;
        }
    }
    return $atts;
}

function DdysOpen_Query($atts, $keys)
{
    $query = array();
    foreach ($keys as $key) {
        if (!array_key_exists($key, $atts) || $atts[$key] === '') {
            continue;
        }
        if (in_array($key, array('page', 'per_page', 'limit', 'year', 'month'), true)) {
            $number = DdysOpen_NumericQueryValue($key, $atts[$key]);
            if ($number !== null) {
                $query[$key] = $number;
            }
        } else {
            $query[$key] = trim((string) $atts[$key]);
        }
    }
    return $query;
}

function DdysOpen_NumericQueryValue($key, $value)
{
    $number = (int) $value;
    if ($key === 'month') {
        return ($number >= 1 && $number <= 12) ? $number : null;
    }
    if ($key === 'year') {
        return ($number >= 1900 && $number <= 2099) ? $number : null;
    }
    if ($key === 'page') {
        return max(1, $number);
    }
    if ($key === 'per_page' || $key === 'limit') {
        return DdysOpen_IntRange($number, 12, 1, 50);
    }
    return $number;
}

function DdysOpen_CacheOptions($atts)
{
    if (isset($atts['cache_ttl']) && $atts['cache_ttl'] !== '') {
        return array('cache_ttl' => (int) $atts['cache_ttl']);
    }
    return array();
}

function DdysOpen_Error($message, $status, $payload)
{
    return array('__ddys_error' => true, 'message' => (string) $message, 'status' => (int) $status, 'payload' => $payload);
}

function DdysOpen_IsError($value)
{
    return is_array($value) && !empty($value['__ddys_error']);
}

function DdysOpen_IsSuccessResponse($payload)
{
    return is_array($payload) && (!array_key_exists('success', $payload) || (bool) $payload['success'] === true);
}

function DdysOpen_PayloadData($payload)
{
    return is_array($payload) && array_key_exists('data', $payload) ? $payload['data'] : $payload;
}

function DdysOpen_PayloadMeta($payload)
{
    return is_array($payload) && isset($payload['meta']) && is_array($payload['meta']) ? $payload['meta'] : array();
}

function DdysOpen_BuildQuery($params)
{
    $output = array();
    foreach ((array) $params as $key => $value) {
        if ($value === null || $value === '' || $value === array()) {
            continue;
        }
        $output[$key] = $value;
    }
    ksort($output);
    return $output;
}

function DdysOpen_NormalizeBaseUrl($url, $default)
{
    $url = trim((string) $url);
    if ($url === '') {
        return $default;
    }
    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
        return $default;
    }
    $scheme = strtolower($parts['scheme']);
    if ($scheme !== 'https' && $scheme !== 'http') {
        return $default;
    }
    return rtrim($url, '/');
}

function DdysOpen_IntRange($value, $default, $min, $max)
{
    $number = (int) $value;
    if ($number < $min) {
        return $default;
    }
    if ($number > $max) {
        return $max;
    }
    return $number;
}

function DdysOpen_Choice($value, $allowed, $default)
{
    $value = strtolower(trim((string) $value));
    return in_array($value, $allowed, true) ? $value : $default;
}

function DdysOpen_Bool($value)
{
    if (is_bool($value)) {
        return $value;
    }
    return in_array(strtolower((string) $value), array('1', 'true', 'yes', 'on'), true);
}

function DdysOpen_ArrayValue($array, $key, $default)
{
    return is_array($array) && array_key_exists($key, $array) ? $array[$key] : $default;
}

function DdysOpen_ArrayIsList($array)
{
    if (!is_array($array)) {
        return false;
    }
    $i = 0;
    foreach (array_keys($array) as $key) {
        if ($key !== $i) {
            return false;
        }
        $i++;
    }
    return true;
}

function DdysOpen_FilterEmpty($values)
{
    $output = array();
    foreach ((array) $values as $key => $value) {
        if (is_array($value)) {
            if (!empty($value)) {
                $output[$key] = $value;
            }
        } elseif ((string) $value !== '') {
            $output[$key] = (string) $value;
        }
    }
    return $output;
}

function DdysOpen_Html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function DdysOpen_Attr($value)
{
    return DdysOpen_Html($value);
}

function DdysOpen_AllowedResourceUrl($url)
{
    return preg_match('#^(https?|magnet|ed2k|thunder):#i', (string) $url) === 1;
}

function DdysOpen_TrimWords($text, $length)
{
    $text = trim(preg_replace('/\s+/u', ' ', (string) $text));
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text, 'UTF-8') > $length ? mb_substr($text, 0, $length, 'UTF-8') . '...' : $text;
    }
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function DdysOpen_Get($key, $default)
{
    if (function_exists('GetVars')) {
        $value = GetVars($key, 'GET');
        return $value === null ? $default : trim((string) $value);
    }
    return isset($_GET[$key]) ? trim((string) $_GET[$key]) : $default;
}

function DdysOpen_Post($key, $default)
{
    if (function_exists('GetVars')) {
        $value = GetVars($key, 'POST');
        return $value === null ? $default : trim((string) $value);
    }
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function DdysOpen_CurrentUrl()
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    return $host ? ($scheme . '://' . $host . $uri) : $uri;
}
