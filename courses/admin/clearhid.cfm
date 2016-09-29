<cfquery name="clearnew" datasource="#dsn#">
	update courses set hidden=false
</cfquery>
<cflocation url="courses.cfm?c=c" addtoken="No">