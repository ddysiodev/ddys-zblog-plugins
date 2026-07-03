# DDYS Z-BlogPHP 插件

中文 | [English](README.md)

[低端影视](https://ddys.io/) 接口的官方 Z-BlogPHP 插件。

## 功能

- 原生 Z-BlogPHP 插件目录结构，安装目录为 `zb_users/plugin/DdysOpen`。
- 支持最新、热门、影片列表、搜索、搜索建议、影片日历、影片详情、播放和下载资源、相关影片、评论、片单、分享、求片、动态、用户、类型、题材、地区。
- 后台可配置 API Base URL、缓存时间、展示样式、来源链接、API Key、求片表单、诊断和缓存清理。
- 自动解析文章、页面、模块里的 DDYS 短代码，并带页面输出兜底解析。
- 提供 `DdysOpen/page.php` 公开页面，站长可以直接放到导航里。
- 使用 `zb_users/cache/ddysopen` 文件缓存，减少对接口的重复请求。
- 求片等写入功能走站点服务端，不在浏览器里暴露 API Key。
- 无第三方运行依赖。

## 安装

1. 将 `DdysOpen` 目录复制到 `zb_users/plugin/DdysOpen`。
2. 进入 Z-BlogPHP 后台。
3. 启用 `DDYS` 插件。
4. 打开后台 `DDYS` 菜单，先点一次 `测试 DDYS 接口`。

## 常用短代码

```text
[ddys_latest type="movie" limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_calendar year="2026" month="7"]
[ddys_movie slug="this-tempting-madness"]
[ddys_sources slug="this-tempting-madness"]
[ddys_related slug="this-tempting-madness"]
[ddys_comments slug="this-tempting-madness" per_page="20"]
[ddys_collections per_page="10"]
[ddys_collection slug="best-sci-fi" per_page="12"]
[ddys_shares per_page="10"]
[ddys_share id="1"]
[ddys_requests per_page="10"]
[ddys_activities per_page="10"]
[ddys_user username="demo"]
[ddys_types]
[ddys_genres]
[ddys_regions]
[ddys_request_form]
```

后台带短代码生成器，可以选择类型、slug、数量、布局后直接复制。

## 公开页面

```text
zb_users/plugin/DdysOpen/page.php?view=latest
zb_users/plugin/DdysOpen/page.php?view=hot
zb_users/plugin/DdysOpen/page.php?view=search
zb_users/plugin/DdysOpen/page.php?view=calendar
zb_users/plugin/DdysOpen/page.php?view=movie&slug=this-tempting-madness
zb_users/plugin/DdysOpen/page.php?view=collections
zb_users/plugin/DdysOpen/page.php?view=requests
```

## 缓存建议

- 类型、题材、地区：24 小时。
- 最新、热门：5 分钟。
- 列表和搜索：10 分钟。
- 详情和资源：30 分钟。
- 评论、求片、动态：2 分钟。

## 环境

- Z-BlogPHP 插件结构。
- PHP 5.6+。
- 优先使用 cURL，请求不可用时会尝试 PHP stream。

## 本地检查

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```
