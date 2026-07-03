<%
Const DDYSOPEN_ID = "DdysOpen"
Const DDYSOPEN_VERSION = "1.0.0"
Const DDYSOPEN_API_DEFAULT = "https://ddys.io/api/v1"
Const DDYSOPEN_SITE_DEFAULT = "https://ddys.io"

Function DdysOpen_DefaultValue(key)
	Select Case key
		Case "api_base_url": DdysOpen_DefaultValue = DDYSOPEN_API_DEFAULT
		Case "site_base_url": DdysOpen_DefaultValue = DDYSOPEN_SITE_DEFAULT
		Case "timeout": DdysOpen_DefaultValue = "12"
		Case "cache_ttl": DdysOpen_DefaultValue = "600"
		Case "fresh_cache_ttl": DdysOpen_DefaultValue = "300"
		Case "detail_cache_ttl": DdysOpen_DefaultValue = "1800"
		Case "theme": DdysOpen_DefaultValue = "auto"
		Case "layout": DdysOpen_DefaultValue = "grid"
		Case "columns": DdysOpen_DefaultValue = "4"
		Case "target": DdysOpen_DefaultValue = "_blank"
		Case "show_source_link": DdysOpen_DefaultValue = "True"
		Case "enable_styles": DdysOpen_DefaultValue = "True"
		Case "enable_request_form": DdysOpen_DefaultValue = "False"
		Case "api_key": DdysOpen_DefaultValue = ""
		Case "request_interval": DdysOpen_DefaultValue = "60"
		Case Else: DdysOpen_DefaultValue = ""
	End Select
End Function

Sub DdysOpen_InitConfig()
	Dim c, keys, i, changed, key
	keys = Array("api_base_url","site_base_url","timeout","cache_ttl","fresh_cache_ttl","detail_cache_ttl","theme","layout","columns","target","show_source_link","enable_styles","enable_request_form","api_key","request_interval")
	Set c = New TConfig
	c.Load DDYSOPEN_ID
	changed = False
	For i = 0 To UBound(keys)
		key = keys(i)
		If c.Exists(key) = False Then
			c.Write key, DdysOpen_DefaultValue(key)
			changed = True
		End If
	Next
	If changed Then c.Save
	Set c = Nothing
	Call DdysOpen_EnsureCacheDir()
End Sub

Function DdysOpen_Config(key)
	Dim c, value
	Set c = New TConfig
	c.Load DDYSOPEN_ID
	If c.Exists(key) Then
		value = c.Read(key)
	Else
		value = DdysOpen_DefaultValue(key)
	End If
	Set c = Nothing
	DdysOpen_Config = value
End Function

Sub DdysOpen_WriteConfig(key, value)
	Dim c
	Set c = New TConfig
	c.Load DDYSOPEN_ID
	c.Write key, value
	c.Save
	Set c = Nothing
End Sub

Function DdysOpen_PluginUrl()
	On Error Resume Next
	DdysOpen_PluginUrl = GetCurrentHost() & "zb_users/plugin/DdysOpen/"
	If Err.Number <> 0 Or DdysOpen_PluginUrl = "" Then
		Err.Clear
		DdysOpen_PluginUrl = BlogHost & "zb_users/plugin/DdysOpen/"
	End If
End Function

Function DdysOpen_NormalizeUrl(value, fallback)
	value = Trim(CStr(value))
	If value = "" Then
		DdysOpen_NormalizeUrl = fallback
	ElseIf LCase(Left(value, 7)) = "http://" Or LCase(Left(value, 8)) = "https://" Then
		Do While Right(value, 1) = "/"
			value = Left(value, Len(value) - 1)
		Loop
		DdysOpen_NormalizeUrl = value
	Else
		DdysOpen_NormalizeUrl = fallback
	End If
End Function

Function DdysOpen_Bool(value)
	value = LCase(Trim(CStr(value)))
	DdysOpen_Bool = (value = "true" Or value = "1" Or value = "yes" Or value = "on")
End Function

