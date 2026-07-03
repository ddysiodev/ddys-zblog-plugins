# DDYS Z-Blog Plugins

English | [中文](README.zh-CN.md)

Official DDYS plugins for the two Z-Blog runtimes, published in one repository with two independent installable plugin directories.

- Official site: [DDYS](https://ddys.io/)
- Repository: [ddysiodev/ddys-zblog-plugins](https://github.com/ddysiodev/ddys-zblog-plugins)
- Z-BlogPHP plugin: [`zblogphp/DdysOpen`](zblogphp/DdysOpen/)
- Z-BlogASP plugin: [`zblogasp/DdysOpen`](zblogasp/DdysOpen/)

## Which Directory To Use

| Runtime | Copy this directory | Destination in Z-Blog |
| --- | --- | --- |
| Z-BlogPHP | `zblogphp/DdysOpen` | `zb_users/plugin/DdysOpen` |
| Z-BlogASP | `zblogasp/DdysOpen` | `zb_users/PLUGIN/DdysOpen` |

Do not mix the two runtime directories. They share the same plugin ID, display name, DDYS feature set, icons, and shortcode naming, but the PHP and Classic ASP implementations are separate.

## What The Plugins Do

Both plugins help a Z-Blog site display DDYS content through the site server instead of asking editors to paste raw API responses into templates.

Core display features:

- Latest and hot movie widgets.
- Movie list and search widgets.
- Calendar view.
- Movie detail, source, related, and comment widgets.
- Collection and share widgets.
- Request, activity, user, type, genre, and region widgets.
- A server-side request form for authenticated request submissions.
- Public display pages that can be linked from navigation menus.

Admin features:

- API Base URL setting.
- Optional API Key for request submission.
- Cache time settings for fresh, list, detail, dictionary, and community data.
- Layout, theme, column, target, and source-link display settings.
- Cache cleanup.
- Shortcode generator.
- Basic diagnostics or connection checks where the runtime supports them.

## Install

### Z-BlogPHP

1. Copy `zblogphp/DdysOpen` to `zb_users/plugin/DdysOpen`.
2. Open the Z-BlogPHP admin panel.
3. Enable the `DDYS` plugin.
4. Open the `DDYS` admin menu.
5. Set the API Base URL if you use a custom endpoint; otherwise keep the default.
6. Save settings and run the connection check from the admin page.

More details: [Z-BlogPHP README](zblogphp/README.md) / [Z-BlogPHP 中文说明](zblogphp/README.zh-CN.md)

### Z-BlogASP

1. Copy `zblogasp/DdysOpen` to `zb_users/PLUGIN/DdysOpen`.
2. Open the Z-BlogASP admin panel.
3. Enable the `DDYS` plugin.
4. Open the `DDYS` admin page and save settings.
5. Add shortcodes to posts, pages, modules, or link the public display pages.

More details: [Z-BlogASP README](zblogasp/README.md) / [Z-BlogASP 中文说明](zblogasp/README.zh-CN.md)

## Common Shortcodes

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

The PHP version renders these shortcodes directly on the server. The ASP version renders widgets through its local `api.asp` proxy and frontend script, while keeping private write operations on the server.

## Public Pages

The plugins also provide standalone display pages for sites that prefer linking to a dedicated DDYS section instead of embedding widgets inside posts.

Z-BlogPHP examples:

```text
zb_users/plugin/DdysOpen/page.php?view=latest
zb_users/plugin/DdysOpen/page.php?view=hot
zb_users/plugin/DdysOpen/page.php?view=search
zb_users/plugin/DdysOpen/page.php?view=calendar
zb_users/plugin/DdysOpen/page.php?view=movie&slug=this-tempting-madness
zb_users/plugin/DdysOpen/page.php?view=collections
zb_users/plugin/DdysOpen/page.php?view=requests
```

Z-BlogASP examples:

```text
zb_users/plugin/DdysOpen/page.asp?view=latest
zb_users/plugin/DdysOpen/page.asp?view=hot
zb_users/plugin/DdysOpen/page.asp?view=search
zb_users/plugin/DdysOpen/page.asp?view=calendar
zb_users/plugin/DdysOpen/page.asp?view=collections
zb_users/plugin/DdysOpen/page.asp?view=requests
```

## Safety Notes

- Configure the API Key only in the plugin admin page.
- Request-form submissions are sent through the site server.
- Public proxy routes are allowlisted.
- Detail routes validate required identifiers before calling DDYS.
- Failed DDYS responses are not written into cache.
- Output is escaped before rendering.
- Cache files are kept inside the plugin or Z-Blog cache area, depending on the runtime.

## Local Checks

Run from this repository directory:

```bash
node tools/check.mjs
node --test tests/*.test.mjs
node zblogphp/tools/check.mjs
node --test zblogphp/tests/*.test.mjs
node zblogasp/tools/check.mjs
node --test zblogasp/tests/*.test.mjs
```

These checks verify the combined repository shape, required plugin files, copied icon sizes, shortcode coverage, route guards, cache behavior, and accidental temporary or secret files.
