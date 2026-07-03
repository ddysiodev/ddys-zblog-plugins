<%@LANGUAGE="VBSCRIPT" CODEPAGE="65001"%>
<%Option Explicit%>
<%Response.Charset="UTF-8"%>
<!-- #include file="../../c_option.asp" -->
<!-- #include file="../../../zb_system/function/c_function.asp" -->
<!-- #include file="../../../zb_system/function/c_system_lib.asp" -->
<!-- #include file="../../../zb_system/function/c_system_base.asp" -->
<!-- #include file="../../../zb_system/function/c_system_event.asp" -->
<!-- #include file="../../../zb_system/function/c_system_plugin.asp" -->
<!-- #include file="../../plugin/p_config.asp" -->
<!-- #include file="function.asp" -->
<%
Call System_Initialize()
Call CheckReference("")
If BlogUser.Level > 1 Then Call ShowError(6)
If CheckPluginState("DdysOpen") = False Then Call ShowError(48)
Call DdysOpen_InitConfig()
Dim BlogTitle, tab, pageBase
BlogTitle = "DDYS"
tab = LCase(Trim(Request.QueryString("tab")))
If tab = "" Then tab = "settings"
pageBase = BlogHost & "zb_users/plugin/DdysOpen/page.asp"
If Request.QueryString("saved") <> "" Then Call SetBlogHint(True, Empty, True)
If Request.QueryString("cache") = "cleared" Then Call SetBlogHint(True, Empty, True)
%>
<!--#include file="..\..\..\zb_system\admin\admin_header.asp"-->
<link rel="stylesheet" type="text/css" href="assets/css/admin.css?v=<%=DDYSOPEN_VERSION%>">
<script defer src="assets/js/admin.js?v=<%=DDYSOPEN_VERSION%>"></script>
<title><%=BlogTitle%></title>
<!--#include file="..\..\..\zb_system\admin\admin_top.asp"-->
<div id="divMain" class="ddys-asp-admin">
	<div id="ShowBlogHint"><%Call GetBlogHint()%></div>
	<div class="divHeader"><img src="assets/images/icon-32.png" width="24" height="24" alt=""> DDYS</div>
	<div class="SubMenu">
		<a href="main.asp?tab=settings"><span class="m-left<%If tab="settings" Then Response.Write " m-now"%>">Settings</span></a>
		<a href="main.asp?tab=shortcodes"><span class="m-left<%If tab="shortcodes" Then Response.Write " m-now"%>">Shortcodes</span></a>
		<a href="main.asp?tab=pages"><span class="m-left<%If tab="pages" Then Response.Write " m-now"%>">Pages</span></a>
		<a href="main.asp?tab=cache"><span class="m-left<%If tab="cache" Then Response.Write " m-now"%>">Cache</span></a>
	</div>
	<div id="divMain2">
	<%If tab = "settings" Then%>
		<form method="post" action="savesetting.asp">
			<table class="tableFull tableBorder ddys-asp-settings">
				<tr><th colspan="2">API</th></tr>
				<tr><td class="td25">API Base URL</td><td><input name="api_base_url" class="ddys-asp-wide" type="text" value="<%=DdysOpen_Attr(DdysOpen_Config("api_base_url"))%>"><p class="note">Default: https://ddys.io/api/v1. You can use your own Worker proxy.</p></td></tr>
				<tr><td>Source Site URL</td><td><input name="site_base_url" class="ddys-asp-wide" type="text" value="<%=DdysOpen_Attr(DdysOpen_Config("site_base_url"))%>"></td></tr>
				<tr><td>Timeout</td><td><input name="timeout" type="number" min="1" max="30" value="<%=DdysOpen_Attr(DdysOpen_Config("timeout"))%>"> seconds</td></tr>
				<tr><th colspan="2">Cache</th></tr>
				<tr><td>Default Cache TTL</td><td><input name="cache_ttl" type="number" min="0" max="604800" value="<%=DdysOpen_Attr(DdysOpen_Config("cache_ttl"))%>"> seconds</td></tr>
				<tr><td>Latest/Hot Cache TTL</td><td><input name="fresh_cache_ttl" type="number" min="0" max="604800" value="<%=DdysOpen_Attr(DdysOpen_Config("fresh_cache_ttl"))%>"> seconds</td></tr>
				<tr><td>Detail Cache TTL</td><td><input name="detail_cache_ttl" type="number" min="0" max="604800" value="<%=DdysOpen_Attr(DdysOpen_Config("detail_cache_ttl"))%>"> seconds</td></tr>
				<tr><th colspan="2">Display</th></tr>
				<tr><td>Theme</td><td><select name="theme"><option value="auto"<%If DdysOpen_Config("theme")="auto" Then Response.Write " selected"%>>auto</option><option value="light"<%If DdysOpen_Config("theme")="light" Then Response.Write " selected"%>>light</option><option value="dark"<%If DdysOpen_Config("theme")="dark" Then Response.Write " selected"%>>dark</option></select></td></tr>
				<tr><td>Layout</td><td><select name="layout"><option value="grid"<%If DdysOpen_Config("layout")="grid" Then Response.Write " selected"%>>grid</option><option value="list"<%If DdysOpen_Config("layout")="list" Then Response.Write " selected"%>>list</option><option value="compact"<%If DdysOpen_Config("layout")="compact" Then Response.Write " selected"%>>compact</option></select></td></tr>
				<tr><td>Columns</td><td><input name="columns" type="number" min="1" max="6" value="<%=DdysOpen_Attr(DdysOpen_Config("columns"))%>"></td></tr>
				<tr><td>Link Target</td><td><select name="target"><option value="_blank"<%If DdysOpen_Config("target")="_blank" Then Response.Write " selected"%>>new window</option><option value="_self"<%If DdysOpen_Config("target")="_self" Then Response.Write " selected"%>>current window</option></select></td></tr>
				<tr><td>Load Frontend Assets</td><td><label><input name="enable_styles" type="checkbox" value="True"<%If DdysOpen_Bool(DdysOpen_Config("enable_styles")) Then Response.Write " checked"%>> enabled</label></td></tr>
				<tr><td>Show Source Link</td><td><label><input name="show_source_link" type="checkbox" value="True"<%If DdysOpen_Bool(DdysOpen_Config("show_source_link")) Then Response.Write " checked"%>> enabled</label></td></tr>
				<tr><th colspan="2">Write API</th></tr>
				<tr><td>Request Form</td><td><label><input name="enable_request_form" type="checkbox" value="True"<%If DdysOpen_Bool(DdysOpen_Config("enable_request_form")) Then Response.Write " checked"%>> enabled</label></td></tr>
				<tr><td>DDYS API Key</td><td><input name="api_key" class="ddys-asp-wide" type="password" value="<%=DdysOpen_Attr(DdysOpen_Config("api_key"))%>"><p class="note">Stored in Z-BlogASP config. It is used only by server-side request submission.</p></td></tr>
				<tr><td>Request Interval</td><td><input name="request_interval" type="number" min="10" max="3600" value="<%=DdysOpen_Attr(DdysOpen_Config("request_interval"))%>"> seconds/IP</td></tr>
			</table>
			<p><input type="submit" class="button" value="Save Settings"></p>
		</form>
	<%ElseIf tab = "shortcodes" Then%>
		<div class="ddys-asp-admin-grid">
			<section class="ddys-asp-panel">
				<h3>Generator</h3>
				<label>Kind <select id="ddys-asp-shortcode-kind"><option value="ddys_latest">latest</option><option value="ddys_hot">hot</option><option value="ddys_search">search</option><option value="ddys_calendar">calendar</option><option value="ddys_movie">movie</option><option value="ddys_sources">sources</option><option value="ddys_collections">collections</option><option value="ddys_request_form">request form</option></select></label>
				<label>slug <input id="ddys-asp-shortcode-slug" type="text"></label>
				<label>limit <input id="ddys-asp-shortcode-limit" type="number" min="1" max="50" value="12"></label>
				<label>type <input id="ddys-asp-shortcode-type" type="text" placeholder="movie"></label>
				<p><button type="button" class="button" id="ddys-asp-shortcode-build">Build</button></p>
			</section>
			<section class="ddys-asp-panel">
				<h3>Shortcode</h3>
				<textarea id="ddys-asp-shortcode-output" rows="8" readonly>[ddys_latest limit="12"]</textarea>
				<p><button type="button" class="button" id="ddys-asp-shortcode-copy">Copy</button></p>
				<pre>[ddys_latest limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_calendar]
