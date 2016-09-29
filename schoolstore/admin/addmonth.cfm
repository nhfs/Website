<cfinclude template="topper.cfm">
<script type="text/javascript">
	function getData(node) {
		var iid=node.options[node.selectedIndex].value;
		if (iid > 0) {
			$.get(
				"getinst.cfm",
				{iid: iid},
				function(data) {
					info=data.match(/fname-(.*)-lname-(.*)-photo-([^\s]*)/);
					$("#fname").val(info[1]);
					$("#lname").val(info[2]);
					if ($("#currphoto").length > 0) {
						$("#delpic").attr("checked",true);
						$("#currphoto").hide();
					}
					if (info[3] > " ") {
						$("#instphoto").html('<img src="../../courses/courses/'+info[3]+'"><br/>If you do not specify a photo, this one from the instructor profile will be used.<input type="hidden" name="instphoto" value="'+info[3]+'">');
					} else {
						$("#instphoto").html('');
					}
				}
			);
		} else {
//			$("#fname").val("");
//			$("#lname").val("");
			$("#instphoto").html('');
			$("#currphoto").show();
			if ($("#currphoto").find("img:first").length > 0) {
				if ($("#currphoto").find("img:first").attr("src").substring(0,5) == "../..") {
					$("#currphoto").find("img:first").remove();
				}
			}
		}
	}
</script>
<cfif isDefined("url.pid") and url.pid gt 0>
	<cfquery datasource="#dsn#" name="GetData">
		Select * from monthly_data where
			pid=#url.pid#
	</cfquery>
</cfif>
<H1>Update Monthly Staff/Instructor</H1>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Information has been updated!</B>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.z")>
	<cfoutput>
		<B>Information has been deleted!</B>
	</cfoutput>
</cfif>

<cfoutput>
<cfform action="month_update.cfm" name="AddData" id="AddData" method="POST" enctype="multipart/form-data" scriptSrc="http://kite.boreal.org/CFIDE/scripts">

<table width="100%">
<tr>
<td valign="top">Type:</td>
<td>
<select name="pid" id="pid" onchange="location.href='addmonth.cfm?pid='+this.options[this.selectedIndex].value;">
<option value="0">--Please select--</option>
<option value="#staff_month#"<cfif isdefined("url.pid") and url.pid eq staff_month> selected</cfif>>Staff Pick of Month</option>
<option value="#inst_month#"<cfif isdefined("url.pid") and url.pid eq inst_month> selected</cfif>>Instructor of Month</option>
<option value="#tool_month#"<cfif isdefined("url.pid") and url.pid eq tool_month> selected</cfif>>Tools of the Season</option>
</select>
</td>
</tr>
<cfif isDefined("url.pid")>
<cfif url.pid neq tool_month>
<TR>
<td valign="top">Instructor Profile:</td>
<td>
<cfquery name="GetInst" datasource="#dsn#">
	select iid, concat(lname,',',fname) as name
	from instructors 
	order by lname
</cfquery>
<cfif isDefined("getData.iid")><cfset val=getdata.iid><cfelse><cfset val=""></cfif>
<cfselect name="iid" query="GetInst" display="name" value="iid" selected="#val#" queryPosition="below" onchange="getData(this)">
	<option value="0">--No profile--</option>
</cfselect>
</td>
</tr>
<tr>
<td valign="top">Name (first and last):</td>
<td>
<cfif isDefined("GetData.fname")>
	<cfset fname=GetData.fname>
	<cfset lname=Getdata.lname>
<cfelse>
	<cfset fname="">
	<cfset lname="">
</cfif>
<input type="text" name="fname" id="fname" size="20" value="#fname#">
<cfinput type="text" name="lname" id="lname" size="30" value="#lname#" required="Yes" message="Please enter the person's name"/>
</td>
</tr>
</cfif>
<cfquery name="getPhotos" datasource="#dsn#">
	select * from monthly_pics where pid=#url.pid# order by pord
</cfquery>
<cfset ctr=1>
<cfloop query="getPhotos">
<TR>
<TD valign="top">
Photo:
</TD>
<TD>
<input type="File" value="Get Picture" name="photo#ctr#">
<!---cfinput type="text" name="Pic"--->
	</td></TR>
	<TR id="currphoto">
	<td>Current Picture:</td>
	<td ALIGN="CENTER"><IMG src="../#photo#"><BR>
	<cfif left(photo, 7) eq "images/">
		<input type="checkbox" value="Yes" name="Delpic#ctr#" id="delpic"> Delete Photo <cfif url.pid neq tool_month>(photo from instructor profile, if any, will be used)</cfif>
	</cfif>
	</td></tr>
	<tr><td></td><td id="instphoto">
	
</TD>
</TR>
<tr><td>Caption:</td>
<td><input type="text" name="caption#ctr#" value="#caption#" size="20"><br/></td>
</tr>
<input type="hidden" name="picid#ctr#" value="#picid#">
<cfset ctr=ctr+1>
</cfloop>
<cfif url.pid eq tool_month><cfset pics=3><cfelse><cfset pics=1></cfif>
<cfloop index="x" from="#ctr#" to="#pics#">
<TR>
<TD valign="top">
Photo:
</TD>
<TD>
<input type="File" value="Get Picture" name="photo#x#">
<!---cfinput type="text" name="Pic"--->
	</td></tr>
	<tr><td></td><td id="instphoto">
	
</TD>
</TR>
<tr><td>Caption:</td>
<td><input type="text" name="caption#x#" size="20"><br/></td>
</tr>

</cfloop>
<tr>
<TD valign="top">Story:</TD>
<TD>
<cfset conf=structNew()>
<cfset conf.CustomConfigurationsPath="/schoolstore/admin/editorconf.js">
<cfif isDefined("GetData.story")><cfset val=GetData.story>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="story"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>
<cfif url.pid eq tool_month>
<tr>
<TD valign="top">Additional text:</TD>
<TD>
<cfset conf=structNew()>
<cfset conf.CustomConfigurationsPath="/schoolstore/admin/editorconf.js">
<cfif isDefined("GetData.extratext")><cfset val=GetData.extratext>
<cfelse><cfset val="">
</cfif>


<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="extratext"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</tr>
</cfif>
<tr>
<td valign="top">Display this box:</td>
<td><input type="checkbox" name="showbox"<cfif isDefined("getdata.showbox") and getData.showbox> checked</cfif>></td>
</tr>

<tr>

<TD COLSPAN=2 ALIGN=Center>
<input type="submit" value="Update Information"><cfif isDefined("url.pid")>
&nbsp;&nbsp;<input type="button" value="Remove Information" onClick="if (confirm('Are you sure you want to delete this information?')) { location.href='month_delete.cfm?pid=#url.pid#'; }">
</cfif>
</TD>
</TR>
</cfif>
</TABLE>


</cfform>
</cfoutput>

<cfinclude template="bottom.cfm">