# DDYS Z-BlogASP 插件

这是面向 Z-BlogASP 的 Classic ASP 插件，用于嵌入 [低端影视](https://ddys.io/) 接口内容。

## 功能

- 原生 Z-BlogASP 插件结构：`plugin.xml`、`include.asp`、`main.asp`、`savesetting.asp`、`function.asp`。
- 公开读取组件通过 `api.asp` 服务端缓存代理读取 DDYS JSON，再由前台 JS 渲染。
- 求片表单通过 `request.asp` 服务端提交，API Key 不暴露到浏览器。
- 支持文章模板标签里的短代码，也提供 `DdysOpen/page.asp` 独立展示页。
- 不依赖 Composer、npm 或第三方 CDN。

## 安装

1. 把 `DdysOpen` 目录复制到 `zb_users/PLUGIN/DdysOpen`。
2. 进入 Z-BlogASP 后台启用 `DDYS` 插件。
3. 打开 DDYS 后台页面保存配置。
4. 在文章、页面或模块中添加短代码。

## 短代码

```text
[ddys_latest limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_calendar]
[ddys_movie slug="this-tempting-madness"]
[ddys_sources slug="this-tempting-madness"]
[ddys_collections per_page="12"]
[ddys_request_form]
```

## 公开页面

```text
zb_users/plugin/DdysOpen/page.asp?view=latest
zb_users/plugin/DdysOpen/page.asp?view=hot
zb_users/plugin/DdysOpen/page.asp?view=search
zb_users/plugin/DdysOpen/page.asp?view=calendar
zb_users/plugin/DdysOpen/page.asp?view=collections
```
