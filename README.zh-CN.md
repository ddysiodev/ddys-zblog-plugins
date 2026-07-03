# DDYS Z-Blog 插件合集

这是 [低端影视](https://ddys.io/) 接口的 Z-Blog 插件合集，一个仓库里同时提供 Z-BlogPHP 和 Z-BlogASP 两套插件。

## 目录

| 目录 | 运行环境 | 安装位置 |
| --- | --- | --- |
| `zblogphp/DdysOpen` | Z-BlogPHP | `zb_users/plugin/DdysOpen` |
| `zblogasp/DdysOpen` | Z-BlogASP | `zb_users/PLUGIN/DdysOpen` |

两套插件不要混装。PHP 版和 ASP 版功能口径一致，但运行时代码完全独立。

## 功能

- 最新、热门、影片列表、搜索、日历、详情、资源、片单、分享、求片、动态、用户、类型、题材、地区。
- 后台配置 API Base URL、缓存时间、展示样式、来源链接、API Key、求片表单。
- 短代码：`[ddys_latest limit="12"]`、`[ddys_search]`、`[ddys_calendar]`、`[ddys_movie slug="..."]`、`[ddys_request_form]`。
- 写入功能走服务端，API Key 不暴露到浏览器。
- 图标来自主站 `public/icons`。
- 不依赖 Composer、npm 或第三方 CDN。

## 检查

```bash
node tools/check.mjs
node zblogphp/tools/check.mjs
node --test tests/*.test.mjs
node --test zblogphp/tests/*.test.mjs
node --test zblogasp/tests/*.test.mjs
```
