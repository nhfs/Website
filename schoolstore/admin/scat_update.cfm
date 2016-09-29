<cfif not IsDefined("form.scatName") or not isdefined("URL.scatID")>
	<cflocation url="addscat.cfm">
</cfif>

<cfquery name="Updscat" datasource="#dsn#">
	Update subcats
		Set CatID='#Form.CatID#',		
		 	scatName='#Form.scatName#', 
			scatDesc='#Form.scatDesc#'
			<cfif Val(Form.scatOrder) GT 0>
				, scatOrder=#Form.scatOrder#
			<cfelse>
				, scatOrder=0
			</cfif>
		Where scatID =#URL.scatID#
</cfquery>

<cflocation url="addscat.cfm?y=#form.scatName#">