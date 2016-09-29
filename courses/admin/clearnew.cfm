<cfquery name="clearnew" datasource="#dsn#">
	update course_dates set new=false
</cfquery>
<cflocation url="courses.cfm?c=c" addtoken="No">