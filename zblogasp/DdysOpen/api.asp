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
Call DdysOpen_InitConfig()
Response.ContentType = "application/json"
Response.Write DdysOpen_ProxyResponse()
%>
