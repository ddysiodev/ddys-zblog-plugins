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
Dim view, shortcode, title
view = LCase(Trim(Request.QueryString("view")))
If view = "" Then view = "latest"
Select Case view
	Case "hot": title = "Hot Movies": shortcode = "[ddys_hot limit=""12""]"
	Case "search": title = "Search": shortcode = "[ddys_search]"
	Case "calendar": title = "Calendar": shortcode = "[ddys_calendar]"
	Case "collections": title = "Collections": shortcode = "[ddys_collections per_page=""12""]"
	Case "requests": title = "Requests": shortcode = "[ddys_requests per_page=""12""]"
	Case "movie": title = "Movie Detail": shortcode = "[ddys_movie slug=""" & DdysOpen_Attr(Request.QueryString("slug")) & """]"
	Case Else: title = "Latest Movies": shortcode = "[ddys_latest limit=""12""]"
End Select
%><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><%=DdysOpen_Html(title)%> - DDYS</title>
	<link rel="stylesheet" type="text/css" href="assets/css/frontend.css?v=<%=DDYSOPEN_VERSION%>">
	<script defer src="assets/js/frontend.js?v=<%=DDYSOPEN_VERSION%>"></script>
</head>
<body class="ddys-asp-page">
	<main class="ddys-asp-page-main">
		<header class="ddys-asp-page-header">
			<a href="<%=DdysOpen_Attr(BlogHost)%>"><%=DdysOpen_Html(ZC_BLOG_NAME)%></a>
			<h1><%=DdysOpen_Html(title)%></h1>
			<nav>
				<a href="?view=latest">Latest</a>
				<a href="?view=hot">Hot</a>
				<a href="?view=search">Search</a>
				<a href="?view=calendar">Calendar</a>
				<a href="?view=collections">Collections</a>
			</nav>
		</header>
		<%=DdysOpen_ParseShortcodes(shortcode)%>
	</main>
</body>
</html>
