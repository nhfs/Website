<cfif not IsDefined("URL.LineID")>
	<cflocation url="addline.cfm">
</cfif>

<!--- first, delete associated pictures, if any --->
<cfquery datasource="#dsn#" name="GetPic">
	select LinePicture from _Lines
		Where LineID=#URL.LineID#
</cfquery>

<cfquery name="DelLine" datasource="#dsn#">
	Delete From _Lines
	WHERE LineID = #URL.LineID#
</cfquery>

<!--- Line is deleted, now delete associated pictures, if any --->

<cfif GetPic.LinePicture GT "">
	<cfset fn="#fileloc#" & Replace(GetPic.LinePicture, "/","\","All")>
	<cflock name="FileLock" type="Exclusive" timeout="30">
	<cffile action="Delete"
		file=#fn#>
	</cflock>
</cfif>

<cflocation url="addline.cfm?z=Z">