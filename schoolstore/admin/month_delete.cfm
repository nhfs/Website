<cfif not IsDefined("url.pid")>
	<cflocation url="addmonth.cfm">
</cfif>

<cfquery name="GetData" datasource="#dsn#">
	select photo from monthly_data where pid=#url.pid#
</cfquery>

<cfquery name="DelData" datasource="#dsn#">
	delete from monthly_data where pid=#url.pid#
</cfquery>

<!--- delete photos --->

<cfif GetData.RecordCount GT 0 and GetData.photo GT "" and left(getData.photo, 7) eq "images/">
	<cfset fn="#fileloc#" & Replace(GetData.photo, "/","\","All")>
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
		<cffile action="Delete"
			file=#fn#>
	</cflock>
</cfif>

<cflocation url="addmonth.cfm?z=z&pid=#url.pid#">
