<cfinclude template="topper.cfm">
<cfif isDefined("url.bid") and url.bid gt 0>
	<cfquery datasource="#dsn#" name="GetData">
		Select * from pbox where
			bid=#url.bid#
	</cfquery>
</cfif>
<H1>Update Power Boxes</H1>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Box has been updated!</B>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.z")>
	<cfoutput>
		<B>Box has been deleted!</B>
	</cfoutput>
</cfif>

<cfoutput>
<cfform action="power_update.cfm" name="AddData" id="AddData" method="POST" enctype="multipart/form-data" scriptSrc="http://kite.boreal.org/CFIDE/scripts">

<table width="100%">
<tr>
<td valign="top">Box Number:</td>
<td>
<select name="bid" id="bid" onchange="location.href='addpower.cfm?bid='+this.options[this.selectedIndex].value;">
<option value="0">--Please select--</option>
<option value="1"<cfif isdefined("url.bid") and url.bid eq 1> selected</cfif>>Box 1</option>
<option value="2"<cfif isdefined("url.bid") and url.bid eq 2> selected</cfif>>Box 2</option>
</select>
</td>
</tr>
<cfif isDefined("url.bid")>
<tr>
<td valign="top">Title:</td>
<td>
<cfif isDefined("GetData.title")>
	<cfset val=getdata.title>
<cfelse>
	<cfset val="">
</cfif>
<cfinput type="text" name="title" id="title" size="60" value="#val#" required="Yes" message="Please enter a title for the box"/>
</td>
</tr>
<TR>
<TD valign="top">
Photo:
</TD>
<TD>
<input type="File" value="Get Picture" name="photo">
<!---cfinput type="text" name="Pic"--->
<cfif IsDefined("Getdata.photo") and GetData.photo GT "">
	</td></TR>
	<TR id="currphoto">
	<td>Current Picture:</td>
	<td ALIGN="CENTER"><IMG src="../#GetData.photo#"><BR>
	<input type="checkbox" value="Yes" name="Delpic" id="delpic"> Delete Photo
</cfif>
	
</TD>
</TR>
<tr>
<TD valign="top">Link:</TD>
<TD>
<cfif isDefined("getData.url")><cfset val=getdata.url><cfelse><cfset val=""></cfif>
<cfinput type="text" name="url" id="url" size="60" value="#val#" required="Yes" message="Please enter a link for the box"/>


</TD>
</tr><tr>

<TD COLSPAN=2 ALIGN=Center>
<input type="submit" value="Update Information"><cfif isDefined("url.bid")>
&nbsp;&nbsp;<input type="button" value="Remove Information" onClick="if (confirm('Are you sure you want to delete this power box?')) { location.href='power_delete.cfm?bid=#url.bid#'; }">
</cfif>
</TD>
</TR>
</cfif>
</TABLE>


</cfform>
</cfoutput>

<cfinclude template="bottom.cfm">