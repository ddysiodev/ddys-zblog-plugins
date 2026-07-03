# DDYS Z-BlogASP 插件

[English](README.md) | 中文

这是用于在 Z-BlogASP 站点中嵌入 [低端影视](https://ddys.io/) 内容的 Classic ASP 插件。

- GitHub 仓库：[ddysiodev/ddys-zblog-plugins](https://github.com/ddysiodev/ddys-zblog-plugins)
- 插件合集说明：[../README.zh-CN.md](../README.zh-CN.md)
- 插件目录：[`DdysOpen`](DdysOpen/)
- 安装位置：`zb_users/PLUGIN/DdysOpen`

## 功能

前台展示：

- 最新影片、热门影片。
- 搜索组件。
- 影片日历组件。
- 影片详情和资源组件。
- 片单、求片和列表组件。
- 服务端求片表单。
- `DdysOpen/page.asp` 独立公开展示页。

后台管理：

- API Base URL 配置。
- 可选 API Key，用于求片等写入接口。
- 缓存时间配置。
- 主题、布局、列数、链接打开方式、来源链接配置。
- 短代码生成器。
- 缓存清理。

运行方式：

- `include.asp` 注册插件、后台菜单、设置子菜单、后台样式和文章模板标签短代码过滤。
- `api.asp` 作为本地公开数据代理和缓存层。
- `request.asp` 在服务端处理求片表单 POST 提交。
- `assets/js/frontend.js` 根据 `api.asp` 返回的 JSON 渲染前台组件。
- `function.asp` 包含配置、路由白名单、缓存、请求、短代码解析、转义和限流逻辑。

## 安装

1. 把 `DdysOpen` 目录复制到 `zb_users/PLUGIN/DdysOpen`。
2. 登录 Z-BlogASP 后台。
3. 启用 `DDYS` 插件。
4. 打开 `DDYS` 后台页。
5. 检查 API Base URL、缓存、展示和求片表单配置。
6. 保存配置。
7. 在文章、页面、模块中添加短代码，或把公开展示页加入导航。

## 配置项

| 配置 | 作用 |
| --- | --- |
| API Base URL | 低端影视接口地址，也可以填写自己的代理地址。 |
| Site Base URL | 可选站点地址，用于某些主题需要绝对插件链接的场景。 |
| API Key | 仅用于需要鉴权的求片提交。 |
| Timeout | 请求接口的超时时间。 |
| Cache TTL | 默认公开数据缓存时间。 |
| Fresh cache TTL | 最新、热门组件缓存。 |
| Detail cache TTL | 影片、资源、片单、分享详情缓存。 |
| Theme 和 Layout | 前台卡片样式与排列方式。 |
| Columns | 建议的网格列数。 |
| Link target | 链接打开方式，例如 `_blank`。 |
| Source link | 是否显示指向低端影视的来源链接。 |
| Request form | 是否启用前台求片表单。 |
| Request interval | 同一 IP 重复提交求片的基础间隔。 |

## 短代码

```text
[ddys_latest limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_calendar year="2026" month="7"]
[ddys_movie slug="this-tempting-madness"]
[ddys_sources slug="this-tempting-madness"]
[ddys_related slug="this-tempting-madness"]
[ddys_comments slug="this-tempting-madness" per_page="20"]
[ddys_collections per_page="12"]
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
| `type` | 最新、搜索、动态 | `type="movie"` |
| `limit` | 最新、热门组件 | `limit="12"` |
| `page` | 分页列表 | `page="2"` |
| `per_page` | 分页列表、评论 | `per_page="20"` |
| `slug` | 影片、资源、相关推荐、评论、片单 | `slug="this-tempting-madness"` |
| `id` | 分享详情 | `id="1"` |
| `username` | 用户详情 | `username="demo"` |
| `layout` | 组件布局 | `layout="grid"` |
| `target` | 链接打开方式 | `target="_blank"` |

## 公开展示页

```text
zb_users/plugin/DdysOpen/page.asp?view=latest
zb_users/plugin/DdysOpen/page.asp?view=hot
zb_users/plugin/DdysOpen/page.asp?view=search
zb_users/plugin/DdysOpen/page.asp?view=calendar
zb_users/plugin/DdysOpen/page.asp?view=collections
zb_users/plugin/DdysOpen/page.asp?view=requests
zb_users/plugin/DdysOpen/page.asp?view=movie&slug=this-tempting-madness
```

这些页面适合直接加入主题导航，作为站点里的低端影视专区。

## 安全与边界处理

- 后台配置通过插件后台页保存。
- API Key 存在服务端，只由 `request.asp` 使用。
- `api.asp` 只接受白名单里的公开路由。
- 详情类路由会先校验必需的 `slug`、`id` 或 `username`。
- 低端影视接口失败响应会返回给浏览器，但不会写入缓存。
- 求片表单按 IP 做基础限流。
- 短代码会注入前台资源，兼容没有专门插件头部钩子的主题。
- JSON 字符串和 HTML 输出会先转义再渲染。

## 本地检查

在 `zblogasp` 目录运行：

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```

在插件合集根目录运行完整检查：

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```
