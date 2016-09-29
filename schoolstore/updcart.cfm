<cfif not IsDefined("form.Qty")>
	<cflocation url="viewcart.cfm">
</cfif>

<cfif #form.Qty# eq 0>
	<cfoutput>#ArrayDeleteAt(Session.Cart,form.CartItem)#</cfoutput>
<cfelseif LSIsNumeric(form.Qty)>
	<cfset session.Cart[form.CartItem].Qty=#form.Qty#>
</cfif>

<cflocation url="viewcart.cfm">