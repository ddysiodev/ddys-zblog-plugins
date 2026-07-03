# DDYS Z-BlogPHP 插件

[English](README.md) | 中文

这是用于在 Z-BlogPHP 站点中嵌入 [低端影视](https://ddys.io/) 内容的官方插件。

- GitHub 仓库：[ddysiodev/ddys-zblog-plugins](https://github.com/ddysiodev/ddys-zblog-plugins)
- 插件合集说明：[../README.zh-CN.md](../README.zh-CN.md)
- 插件目录：[`DdysOpen`](DdysOpen/)
- 安装位置：`zb_users/plugin/DdysOpen`

## 功能

前台展示：

- 最新影片、热门影片。
- 影片列表、搜索、搜索建议。
- 影片日历。
- 影片详情组件。
- 资源、相关推荐、评论组件。
- 片单、分享、求片、动态、用户、类型、题材、地区。
- 用于提交求片的短代码表单。
- `DdysOpen/page.php` 独立公开展示页。

后台管理：

- API Base URL 配置。
- 可选 API Key，用于求片等写入接口。
- 最新、列表、详情、字典、社区数据的缓存时间配置。
- 主题、布局、列数、链接打开方式、来源链接配置。
- 短代码生成器。
- 缓存清理。
- 接口连通性测试。

运行方式：

- 自动解析文章和页面里的短代码。
- 在 Z-BlogPHP 暴露模块内容时解析模块短代码。
- 增加页面输出缓冲兜底，兼容绕过常规内容渲染的主题。
- 求片表单通过 `DdysOpen/request.php` 在站点服务端提交。
- 缓存文件存放在 `zb_users/cache/ddysopen`。

## 安装

1. 把 `DdysOpen` 目录复制到 `zb_users/plugin/DdysOpen`。
2. 登录 Z-BlogPHP 后台。
3. 进入插件管理，启用 `DDYS` 插件。
4. 打开后台 `DDYS` 菜单。
5. 检查 API Base URL、缓存、展示和求片表单配置。
6. 保存配置。
7. 在诊断区域执行一次接口测试。

## 配置项

| 配置 | 作用 |
| --- | --- |
| API Base URL | 低端影视接口地址，也可以填写自己的代理地址。 |
| API Key | 仅用于需要鉴权的求片提交。 |
| Timeout | 请求接口的超时时间。 |
| Dictionary cache TTL | 类型、题材、地区、日历等数据缓存。 |
| Fresh cache TTL | 最新、热门组件缓存。 |
| List cache TTL | 影片、搜索、片单、分享、求片列表缓存。 |
| Detail cache TTL | 影片、资源、相关推荐、片单、分享详情缓存。 |
| Community cache TTL | 评论、求片、动态缓存。 |
| Theme 和 Layout | 前台卡片样式与排列方式。 |
| Source link | 是否显示指向低端影视的来源链接。 |
| Request form | 是否启用前台求片表单。 |

## 短代码

```text
[ddys_movies type="movie" limit="12"]
[ddys_latest type="movie" limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_suggest q="dark"]
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

常用属性：

| 属性 | 用途 | 示例 |
| --- | --- | --- |
| `type` | 最新、影片列表、搜索、动态 | `type="movie"` |
| `limit` | 最新、热门、影片列表 | `limit="12"` |
| `page` | 分页列表 | `page="2"` |
| `per_page` | 分页列表、评论 | `per_page="20"` |
| `slug` | 影片、资源、相关推荐、评论、片单 | `slug="this-tempting-madness"` |
| `id` | 分享详情 | `id="1"` |
| `username` | 用户详情 | `username="demo"` |
| `layout` | 组件布局 | `layout="grid"` |
| `target` | 链接打开方式 | `target="_blank"` |

## 公开展示页

```text
zb_users/plugin/DdysOpen/page.php?view=latest
zb_users/plugin/DdysOpen/page.php?view=hot
zb_users/plugin/DdysOpen/page.php?view=search
zb_users/plugin/DdysOpen/page.php?view=calendar
zb_users/plugin/DdysOpen/page.php?view=movie&slug=this-tempting-madness
zb_users/plugin/DdysOpen/page.php?view=collections
zb_users/plugin/DdysOpen/page.php?view=requests
```

这些页面适合直接加入主题导航，作为站点里的低端影视专区。

## 安全与边界处理

- 后台保存配置需要管理员权限。
- 保存配置时会使用 Z-BlogPHP 可用的 CSRF 校验能力。
- 前台输出会做转义。
- 求片表单带基础限流。
- API Key 不会输出到前台 JavaScript。
- 详情短代码缺少必要属性时会显示可读错误。
- 缓存时间设为 `0` 可以关闭对应缓存。
- 接口网络异常时显示错误提示，不会中断整个页面。

## 本地检查

在 `zblogphp` 目录运行：

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```

在插件合集根目录运行完整检查：

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```
