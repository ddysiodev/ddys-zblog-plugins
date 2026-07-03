# DDYS Z-BlogASP Plugin

Classic ASP plugin for embedding DDYS Open API content in Z-BlogASP.

## Features

- Native Z-BlogASP plugin structure: `plugin.xml`, `include.asp`, `main.asp`, `savesetting.asp`, `function.asp`.
- Public read widgets are rendered by `assets/js/frontend.js` through the server-side `api.asp` cache proxy.
- Request submission is handled by `request.asp`, so the DDYS API key is not exposed in browser code.
- Supports shortcodes in article template tags and standalone pages under `DdysOpen/page.asp`.
- No Composer, npm, or CDN runtime dependency.

## Installation

1. Copy `DdysOpen` to `zb_users/PLUGIN/DdysOpen`.
2. Enable the `DDYS` plugin in the Z-BlogASP admin panel.
3. Open the DDYS admin page and save settings.
4. Add shortcodes to posts, pages, or modules.

## Shortcodes

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

## Public Pages

```text
zb_users/plugin/DdysOpen/page.asp?view=latest
zb_users/plugin/DdysOpen/page.asp?view=hot
zb_users/plugin/DdysOpen/page.asp?view=search
zb_users/plugin/DdysOpen/page.asp?view=calendar
zb_users/plugin/DdysOpen/page.asp?view=collections
```

## Checks

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```
