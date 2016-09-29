<cfif not isdefined("Form.etitle") and not IsDefined("url.del")>
	<cflocation url="cdata.cfm">
</cfif>
<cfinclude template="header.cfm">

<cfif IsDefined("url.eid") and not IsDefined("form.eid")>
	<cfset form.eid=url.eid>
</cfif>

<cfif IsDefined("form.eid")>
	<!--- updating existing data --->
	<cfif IsDefined("url.del") and url.del eq "yes">
		<!--- delete data --->
		<cfquery name="DelData" datasource="#dsn#">
			delete from course_extra where 
				eid=#form.eid#
		</cfquery>
	<cfelse>
		<!--- update data --->
		<cfquery name="savedata" datasource="#dsn#">
			update course_extra set
				etitle='#form.etitle#',
				etext='#form.etext#'
			where eid=#form.eid#
		</cfquery>
	</cfif>

<cfelse>
	<!--- adding new data --->
	
	<cfquery datasource="#dsn#" name="adddata">
		Insert into course_extra
			(etitle, etext)
			values ('#form.etitle#', '#form.etext#')
	</cfquery>
</cfif>

<cflocation url="cdata.cfm?x=x" addtoken="no">
