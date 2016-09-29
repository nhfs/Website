<cfif not IsDefined("form.scatName")>
	<cflocation url="addscat.cfm">
</cfif>

<cfquery name="addScat" datasource="#dsn#">
	Insert Into subcats
		(CatID, scatName, scatDesc, scatOrder)
	VALUES
		('#Form.CatID#','#form.scatName#', '#form.scatDesc#'
		<cfif Form.scatOrder GT 0>
			, #Form.scatOrder#
		<cfelse>
			, 0
		</cfif>
		)
</cfquery>

<cflocation url="addscat.cfm?x=#form.scatName#&CatID=#Form.CatID#">