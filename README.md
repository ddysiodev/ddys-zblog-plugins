# DDYS Z-Blog Plugins

Official DDYS plugins for both Z-Blog runtimes.

## Directories

| Directory | Runtime | Install directory |
| --- | --- | --- |
| `zblogphp/DdysOpen` | Z-BlogPHP | `zb_users/plugin/DdysOpen` |
| `zblogasp/DdysOpen` | Z-BlogASP | `zb_users/PLUGIN/DdysOpen` |

The two plugins are intentionally separate. They share the same product name and feature set, but they do not share runtime code.

## Features

- Latest, hot, movie lists, search, calendar, details, sources, collections, shares, requests, activities, users, types, genres, and regions.
- Admin settings for API Base URL, cache TTLs, display options, source links, and server-side request form.
- Shortcodes such as `[ddys_latest limit="12"]`, `[ddys_search]`, `[ddys_calendar]`, `[ddys_movie slug="..."]`, and `[ddys_request_form]`.
- API key stays server-side for write operations.
- Icons are copied from the DDYS site icon set.
- No Composer, npm, or CDN runtime dependency.

## Checks

```bash
node tools/check.mjs
node zblogphp/tools/check.mjs
node --test tests/*.test.mjs
node --test zblogphp/tests/*.test.mjs
node --test zblogasp/tests/*.test.mjs
```

## Source

Official site: [DDYS](https://ddys.io/)