Function DdysOpen_IntRange(value, fallback, min, max)
	If IsNumeric(value) Then
		value = CLng(value)
		If value < min Then value = fallback
		If value > max Then value = max
		DdysOpen_IntRange = value
	Else
		DdysOpen_IntRange = fallback
	End If
End Function

Function DdysOpen_Html(value)
	DdysOpen_Html = TransferHTML(CStr(value), "[html-format]")
End Function

Function DdysOpen_Attr(value)
	DdysOpen_Attr = DdysOpen_Html(value)
End Function

Function DdysOpen_JsonString(value)
	value = CStr(value)
	value = Replace(value, "\", "\\")
	value = Replace(value, """", "\""")
	value = Replace(value, vbTab, "\t")
	value = Replace(value, vbCrLf, "\n")
	value = Replace(value, vbCr, "\n")
	value = Replace(value, vbLf, "\n")
	DdysOpen_JsonString = """" & value & """"
End Function

Function DdysOpen_CacheDir()
	DdysOpen_CacheDir = BlogPath & "zb_users\PLUGIN\DdysOpen\cache\"
End Function

Sub DdysOpen_EnsureCacheDir()
	On Error Resume Next
	Dim fso
	Set fso = Server.CreateObject("Scripting.FileSystemObject")
	If Not fso.FolderExists(DdysOpen_CacheDir()) Then fso.CreateFolder DdysOpen_CacheDir()
	Set fso = Nothing
	Err.Clear
End Sub

Sub DdysOpen_ClearCache()
	On Error Resume Next
	Dim fso, folder, file
	Set fso = Server.CreateObject("Scripting.FileSystemObject")
	If fso.FolderExists(DdysOpen_CacheDir()) Then
		Set folder = fso.GetFolder(DdysOpen_CacheDir())
		For Each file In folder.Files
			If LCase(Right(file.Name, 5)) = ".json" Or LCase(Right(file.Name, 4)) = ".txt" Then file.Delete True
		Next
	End If
	Set folder = Nothing
	Set fso = Nothing
	Err.Clear
End Sub

Function DdysOpen_CacheCount()
	On Error Resume Next
	Dim fso, folder, file, n
	n = 0
	Set fso = Server.CreateObject("Scripting.FileSystemObject")
	If fso.FolderExists(DdysOpen_CacheDir()) Then
		Set folder = fso.GetFolder(DdysOpen_CacheDir())
		For Each file In folder.Files
			n = n + 1
		Next
	End If
	Set folder = Nothing
	Set fso = Nothing
	DdysOpen_CacheCount = n
	Err.Clear
End Function

Function DdysOpen_SafeFileName(value)
	Dim re
	Set re = New RegExp
	re.Global = True
	re.Pattern = "[^A-Za-z0-9_.-]"
	value = re.Replace(CStr(value), "_")
	If Len(value) > 160 Then value = Left(value, 160)
	DdysOpen_SafeFileName = value
	Set re = Nothing
End Function

Function DdysOpen_CacheRead(key, ttl)
	On Error Resume Next
	Dim fso, file, path, modified
	DdysOpen_CacheRead = ""
	If ttl <= 0 Then Exit Function
	path = DdysOpen_CacheDir() & DdysOpen_SafeFileName(key) & ".json"
	Set fso = Server.CreateObject("Scripting.FileSystemObject")
	If fso.FileExists(path) Then
		Set file = fso.GetFile(path)
		modified = file.DateLastModified
		If DateDiff("s", modified, Now()) <= ttl Then
			DdysOpen_CacheRead = DdysOpen_ReadTextFile(path)
		End If
	End If
	Set file = Nothing
	Set fso = Nothing
	Err.Clear
End Function

Sub DdysOpen_CacheWrite(key, content)
	On Error Resume Next
	Call DdysOpen_WriteTextFile(DdysOpen_CacheDir() & DdysOpen_SafeFileName(key) & ".json", content)
	Err.Clear
End Sub

Function DdysOpen_ReadTextFile(path)
	Dim stream
	Set stream = Server.CreateObject("ADODB.Stream")
	stream.Type = 2
	stream.Charset = "utf-8"
	stream.Open
	stream.LoadFromFile path
	DdysOpen_ReadTextFile = stream.ReadText
	stream.Close
	Set stream = Nothing
End Function

Sub DdysOpen_WriteTextFile(path, content)
	Dim stream
	Set stream = Server.CreateObject("ADODB.Stream")
	stream.Type = 2
	stream.Charset = "utf-8"
	stream.Open
	stream.WriteText content
	stream.SaveToFile path, 2
	stream.Close
	Set stream = Nothing
End Sub

Function DdysOpen_HttpRequest(method, url, body, headers)
	On Error Resume Next
	Dim http, timeoutMs, i
	timeoutMs = DdysOpen_IntRange(DdysOpen_Config("timeout"), 12, 1, 30) * 1000
	Set http = Server.CreateObject("MSXML2.ServerXMLHTTP.6.0")
	If Err.Number <> 0 Then
		Err.Clear
		Set http = Server.CreateObject("MSXML2.ServerXMLHTTP")
	End If
	If Err.Number <> 0 Then
		Err.Clear
		Set http = Server.CreateObject("Microsoft.XMLHTTP")
	End If
	If Err.Number <> 0 Then
		DdysOpen_HttpRequest = "{""success"":false,""message"":""HTTP component unavailable""}"
		Err.Clear
		Exit Function
	End If
	http.setTimeouts timeoutMs, timeoutMs, timeoutMs, timeoutMs
	If Err.Number <> 0 Then Err.Clear
	http.open method, url, False
	http.setRequestHeader "Accept", "application/json"
	http.setRequestHeader "User-Agent", "ddys-zblogasp-plugin/" & DDYSOPEN_VERSION
	If IsArray(headers) Then
		For i = 0 To UBound(headers) Step 2
			http.setRequestHeader headers(i), headers(i + 1)
		Next
	End If
	If method = "POST" Then
		http.setRequestHeader "Content-Type", "application/json"
		http.send body
	Else
		http.send
	End If
	If Err.Number <> 0 Then
		DdysOpen_HttpRequest = "{""success"":false,""message"":""DDYS request failed""}"
		Err.Clear
	Else
		DdysOpen_HttpRequest = http.responseText
	End If
	Set http = Nothing
End Function

Function DdysOpen_QueryValue(name)
	DdysOpen_QueryValue = Trim(CStr(Request.QueryString(name)))
End Function

Function DdysOpen_FormValue(name)
	DdysOpen_FormValue = Trim(CStr(Request.Form(name)))
End Function

Function DdysOpen_AllowedRoute(route)
	Select Case LCase(route)
		Case "movies","latest","hot","search","suggest","calendar","movie","sources","related","comments","collections","collection","shares","share","requests","activities","user","types","genres","regions"
			DdysOpen_AllowedRoute = True
		Case Else
			DdysOpen_AllowedRoute = False
	End Select
End Function

Function DdysOpen_ApiPath(route)
	Dim slug, id, username
	route = LCase(route)
	slug = DdysOpen_QueryValue("slug")
	id = DdysOpen_QueryValue("id")
	username = DdysOpen_QueryValue("username")
	Select Case route
		Case "movies": DdysOpen_ApiPath = "/movies"
		Case "latest": DdysOpen_ApiPath = "/latest"
		Case "hot": DdysOpen_ApiPath = "/hot"
		Case "search": DdysOpen_ApiPath = "/search"
		Case "suggest": DdysOpen_ApiPath = "/suggest"
		Case "calendar": DdysOpen_ApiPath = "/calendar"
		Case "movie"
			If slug = "" Then DdysOpen_ApiPath = "" Else DdysOpen_ApiPath = "/movies/" & Server.URLEncode(slug)
		Case "sources"
			If slug = "" Then DdysOpen_ApiPath = "" Else DdysOpen_ApiPath = "/movies/" & Server.URLEncode(slug) & "/sources"
		Case "related"
			If slug = "" Then DdysOpen_ApiPath = "" Else DdysOpen_ApiPath = "/movies/" & Server.URLEncode(slug) & "/related"
		Case "comments"
			If slug = "" Then DdysOpen_ApiPath = "" Else DdysOpen_ApiPath = "/movies/" & Server.URLEncode(slug) & "/comments"
		Case "collections": DdysOpen_ApiPath = "/collections"
		Case "collection"
			If slug = "" Then DdysOpen_ApiPath = "" Else DdysOpen_ApiPath = "/collections/" & Server.URLEncode(slug)
		Case "shares": DdysOpen_ApiPath = "/shares"
		Case "share"
			If id = "" Then DdysOpen_ApiPath = "" Else DdysOpen_ApiPath = "/shares/" & Server.URLEncode(id)
		Case "requests": DdysOpen_ApiPath = "/requests"
		Case "activities": DdysOpen_ApiPath = "/activities"
		Case "user"
			If username = "" Then DdysOpen_ApiPath = "" Else DdysOpen_ApiPath = "/user/" & Server.URLEncode(username)
		Case "types": DdysOpen_ApiPath = "/types"
		Case "genres": DdysOpen_ApiPath = "/genres"
		Case "regions": DdysOpen_ApiPath = "/regions"
		Case Else: DdysOpen_ApiPath = ""
	End Select
End Function

Function DdysOpen_ProxyQueryString()
	Dim keys, i, key, value, output
	keys = Array("type","genre","region","year","sort","page","per_page","limit","q","month")
	output = ""
	For i = 0 To UBound(keys)
		key = keys(i)
		value = DdysOpen_QueryValue(key)
		If value <> "" Then
			If output <> "" Then output = output & "&"
			output = output & Server.URLEncode(key) & "=" & Server.URLEncode(value)
		End If
	Next
	DdysOpen_ProxyQueryString = output
End Function

Function DdysOpen_ProxyResponse()
	Dim route, path, query, url, ttl, cacheKey, cached, response
	route = LCase(DdysOpen_QueryValue("route"))
	If DdysOpen_AllowedRoute(route) = False Then
		DdysOpen_ProxyResponse = "{""success"":false,""message"":""Route not allowed""}"
		Exit Function
	End If
	path = DdysOpen_ApiPath(route)
	If path = "" Then
		DdysOpen_ProxyResponse = "{""success"":false,""message"":""Invalid route parameters""}"
		Exit Function
	End If
	query = DdysOpen_ProxyQueryString()
	url = DdysOpen_NormalizeUrl(DdysOpen_Config("api_base_url"), DDYSOPEN_API_DEFAULT) & path
	If query <> "" Then url = url & "?" & query
	ttl = DdysOpen_IntRange(DdysOpen_Config("cache_ttl"), 600, 0, 604800)
	If route = "latest" Or route = "hot" Then ttl = DdysOpen_IntRange(DdysOpen_Config("fresh_cache_ttl"), 300, 0, 604800)
	If route = "movie" Or route = "sources" Or route = "related" Or route = "collection" Or route = "share" Then ttl = DdysOpen_IntRange(DdysOpen_Config("detail_cache_ttl"), 1800, 0, 604800)
	cacheKey = route & "_" & path & "_" & query
	cached = DdysOpen_CacheRead(cacheKey, ttl)
	If cached <> "" Then
		DdysOpen_ProxyResponse = cached
		Exit Function
	End If
	response = DdysOpen_HttpRequest("GET", url, "", Empty)
	If response <> "" And InStr(response, """success"":false") = 0 Then
		Call DdysOpen_CacheWrite(cacheKey, response)
	End If
	DdysOpen_ProxyResponse = response
End Function

Function DdysOpen_CheckRateLimit(scope, key, interval)
	On Error Resume Next
	Dim cacheKey, path, lastText, lastTime, nowTime
	DdysOpen_CheckRateLimit = True
	If interval <= 0 Then Exit Function
	cacheKey = "rate_" & scope & "_" & key
	path = DdysOpen_CacheDir() & DdysOpen_SafeFileName(cacheKey) & ".txt"
	lastTime = 0
	lastText = ""
	nowTime = DateDiff("s", #1/1/1970#, Now())
	Dim fso
	Set fso = Server.CreateObject("Scripting.FileSystemObject")
	If fso.FileExists(path) Then lastText = DdysOpen_ReadTextFile(path)
	Set fso = Nothing
	If IsNumeric(lastText) Then lastTime = CLng(lastText)
	If lastTime > nowTime Then lastTime = 0
	If lastTime > 0 And nowTime - lastTime < interval Then
		DdysOpen_CheckRateLimit = False
		Exit Function
	End If
	Call DdysOpen_WriteTextFile(path, CStr(nowTime))
	Err.Clear
End Function

Function DdysOpen_RequestResponse()
	Dim apiKey, body, url, headers, ip
	If DdysOpen_Bool(DdysOpen_Config("enable_request_form")) = False Then
		DdysOpen_RequestResponse = "{""success"":false,""message"":""Request form is disabled""}"
		Exit Function
	End If
	apiKey = Trim(DdysOpen_Config("api_key"))
	If apiKey = "" Then
		DdysOpen_RequestResponse = "{""success"":false,""message"":""DDYS API Key is not configured""}"
		Exit Function
	End If
	ip = Request.ServerVariables("REMOTE_ADDR")
	If DdysOpen_CheckRateLimit("request", ip, DdysOpen_IntRange(DdysOpen_Config("request_interval"), 60, 10, 3600)) = False Then
		DdysOpen_RequestResponse = "{""success"":false,""message"":""Please wait before submitting again""}"
		Exit Function
	End If
	If DdysOpen_FormValue("title") = "" Then
		DdysOpen_RequestResponse = "{""success"":false,""message"":""Please enter a title""}"
		Exit Function
	End If
	body = "{""title"":" & DdysOpen_JsonString(DdysOpen_FormValue("title")) & _
		",""year"":" & DdysOpen_JsonString(DdysOpen_FormValue("year")) & _
		",""type"":" & DdysOpen_JsonString(DdysOpen_FormValue("type")) & _
		",""description"":" & DdysOpen_JsonString(DdysOpen_FormValue("description")) & _
		",""douban_id"":" & DdysOpen_JsonString(DdysOpen_FormValue("douban_id")) & "}"
	url = DdysOpen_NormalizeUrl(DdysOpen_Config("api_base_url"), DDYSOPEN_API_DEFAULT) & "/requests"
	headers = Array("Authorization","Bearer " & apiKey)
	DdysOpen_RequestResponse = DdysOpen_HttpRequest("POST", url, body, headers)
End Function

Sub DdysOpen_TemplateTags(ByRef aryTemplateTagsName, ByRef aryTemplateTagsValue)
	Dim i
	For i = 1 To UBound(aryTemplateTagsValue)
		If IsNull(aryTemplateTagsValue(i)) = False Then
			If InStr(CStr(aryTemplateTagsValue(i)), "[ddys_") > 0 Then
				aryTemplateTagsValue(i) = DdysOpen_ParseShortcodes(CStr(aryTemplateTagsValue(i)))
			End If
		End If
	Next
End Sub

Function DdysOpen_ParseShortcodes(content)
	Dim re, matches, match, output
	output = content
	Set re = New RegExp
	re.IgnoreCase = True
	re.Global = True
	re.Pattern = "\[(ddys_[a-z_]+)([^\]]*)\]"
	Set matches = re.Execute(content)
	For Each match In matches
		output = Replace(output, match.Value, DdysOpen_RenderShortcode(match.SubMatches(0), match.SubMatches(1)))
	Next
	Set matches = Nothing
	Set re = Nothing
	DdysOpen_ParseShortcodes = output
End Function

Function DdysOpen_Attribute(attrText, name, fallback)
	Dim re, matches
	DdysOpen_Attribute = fallback
	Set re = New RegExp
	re.IgnoreCase = True
	re.Global = False
	re.Pattern = "(^|\s)" & name & "\s*=\s*" & Chr(34) & "([^" & Chr(34) & "]*)" & Chr(34)
	Set matches = re.Execute(attrText)
	If matches.Count > 0 Then DdysOpen_Attribute = matches(0).SubMatches(1)
	Set matches = Nothing
	Set re = Nothing
End Function

Function DdysOpen_RenderShortcode(tag, attrText)
	Dim kind, html, apiUrl, requestUrl, value
	kind = LCase(Replace(tag, "ddys_", ""))
	apiUrl = DdysOpen_PluginUrl() & "api.asp"
	requestUrl = DdysOpen_PluginUrl() & "request.asp"
	If kind = "request_form" Then
		DdysOpen_RenderShortcode = DdysOpen_FrontendAssets() & "<form class=""ddys-asp ddys-asp-request-form"" method=""post"" action=""" & DdysOpen_Attr(requestUrl) & """ data-ddys-request-form>" & _
			"<label>Title<input type=""text"" name=""title"" maxlength=""255"" required></label>" & _
			"<label>Year<input type=""number"" name=""year"" min=""1900"" max=""2099""></label>" & _
			"<label>Type<select name=""type""><option value=""""></option><option value=""movie"">movie</option><option value=""series"">series</option><option value=""variety"">variety</option><option value=""anime"">anime</option></select></label>" & _
			"<label>Douban ID<input type=""text"" name=""douban_id"" maxlength=""30""></label>" & _
			"<label>Note<textarea name=""description"" maxlength=""1000""></textarea></label>" & _
			"<button type=""submit"">Submit</button><p class=""ddys-asp-status"" role=""status""></p></form>"
		Exit Function
	End If
	html = "<div class=""ddys-asp ddys-asp-theme-" & DdysOpen_Attr(DdysOpen_Config("theme")) & """ data-ddys-widget data-api=""" & DdysOpen_Attr(apiUrl) & """ data-kind=""" & DdysOpen_Attr(kind) & """"
	For Each value In Array("type","genre","region","year","sort","page","per_page","limit","q","month","slug","id","username")
		If DdysOpen_Attribute(attrText, CStr(value), "") <> "" Then
			html = html & " data-" & Replace(CStr(value), "_", "-") & "=""" & DdysOpen_Attr(DdysOpen_Attribute(attrText, CStr(value), "")) & """"
		End If
	Next
	html = html & " data-layout=""" & DdysOpen_Attr(DdysOpen_Attribute(attrText, "layout", DdysOpen_Config("layout"))) & """"
	html = html & " data-target=""" & DdysOpen_Attr(DdysOpen_Attribute(attrText, "target", DdysOpen_Config("target"))) & """"
	html = html & "><div class=""ddys-asp-loading"">Loading DDYS...</div></div>"
	DdysOpen_RenderShortcode = DdysOpen_FrontendAssets() & html
End Function

Dim DdysOpen_AssetsPrinted
DdysOpen_AssetsPrinted = False

Function DdysOpen_FrontendAssets()
	If DdysOpen_AssetsPrinted Or DdysOpen_Bool(DdysOpen_Config("enable_styles")) = False Then
		DdysOpen_FrontendAssets = ""
		Exit Function
	End If
	DdysOpen_AssetsPrinted = True
	DdysOpen_FrontendAssets = "<link rel=""stylesheet"" type=""text/css"" href=""" & DdysOpen_Attr(DdysOpen_PluginUrl() & "assets/css/frontend.css?v=" & DDYSOPEN_VERSION) & """ />" & _
		"<script defer src=""" & DdysOpen_Attr(DdysOpen_PluginUrl() & "assets/js/frontend.js?v=" & DDYSOPEN_VERSION) & """></script>"
End Function
%>
