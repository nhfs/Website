<cfinclude template="topper.cfm">

<cfquery datasource="#dsn#" name="GetData">
	Select * from shopping_data
</cfquery>
<H1>Update "How Shopping Works" Page Text</H1>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Data has been updated!</B>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.z")>
	<cfoutput>
		<B>Data has been deleted!</B>
	</cfoutput>
</cfif>

<cfoutput>
<cfform action="opara_update.cfm" name="AddData" id="AddData" method="POST" enctype="multipart/form-data" scriptSrc="http://kite.boreal.org/CFIDE/scripts">

<table width="100%">
<TR>
<TD valign="top">Ordering Info:</TD>
<TD>
<cfset conf=structNew()>
<cfset conf.CustomConfigurationsPath="/schoolstore/admin/editorconf.js">
<cfquery name="GetPara" dbtype="query">
	select sh_text from GetData
		where pid='#ordering_code#'
</cfquery>
<cfif GetData.RecordCount GT 0><cfset val=GetPara.sh_text>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="sh_text#ordering_code#"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>

<TR>
<TD valign="top">Guarantee Info:</TD>
<TD>
<cfquery name="GetPara" dbtype="query">
	select sh_text from GetData
		where pid='#guarantee_code#'
</cfquery>
<cfif GetData.RecordCount GT 0><cfset val=GetPara.sh_text>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="sh_text#guarantee_code#"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>
<TR>
<TD valign="top">Privacy Info:</TD>
<TD>
<cfquery name="GetPara" dbtype="query">
	select sh_text from GetData
		where pid='#privacy_code#'
</cfquery>
<cfif GetData.RecordCount GT 0><cfset val=GetPara.sh_text>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="sh_text#privacy_code#"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>


<TR>
<TD valign="top">Security Info:</TD>
<TD>
<cfquery name="GetPara" dbtype="query">
	select sh_text from GetData
		where pid='#security_code#'
</cfquery>
<cfif GetData.RecordCount GT 0><cfset val=GetPara.sh_text>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="sh_text#security_code#"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>

<TR>
<TD valign="top">Return / Refund Info:</TD>
<TD>
<cfquery name="GetPara" dbtype="query">
	select sh_text from GetData
		where pid='#return_code#'
</cfquery>
<cfif GetData.RecordCount GT 0><cfset val=GetPara.sh_text>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="sh_text#return_code#"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>

<TR>
<TD valign="top">Delivery Info:</TD>
<TD>
<cfquery name="GetPara" dbtype="query">
	select sh_text from GetData
		where pid='#delivery_code#'
</cfquery>
<cfif GetData.RecordCount GT 0><cfset val=GetPara.sh_text>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="sh_text#delivery_code#"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>

<TR>
<TD valign="top">Shipping Info:</TD>
<TD>
<cfquery name="GetPara" dbtype="query">
	select sh_text from GetData
		where pid='#shipping_code#'
</cfquery>
<cfif GetData.RecordCount GT 0><cfset val=GetPara.sh_text>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="sh_text#shipping_code#"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>
<TR>
<TD valign="top">Order a Gift Info:</TD>
<TD>
<cfquery name="GetPara" dbtype="query">
	select sh_text from GetData
		where pid='#gift_code#'
</cfquery>
<cfif GetData.RecordCount GT 0><cfset val=GetPara.sh_text>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="sh_text#gift_code#"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>

<TR>
<TD COLSPAN=2 ALIGN=Center>
<input type="submit" value="Update Information">
</TD>
</TR>
</TABLE>


</cfform>
</cfoutput>

<cfinclude template="bottom.cfm">