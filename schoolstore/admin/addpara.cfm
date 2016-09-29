<cfinclude template="topper.cfm">

<cfquery datasource="#dsn#" name="GetData">
	Select * from mainpage_data
</cfquery>
<H1>Update Launch Page Text and Photos</H1>

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
<cfform action="para_update.cfm" name="AddData" id="AddData" method="POST" enctype="multipart/form-data" scriptSrc="http://kite.boreal.org/CFIDE/scripts">

<table width="100%">
<TR>
<TD valign="top">Text:</TD>
<TD>
<cfset conf=structNew()>
<cfset conf.CustomConfigurationsPath="/schoolstore/admin/editorconf.js">
<cfif GetData.RecordCount GT 0><cfset val=GetData.mp_text>
<cfelse><cfset val="We have items available in the several categories. Please select one you're interested in from the menu above ">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="mp_text"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>

<TR>
<TD valign="top">
1st Photo:
</TD>
<TD>
<input type="File" value="Get Picture" name="mp_photo1">
<!---cfinput type="text" name="Pic"--->
<cfif IsDefined("Getdata.mp_photo1") and GetData.mp_photo1 GT "">
	</td></TR>
	<TR>
	<td>Current Picture:</td>
	<td ALIGN="CENTER"><IMG src="../#GetData.mp_photo1#"><BR>
	<input type="checkbox" value="Yes" name="Delpic1"> Delete Picture</td>
	</TR>
</cfif>

</TD>
</TR>
<TR>
<TD valign="top">
2nd Photo:
</TD>
<TD>
<input type="File" value="Get Picture" name="mp_photo2">
<!---cfinput type="text" name="Pic"--->
<cfif IsDefined("Getdata.mp_photo2") and GetData.mp_photo2 GT "">
	</td></TR>
	<TR>
	<td>Current Picture:</td>
	<td ALIGN="CENTER"><IMG src="../#Getdata.mp_photo2#"><BR>
	<input type="checkbox" value="Yes" name="Delpic2"> Delete Picture</td>
	</TR>
</cfif>

</TD>
</TR>
<tr>
<td colspan="2"><i>Note: Photos will be displayed down the left side of the page, with the text and categories
to the right of the photos.</i></td></tr>

<TR>
<TD COLSPAN=2 ALIGN=Center>
<input type="submit" value="Update Information">&nbsp;&nbsp;<input type="button" value="Reset Information" onClick="location.href='para_delete.cfm';">
</TD>
</TR>
</TABLE>


</cfform>
</cfoutput>

<cfinclude template="bottom.cfm">