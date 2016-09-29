<cfif not IsDefined("form.Students")>
	<cflocation url="viewcart.cfm">
</cfif>

<cfif form.students eq 0>
	<cfset ArrayDeleteAt(Session.Cart,form.CartItem)>
<cfelseif LSIsNumeric(form.students)>
	<cfif isDefined("form.kids") and form.kids GT "">
		<cfset session.cart[form.cartItem].children=form.kids>
		<cfset session.Cart[form.CartItem].students=form.Students>
	<cfelse>
		<cfset session.cart[form.cartItem].children=0>
		<cfset session.Cart[form.CartItem].students=form.Students>
	</cfif>
</cfif>

<cflocation url="register.cfm" addtoken="no">