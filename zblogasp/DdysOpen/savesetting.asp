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

If Request.Form("action") = "clear_cache" Then
	Call DdysOpen_ClearCache()
	Response.Redirect "main.asp?tab=cache&cache=cleared"
	Response.End
End If

Call DdysOpen_WriteConfig("api_base_url", DdysOpen_NormalizeUrl(Request.Form("api_base_url"), DDYSOPEN_API_DEFAULT))
Call DdysOpen_WriteConfig("site_base_url", DdysOpen_NormalizeUrl(Request.Form("site_base_url"), DDYSOPEN_SITE_DEFAULT))
Call DdysOpen_WriteConfig("timeout", CStr(DdysOpen_IntRange(Request.Form("timeout"), 12, 1, 30)))
Call DdysOpen_WriteConfig("cache_ttl", CStr(DdysOpen_IntRange(Request.Form("cache_ttl"), 600, 0, 604800)))
Call DdysOpen_WriteConfig("fresh_cache_ttl", CStr(DdysOpen_IntRange(Request.Form("fresh_cache_ttl"), 300, 0, 604800)))
Call DdysOpen_WriteConfig("detail_cache_ttl", CStr(DdysOpen_IntRange(Request.Form("detail_cache_ttl"), 1800, 0, 604800)))
Call DdysOpen_WriteConfig("theme", LCase(Trim(Request.Form("theme"))))
Call DdysOpen_WriteConfig("layout", LCase(Trim(Request.Form("layout"))))
Call DdysOpen_WriteConfig("columns", CStr(DdysOpen_IntRange(Request.Form("columns"), 4, 1, 6)))
Call DdysOpen_WriteConfig("target", Trim(Request.Form("target")))
If Request.Form("enable_styles") <> "" Then
	Call DdysOpen_WriteConfig("enable_styles", "True")
Else
	Call DdysOpen_WriteConfig("enable_styles", "False")
End If
If Request.Form("show_source_link") <> "" Then
	Call DdysOpen_WriteConfig("show_source_link", "True")
Else
	Call DdysOpen_WriteConfig("show_source_link", "False")
End If
If Request.Form("enable_request_form") <> "" Then
	Call DdysOpen_WriteConfig("enable_request_form", "True")
Else
	Call DdysOpen_WriteConfig("enable_request_form", "False")
End If
Call DdysOpen_WriteConfig("api_key", Trim(Request.Form("api_key")))
Call DdysOpen_WriteConfig("request_interval", CStr(DdysOpen_IntRange(Request.Form("request_interval"), 60, 10, 3600)))

Response.Redirect "main.asp?saved=1"
%>
