<!-- #include file="function.asp" -->
<%
Call RegisterPlugin("DdysOpen","ActivePlugin_DdysOpen")

Function ActivePlugin_DdysOpen()
	Call DdysOpen_InitConfig()
	Call Add_Response_Plugin("Response_Plugin_Admin_Left",MakeLeftMenu(1,"DDYS",GetCurrentHost() & "zb_users/plugin/DdysOpen/main.asp","nav_DdysOpen","aDdysOpen",GetCurrentHost() & "zb_users/plugin/DdysOpen/assets/images/icon-32.png"))
	Call Add_Response_Plugin("Response_Plugin_SettingMng_SubMenu",MakeSubMenu("DDYS","zb_users/plugin/DdysOpen/main.asp","m-left",False))
	Call Add_Response_Plugin("Response_Plugin_Admin_Header","<link rel=""stylesheet"" type=""text/css"" href=""" & GetCurrentHost() & "zb_users/plugin/DdysOpen/assets/css/admin.css?v=" & DDYSOPEN_VERSION & """ />")
	Call Add_Filter_Plugin("Filter_Plugin_TArticle_Export_TemplateTags","DdysOpen_TemplateTags")
End Function

Function InstallPlugin_DdysOpen()
	Call DdysOpen_InitConfig()
	Call DdysOpen_EnsureCacheDir()
End Function

Function UnInstallPlugin_DdysOpen()
	Call DdysOpen_ClearCache()
End Function
%>
