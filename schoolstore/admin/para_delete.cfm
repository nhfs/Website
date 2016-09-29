<cfinclude template="topper.cfm">
<cfif not IsDefined("form.confirm")>
	<form action="para_delete.cfm" method="POST">
		<b>Warning!</b>  This will delete the photos for your launch page and reset the text to "We have items available in the following categories. Please select one you're interested in: ".
		Are you sure you want to proceed?
		<input type="hidden" name="confirm" value="yes"><br>
		<input type="submit" value="Go Ahead and Reset the Launch Page">&nbsp;&nbsp;
		<input type="button" value="No, I've Changed My Mind! ABORT!" onclick="location.href='addpara.cfm'">

	</form>
	<cfabort>
</cfif>
<cfquery name="GetData" datasource="#dsn#">
	select mp_photo1, mp_photo2 from mainpage_data
</cfquery>

<cfquery name="DelData" datasource="#dsn#">
	delete from mainpage_data
</cfquery>

<!--- delete photos --->

<cfif GetData.RecordCount GT 0 and GetData.mp_photo1 GT "">
	<cfset fn="#fileloc#" & Replace(GetData.mp_photo1, "/","\","All")>
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
		<cffile action="Delete"
			file=#fn#>
	</cflock>
</cfif>

<cfif GetData.RecordCount GT 0 and GetData.mp_photo2 GT "">
	<cfset fn="#fileloc#" & Replace(GetData.mp_photo2, "/","\","All")>
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
	<cffile action="Delete"
			file=#fn#>
	</cflock>
</cfif>

<cflocation url="addpara.cfm?z=z">