[ddys_movie slug="this-tempting-madness"]
[ddys_sources slug="this-tempting-madness"]
[ddys_request_form]</pre>
			</section>
		</div>
	<%ElseIf tab = "pages" Then%>
		<table class="tableFull tableBorder">
			<tr><th>Page</th><th>URL</th></tr>
			<tr><td>Latest</td><td><a href="<%=pageBase%>?view=latest" target="_blank"><%=pageBase%>?view=latest</a></td></tr>
			<tr><td>Hot</td><td><a href="<%=pageBase%>?view=hot" target="_blank"><%=pageBase%>?view=hot</a></td></tr>
			<tr><td>Search</td><td><a href="<%=pageBase%>?view=search" target="_blank"><%=pageBase%>?view=search</a></td></tr>
			<tr><td>Calendar</td><td><a href="<%=pageBase%>?view=calendar" target="_blank"><%=pageBase%>?view=calendar</a></td></tr>
			<tr><td>Collections</td><td><a href="<%=pageBase%>?view=collections" target="_blank"><%=pageBase%>?view=collections</a></td></tr>
		</table>
	<%ElseIf tab = "cache" Then%>
		<p>Cache files: <strong><%=DdysOpen_CacheCount()%></strong></p>
		<form method="post" action="savesetting.asp">
			<input type="hidden" name="action" value="clear_cache">
			<p><input type="submit" class="button" value="Clear DDYS Cache" onclick="return confirm('Clear DDYS cache?');"></p>
		</form>
	<%End If%>
	</div>
</div>
<!--#include file="..\..\..\zb_system\admin\admin_footer.asp"-->
