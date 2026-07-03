# DDYS Z-BlogPHP Plugin

English | [中文](README.zh-CN.md)

Official Z-BlogPHP plugin for embedding [DDYS](https://ddys.io/) content in a Z-BlogPHP site.

- Repository: [ddysiodev/ddys-zblog-plugins](https://github.com/ddysiodev/ddys-zblog-plugins)
- Combined project README: [../README.md](../README.md)
- Plugin directory: [`DdysOpen`](DdysOpen/)
- Install destination: `zb_users/plugin/DdysOpen`

## Features

Display features:

- Latest and hot movie blocks.
- Movie list, search, and search suggestions.
- Calendar view.
- Movie detail page widgets.
- Source, related movie, and comment widgets.
- Collection, share, request, activity, user, type, genre, and region widgets.
- Request form shortcode for authenticated submissions.
- Standalone public pages through `DdysOpen/page.php`.

Admin features:

- API Base URL setting.
- Optional API Key for request submission.
- Separate cache TTL settings for fresh data, lists, details, dictionaries, and community data.
- Theme, layout, column, link target, and source-link settings.
- Shortcode generator.
- Cache cleanup.
- Connection test.

Runtime behavior:

- Parses shortcodes in post and page content.
- Parses shortcodes in modules where Z-BlogPHP exposes the content.
- Adds a final output-buffer fallback for themes that bypass normal content rendering.
- Keeps request-form submissions on the site server through `DdysOpen/request.php`.
- Stores cache files under `zb_users/cache/ddysopen`.

## Installation

1. Copy the `DdysOpen` directory into `zb_users/plugin/DdysOpen`.
2. Sign in to the Z-BlogPHP admin panel.
3. Open the plugin management page and enable `DDYS`.
4. Open the `DDYS` admin menu.
5. Review API Base URL, cache settings, display settings, and request-form settings.
6. Save settings.
7. Run the connection test from the diagnostics area.

## Settings

| Setting | Purpose |
| --- | --- |
| API Base URL | DDYS API endpoint or your own proxy endpoint. |
| API Key | Used only for authenticated request submissions. |
| Timeout | Maximum request time before returning an error. |
| Dictionary cache TTL | Types, genres, regions, and calendar-related data. |
| Fresh cache TTL | Latest and hot widgets. |
| List cache TTL | Movie, search, collection, share, and request lists. |
| Detail cache TTL | Movie, source, related, collection, and share detail data. |
| Community cache TTL | Comments, requests, and activities. |
| Theme and layout | Frontend card style and arrangement. |
| Source link | Whether rendered cards link back to DDYS. |
| Request form | Enables or disables the public request form. |

## Shortcodes

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

Common attributes:

| Attribute | Used by | Example |
| --- | --- | --- |
| `type` | latest, movies, search, activities | `type="movie"` |
| `limit` | latest, hot, movies | `limit="12"` |
| `page` | paged lists | `page="2"` |
| `per_page` | paged lists and comments | `per_page="20"` |
| `slug` | movie, sources, related, comments, collection | `slug="this-tempting-madness"` |
| `id` | share detail | `id="1"` |
| `username` | user detail | `username="demo"` |
| `layout` | rendered widgets | `layout="grid"` |
| `target` | rendered links | `target="_blank"` |

## Public Pages

```text
zb_users/plugin/DdysOpen/page.php?view=latest
zb_users/plugin/DdysOpen/page.php?view=hot
zb_users/plugin/DdysOpen/page.php?view=search
zb_users/plugin/DdysOpen/page.php?view=calendar
zb_users/plugin/DdysOpen/page.php?view=movie&slug=this-tempting-madness
zb_users/plugin/DdysOpen/page.php?view=collections
zb_users/plugin/DdysOpen/page.php?view=requests
```

These pages are useful when a theme navigation item should open a DDYS section directly.

## Security And Edge Cases

- Admin actions require Z-BlogPHP admin access.
- Settings saves use the Z-BlogPHP CSRF token helpers when available.
- Frontend output is escaped.
- Request-form submissions are rate limited.
- API Key is never printed into frontend JavaScript.
- Detail shortcodes return a readable error when required attributes are missing.
- Cache can be disabled per setting by using `0` seconds.
- Network errors return an error box instead of breaking the whole page.

## Local Checks

Run from `zblogphp`:

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```

Run from the combined repository root for the full PHP plus ASP check:

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```
