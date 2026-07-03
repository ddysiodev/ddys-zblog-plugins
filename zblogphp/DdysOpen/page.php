<?php
require '../../../zb_system/function/c_system_base.php';
$zbp->Load();
require_once dirname(__FILE__) . '/function.php';

$settings = DdysOpen_GetSettings();
$view = DdysOpen_Get('view', 'latest');
$titleMap = array(
    'latest' => 'Latest Movies',
    'hot' => '鐑棬褰辩墖',
    'search' => '鎼滅储',
    'calendar' => '褰辩墖鏃ュ巻',
    'movie' => '褰辩墖璇︽儏',
    'collections' => '鐗囧崟',
    'requests' => '姹傜墖',
);
$title = isset($titleMap[$view]) ? $titleMap[$view] : 'DDYS';
$params = array(
    'limit' => DdysOpen_Get('limit', 12),
    'q' => DdysOpen_Get('q', DdysOpen_Get('ddys_q', '')),
    'type' => DdysOpen_Get('type', DdysOpen_Get('ddys_type', 'movie')),
    'year' => DdysOpen_Get('year', ''),
    'month' => DdysOpen_Get('month', ''),
    'slug' => DdysOpen_Get('slug', ''),
    'page' => DdysOpen_Get('page', 1),
);
$content = DdysOpen_RenderRoute($view, $params);
?><!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo DdysOpen_Html($title); ?> - DDYS</title>
  <link rel="stylesheet" href="assets/css/frontend.css?v=<?php echo DDYSOPEN_VERSION; ?>">
  <script defer src="assets/js/frontend.js?v=<?php echo DDYSOPEN_VERSION; ?>"></script>
</head>
<body class="ddys-zbp-page">
  <main class="ddys-zbp-page-main">
    <header class="ddys-zbp-page-header">
      <a href="<?php echo DdysOpen_Attr($zbp->host); ?>"><?php echo DdysOpen_Html($zbp->name); ?></a>
      <h1><?php echo DdysOpen_Html($title); ?></h1>
      <nav>
        <a href="?view=latest">Latest</a>
        <a href="?view=hot">鐑棬</a>
        <a href="?view=search">鎼滅储</a>
        <a href="?view=calendar">鏃ュ巻</a>
        <a href="?view=collections">鐗囧崟</a>
      </nav>
    </header>
    <?php echo $content; ?>
  </main>
</body>
</html>
