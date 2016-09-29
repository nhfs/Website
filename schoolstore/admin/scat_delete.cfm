<cfif not IsDefined("URL.scatID")>
	<cflocation url="addscat.cfm">
</cfif>

<cfquery name="Delscat" datasource="#dsn#">
	Delete From subcats
	WHERE scatID = #URL.scatID#
</cfquery>

<cflocation url="addscat.cfm?z=Z">