# DDYS Z-BlogASP Plugin

English | [中文](README.zh-CN.md)

Classic ASP plugin for embedding [DDYS](https://ddys.io/) content in a Z-BlogASP site.

- Repository: [ddysiodev/ddys-zblog-plugins](https://github.com/ddysiodev/ddys-zblog-plugins)
- Combined project README: [../README.md](../README.md)
- Plugin directory: [`DdysOpen`](DdysOpen/)
- Install destination: `zb_users/PLUGIN/DdysOpen`

## Features

Display features:

- Latest and hot movie widgets.
- Search widget.
- Calendar widget.
- Movie detail and source widgets.
- Collection, request, and list widgets.
- Server-side request form.
- Standalone public display pages through `DdysOpen/page.asp`.

Admin features:

- API Base URL setting.
- Optional API Key for request submission.
- Cache TTL settings.
- Theme, layout, column, target, and source-link settings.
- Shortcode generator.
- Cache cleanup.

Runtime behavior:

- `include.asp` registers the plugin, admin menu, settings submenu, admin stylesheet, and article template tag shortcode filter.
- `api.asp` acts as the local public-data proxy and cache layer.
- `request.asp` handles request-form POST submissions on the server.
- `assets/js/frontend.js` renders widgets from the JSON returned by `api.asp`.
- `function.asp` contains config, route allowlist, cache helpers, request helpers, shortcode parsing, escaping, and rate limiting.

## Installation

1. Copy the `DdysOpen` directory into `zb_users/PLUGIN/DdysOpen`.
2. Sign in to the Z-BlogASP admin panel.
3. Enable the `DDYS` plugin.
4. Open the `DDYS` admin page.
5. Review API Base URL, cache settings, display settings, and request-form settings.
6. Save settings.
7. Add shortcodes to posts, pages, modules, or link the public display pages.

## Settings

| Setting | Purpose |
| --- | --- |
| API Base URL | DDYS API endpoint or your own proxy endpoint. |
| Site Base URL | Optional site URL used when a theme needs absolute plugin links. |
| API Key | Used only for authenticated request submissions. |
| Timeout | Maximum request time before returning an error. |
| Cache TTL | Default public data cache time. |
| Fresh cache TTL | Latest and hot widgets. |
| Detail cache TTL | Movie, source, collection, and share details. |
| Theme and layout | Frontend card style and arrangement. |
| Columns | Suggested grid column count. |
| Link target | Rendered link target, such as `_blank`. |
| Source link | Whether rendered cards link back to DDYS. |
| Request form | Enables or disables the public request form. |
| Request interval | Basic interval limit for repeated request-form submissions. |

## Shortcodes

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

Common attributes:

| Attribute | Used by | Example |
| --- | --- | --- |
| `type` | latest, search, activities | `type="movie"` |
| `limit` | latest and hot widgets | `limit="12"` |
| `page` | paged lists | `page="2"` |
| `per_page` | paged lists and comments | `per_page="20"` |
| `slug` | movie, sources, related, comments, collection | `slug="this-tempting-madness"` |
| `id` | share detail | `id="1"` |
| `username` | user detail | `username="demo"` |
| `layout` | rendered widgets | `layout="grid"` |
| `target` | rendered links | `target="_blank"` |

## Public Pages

```text
zb_users/plugin/DdysOpen/page.asp?view=latest
zb_users/plugin/DdysOpen/page.asp?view=hot
zb_users/plugin/DdysOpen/page.asp?view=search
zb_users/plugin/DdysOpen/page.asp?view=calendar
zb_users/plugin/DdysOpen/page.asp?view=collections
zb_users/plugin/DdysOpen/page.asp?view=requests
zb_users/plugin/DdysOpen/page.asp?view=movie&slug=this-tempting-madness
```

Use these URLs when a theme navigation item should open a dedicated DDYS section.

## Security And Edge Cases

- Admin settings are saved through the plugin admin page.
- API Key is stored server-side and used only by `request.asp`.
- `api.asp` only accepts allowlisted public routes.
- Detail routes validate required `slug`, `id`, or `username` values before calling DDYS.
- Failed DDYS responses are returned to the browser but are not written into cache.
- Request-form submissions are rate limited by IP.
- Frontend assets are injected by shortcodes so widgets still work in themes without a dedicated plugin header hook.
- JSON strings and HTML output are escaped before rendering.

## Local Checks

Run from `zblogasp`:

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```

Run from the combined repository root for the full PHP plus ASP check:

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```
