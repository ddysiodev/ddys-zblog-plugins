# DDYS Z-Blog 插件合集

[English](README.md) | 中文

这是 [低端影视](https://ddys.io/) 面向两套 Z-Blog 运行环境提供的插件合集。仓库里保留两个独立插件目录，分别给 Z-BlogPHP 和 Z-BlogASP 使用。

- 官方网站：[低端影视](https://ddys.io/)
- GitHub 仓库：[ddysiodev/ddys-zblog-plugins](https://github.com/ddysiodev/ddys-zblog-plugins)
- Z-BlogPHP 插件：[`zblogphp/DdysOpen`](zblogphp/DdysOpen/)
- Z-BlogASP 插件：[`zblogasp/DdysOpen`](zblogasp/DdysOpen/)

## 应该复制哪个目录

| 运行环境 | 复制这个目录 | 放到 Z-Blog 里的位置 |
| --- | --- | --- |
| Z-BlogPHP | `zblogphp/DdysOpen` | `zb_users/plugin/DdysOpen` |
| Z-BlogASP | `zblogasp/DdysOpen` | `zb_users/PLUGIN/DdysOpen` |

两套插件不要混装。它们使用同一个插件 ID、同一个显示名称、同一套 DDYS 功能口径和图标，但 PHP 版与 Classic ASP 版代码完全独立。

## 插件能做什么

这两个插件用于在 Z-Blog 站点里展示低端影视内容，让站长通过后台配置和短代码调用接口内容，不需要手动处理接口 JSON。

前台展示能力：

- 最新影片、热门影片。
- 影片列表、搜索。
- 影片日历。
- 影片详情、资源、相关推荐、评论。
- 片单、分享。
- 求片、动态、用户、类型、题材、地区。
- 服务端求片表单。
- 可直接放进导航菜单的公开展示页。

后台管理能力：

- 配置 API Base URL。
- 配置可选 API Key，用于求片等写入接口。
- 配置最新、列表、详情、字典、社区数据的缓存时间。
- 配置布局、主题、列数、链接打开方式和来源链接。
- 清理缓存。
- 生成短代码。
- PHP 版提供接口连通性检查；ASP 版提供基础配置与缓存管理。

## 安装

### Z-BlogPHP

1. 把 `zblogphp/DdysOpen` 复制到 `zb_users/plugin/DdysOpen`。
2. 进入 Z-BlogPHP 后台。
3. 启用 `DDYS` 插件。
4. 打开后台 `DDYS` 菜单。
5. 如果使用自己的接口地址，修改 API Base URL；否则保留默认值。
6. 保存配置，并在后台执行一次接口测试。

详细说明：[Z-BlogPHP English](zblogphp/README.md) / [Z-BlogPHP 中文说明](zblogphp/README.zh-CN.md)

### Z-BlogASP

1. 把 `zblogasp/DdysOpen` 复制到 `zb_users/PLUGIN/DdysOpen`。
2. 进入 Z-BlogASP 后台。
3. 启用 `DDYS` 插件。
4. 打开 `DDYS` 后台页并保存配置。
5. 在文章、页面、模块中添加短代码，或把公开展示页链接加入导航。

详细说明：[Z-BlogASP English](zblogasp/README.md) / [Z-BlogASP 中文说明](zblogasp/README.zh-CN.md)

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

PHP 版会在服务端直接渲染短代码。ASP 版通过本地 `api.asp` 代理读取公开数据，再由前台脚本渲染组件；涉及 API Key 的求片提交仍然走服务端。

## 公开展示页

如果不想把组件嵌入文章，也可以把插件自带页面加入站点导航。

Z-BlogPHP 示例：

```text
zb_users/plugin/DdysOpen/page.php?view=latest
zb_users/plugin/DdysOpen/page.php?view=hot
zb_users/plugin/DdysOpen/page.php?view=search
zb_users/plugin/DdysOpen/page.php?view=calendar
zb_users/plugin/DdysOpen/page.php?view=movie&slug=this-tempting-madness
zb_users/plugin/DdysOpen/page.php?view=collections
zb_users/plugin/DdysOpen/page.php?view=requests
```

Z-BlogASP 示例：

```text
zb_users/plugin/DdysOpen/page.asp?view=latest
zb_users/plugin/DdysOpen/page.asp?view=hot
zb_users/plugin/DdysOpen/page.asp?view=search
zb_users/plugin/DdysOpen/page.asp?view=calendar
zb_users/plugin/DdysOpen/page.asp?view=collections
zb_users/plugin/DdysOpen/page.asp?view=requests
```

## 安全与边界处理

- API Key 只在插件后台配置。
- 求片表单通过站点服务端提交。
- 公开代理接口使用路由白名单。
- 详情类路由会先校验必需的 `slug`、`id` 或 `username`。
- 失败响应不会写入缓存。
- 前台输出会做转义。
- 缓存文件存放在插件或 Z-Blog 缓存目录中，按运行环境区分。

## 本地检查

在本目录运行：

```bash
node tools/check.mjs
node --test tests/*.test.mjs
node zblogphp/tools/check.mjs
node --test zblogphp/tests/*.test.mjs
node zblogasp/tools/check.mjs
node --test zblogasp/tests/*.test.mjs
```

这些检查会覆盖合并仓库目录形态、必需插件文件、图标尺寸、短代码覆盖、路由拦截、缓存边界，以及是否误带临时文件或敏感文本。
