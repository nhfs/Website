<cfif not IsDefined("url.bid")>
	<cflocation url="addpower.cfm">
</cfif>

<cfquery name="GetData" datasource="#dsn#">
	select photo from pbox where bid=#url.bid#
</cfquery>

<cfquery name="DelData" datasource="#dsn#">
	delete from pbox where bid=#url.bid#
</cfquery>

<!--- delete photos --->

<cfif GetData.RecordCount GT 0 and GetData.photo GT "">
	<cfset fn="#fileloc#" & Replace(GetData.photo, "/","\","All")>
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
		<cffile action="Delete"
			file=#fn#>
	</cflock>
</cfif>

<cflocation url="addpower.cfm?z=z&bid=#url.bid#">
