<cfquery name="GetCourses" datasource="#dsn#">
	select cid, tid from course_themes 
		order by cid
</cfquery>

<cfset lastcid=0>
<cfloop query="GetCourses">
	<cfoutput>#cid#-#tid#</cfoutput>
	<cfif cid neq lastcid>
		<cfquery name="setRank" datasource="#dsn#">
			update course_themes set rank=1 where tid=#tid# and cid=#cid#
		</cfquery>
		<cfset lastcid=cid>
		1<br/>
	<cfelse>
		<cfquery name="setRank2" datasource="#dsn#">
			update course_themes set rank=2 where tid=#tid# and cid=#cid#
		</cfquery>	
		2<br/>
	</cfif>
</cfloop>