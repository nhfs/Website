<cfquery name="getParts" datasource="#dsn#">
	select pid, porder, plevel  from partners order by porder
</cfquery>

<cfloop index="x" from="1" to="#arrayLen(levels)#">
	<cfquery name="getStarts" dbtype="query">
		select pid, porder from getParts where porder > 1 and plevel = #x# order by porder
	</cfquery>
	<cfset ord=1>
	<cfloop query="getstarts">
		<cfquery name="SaveOrd" datasource="#dsn#">
			update partners set porder=#ord# where pid=#pid#
		</cfquery>
		<cfset ord=ord+1>
	</cfloop>

	<cfquery name="GetFirst" dbtype="query">
		select pid, porder from getParts where porder <= 1 and plevel = #x# order by porder
	</cfquery>
	<cfloop query="getFirst">
		<cfquery name="SaveOrd" datasource="#dsn#">
			update partners set porder=#ord# where pid=#pid#
		</cfquery>
		<cfset ord=ord+1>
	</cfloop>
</cfloop>