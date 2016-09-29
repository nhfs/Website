<div class="cart">
<A HREF="viewcart.cfm">
<B>You have
<cfoutput>
<cfif #ArrayIsEmpty(session.Cart)#>
no
<cfelse>
#ArrayLen(session.Cart)#
</cfif>
</cfoutput>
<cfif ArrayLen(session.Cart) EQ 1>
item
<cfelse>
items
</cfif>
<br/>in your cart<br/>
<img Src="images/cart.gif" alt="View cart" border=0><BR>
View cart</B>
</a>
</div>