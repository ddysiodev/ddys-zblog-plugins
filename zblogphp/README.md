# DDYS Z-BlogPHP Plugin

[中文](README.zh-CN.md) | English

Official Z-BlogPHP plugin for the [DDYS](https://ddys.io/) API.

## Features

- Works as a native Z-BlogPHP plugin under `zb_users/plugin/DdysOpen`.
- Displays latest, hot, movie lists, search, suggestions, calendar, movie details, sources, related movies, comments, collections, shares, requests, activities, users, types, genres, and regions.
- Provides a Z-BlogPHP admin page for API Base URL, cache TTLs, display style, source links, API key, request form, diagnostics, and cache cleanup.
- Parses DDYS shortcodes in posts, pages, modules, and the final page output as a fallback.
- Includes standalone public pages under `DdysOpen/page.php`.
- Uses server-side cache files in `zb_users/cache/ddysopen`.
- Keeps write operations server-side through `DdysOpen/request.php`.
- Uses no external runtime dependencies.

## Installation

1. Copy the `DdysOpen` directory to `zb_users/plugin/DdysOpen`.
2. Open the Z-BlogPHP admin panel.
3. Enable the `DDYS` plugin.
4. Open `DDYS` from the admin menu and click `测试 DDYS 接口`.

## Shortcodes

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

## Compatibility

- Z-BlogPHP plugin structure.
- PHP 5.6+.
- cURL is preferred; PHP stream fallback is included.

## Development Checks

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```
