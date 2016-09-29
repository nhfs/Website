
<cfif #isdefined("URL.ID")#>
<!---- add item to cart array ----->
	<cfset dupflag = false>
	<cfloop index="idx" from=1 to=#ArrayLen(session.Cart)#>
		<cfif session.Cart[idx].ItemID eq URL.ID and 
			(not Isdefined("Form.ItemOptions") OR (isdefined("Form.ItemOptions") and session.Cart[idx].Opt eq Form.ItemOPtions)) and
			(not IsDefined("Form.ItemSizes") OR (isdefined("Form.ItemSizes") and session.Cart[idx].Size eq Form.ItemSizes))>
			<cfset session.Cart[idx].Qty = session.Cart[idx].Qty + Form.QtyOrdered>
			<script language="Javascript">
				alert("The item you ordered is already in your cart.  For your" + 
					" convenience, we've updated the quantity of the existing item, " +
					"rather than adding this item a second time.");
			</script>
			<cfset dupflag=true>
		</cfif>
	</cfloop>
	
	<cfif not dupflag>
		<cfquery name="getItem" datasource="#dsn#">
			Select CatID, GroupName, ItemName, ItemCode, Price, saleprice, taxable 
				from Items INNER JOIN 
					(Groups INNER JOIN 
					(_lines INNER JOIN subcats ON _lines.scatid=subcats.scatid)
					ON Groups.LineID=_lines.LineID)
					ON Groups.GroupID = Items.GroupID
				where ItemID=<cfqueryparam value="#URL.ID#" cfsqltype="CF_SQL_INTEGER">
		
		</cfquery>
	
		<cfoutput query="getItem">

			<cfset Item=structnew()>
			<cfset Item.ItemID=#URL.ID#>
			<cfset Item.CatID=#getItem.CatID#>
			<cfset Item.ItemName=#getItem.GroupName#>
			<cfif GetItem.ItemName GT "">
				<cfset Item.ItemName=Item.ItemName & "-" & GetItem.ItemName>
			</cfif>
			<cfif getitem.saleprice GT 0>
				<cfset item.price=getitem.saleprice>
				<cfset item.saved=getitem.price-getitem.saleprice>
			<cfelse>
				<cfset Item.Price=#getItem.Price#>
				<cfset item.saved=0>
			</cfif>
			<cfset Item.Qty=#Form.QtyOrdered#>
			<cfset Item.taxable=getItem.taxable>
			<cfif isdefined("Form.ItemOptions")>
				<cfset Item.Opt = #Form.ItemOptions#>
			<cfelse>
				<cfset Item.Opt = "">
			</cfif>
			<cfif isdefined("Form.ItemSizes")>
				<cfset Item.Size = #Form.ItemSizes#>
			<cfelse>
				<cfset Item.Size = "">
			</cfif>
			<cfset ArrayAppend(session.Cart,Item)>
		</cfoutput>
	</cfif>
</cfif>
<cfset masthead="kayaks">
<cfinclude Template="Templates/topper.cfm">
<BR>
<DIV ALIGN=Center><H2>
Shopping Cart Contents</H2></DIV>

<div class="data wide">
<TABLE WIDTH=100%>
<TR>
<TD width="40%" class="tier1">Item</TD>
<TD width="20%" align=right class="tier1">Price</TD>
<TD width="10%" align=right class="tier1">Qty</TD>
<TD width="20%" align=right class="tier1">Total</TD>
<TD width="10%" class="tier1"></TD>

</TR>

<cfset totqty=0>
<cfset totamt=0>
<cfset totsaved=0>

<cfif ArrayLen(session.Cart) GT 0>

	<cfloop index="cnt" from=1 to=#ArrayLen(session.Cart)#>
	<cfoutput>
	<form action="updcart.cfm" method=POST>
	<TR>
	<TD>
	<input type="hidden" value="#cnt#" name="CartItem">
	<A HREF="catalog.cfm?CatID=#session.Cart[cnt].CatID###i#session.Cart[cnt].ItemID#">
	#session.Cart[cnt].ItemName#
	<cfif session.Cart[cnt].Opt GT ""> (#session.Cart[cnt].Opt#</cfif>
	<cfif session.Cart[cnt].Size GT "">
		<cfif session.Cart[cnt].Opt GT "">
			, #session.Cart[cnt].Size#
		<cfelse>
			(#session.Cart[cnt].Size#
		</cfif>
	</cfif>
	<cfif session.Cart[cnt].Opt GT "" OR session.Cart[cnt].Size GT "">)</cfif></A>
	</TD>
	<TD align=right>
	<cfif session.cart[cnt].saved GT 0>
	<span class="struck">#dollarformat(session.cart[cnt].price + session.cart[cnt].saved)#</span>
	<span class="sale"></cfif>
	#DollarFormat(session.Cart[cnt].Price)#
	<cfif session.cart[cnt].saved GT 0></span></cfif>
	</TD>
	<TD align=right>
	<cfif NOT LSIsNumeric(session.Cart[cnt].Qty)>
		<cfset session.Cart[cnt].Qty = 1>
	</cfif>
	<input dir="rtl" type="text" size=3 name="Qty" value="#session.Cart[cnt].Qty#">
	<cfset totqty=totqty+#session.Cart[cnt].Qty#>
	</TD>
	<TD align=right>
	<cfset tot=#session.Cart[cnt].Qty# * #session.Cart[cnt].Price#>
	<cfset saved=session.cart[cnt].qty * session.cart[cnt].saved>
	#DollarFormat(tot)#
	<cfset totamt = totamt+#tot#>
	</TD>
	<TD>
	<input type="Submit" value="Update Qty"> </TD>
	</TR></form>
	<cfif cnt lt ArrayLen(session.Cart)>
		<tR>
		<TD COLSPAN=5 height=1 bgcolor="##000033"></TD>
		</tr>
	</cfif>
	</cfoutput>
	</cfloop>
<cfelse>
	<TD COLSPAN=5><B>Your cart is empty.</B></TD>
	<cfset session.shi=0>
</cfif>
<cfif totamt GT 0>
	<TR>
	<TD COLSPAN=2 class="tier1">
	<B>Subtotals</B>
	</TD>
	<cfoutput>
	<TD align=right class="tier1">
	<B>#totqty#</B>
	</TD>
	<td align=right class="tier1">
	<B>#DollarFormat(totamt)#</B>
	</td>
	<TD class="tier1"></TD>
	</TR>
<!--- special handling for gift certificates - if order is only a cert., just charge flat rate of $5.00 per 2 certs,
	otherwise, they'll slip cert in box so don't need to include its value in shipping calc --->
<cfset cert_tot=0>
<cfset cert_num=0>
<cfloop index="idx" from="1" to="#arraylen(session.cart)#">
	<cfif session.cart[idx].catid eq cert_cat>
		<cfset cert_tot=cert_tot+(session.cart[idx].price*session.cart[idx].qty)>
		<cfset cert_num=cert_num+session.cart[idx].qty>
	</cfif>
</cfloop>
<cfif cert_tot eq totamt>
<!--- just gift certificates are in order --->
	<cfset session.shi=5*ceiling(cert_num/2)>
<cfelse>
	<cfset shiptot=totamt-cert_tot>
	<cfif shiptot le 60>
		<cfset session.shi=6>
	<cfelse>
		<cfset session.shi=shiptot*.1>
	</cfif>
</cfif>
<!--- special sale dec 7, 2013 --->
<cfif now() lt createDate(2013,12,8) and now() gt createDate(2013,12,6)>
	<cfif shiptot GT 75>
		<cfset session.shi=0>
	</cfif>
</cfif>
	<TR>
	<TD COLSPAN=3>
	<B>Shipping, Handling and Insurance*</B></FONT>
	</TD>
	<td align=right>
	<B><cfif session.shi GT 0>#DollarFormat(session.shi)#<cfelse>FREE</cfif></B></FONT>
	</td>
	<TD></TD>
	</TR>

	<TR>
	<TD COLSPAN=3 class="tier1">
	<B>Total**</B>
	</TD>
	<td align=right class="tier1">
	<B>#DollarFormat(totamt+session.shi)#</B>
	</td>
	<TD class="tier1"></TD>
	
	</TR>
<cfif saved GT 0>
	<tr><td colspan="5" class="sale"><br/><b>You've saved #dollarformat(saved)#!</b></td>
	</tr>
</cfif>
</cfoutput>
</cfif>
</TABLE>

<cfif ArrayLen(session.Cart) eq 0>
<BR>
<B>If you've added items to your cart, but the cart is empty, it may
be that you don't have cookies enabled on your browser.  You must have
cookies enabled in order to use our online shopping cart.</B><P>
<cfelse>
<BR>
* <B>This is an estimated shipping amount.  In the event that shipping charges differ from the estimate, we 
will contact you prior to charging your order.  Please allow 7-10 days for shipping.<P>
** Minnesota Residents add <cfoutput>#taxrate*100#</cfoutput>% sales tax<BR>
<cfif taxrate neq localtax>
** Cook County Residents add <cfoutput>#localtax*100#%</cfoutput> sales tax<br/>
</cfif>
<BR></B>
<b>To remove an item from your cart, change its quantity to 0.</b>
<p>
</cfif>
</div>
<CENTER>
<BR>
<table class="linelist"><tr><td class="cartlink"><A HREF="index.cfm">Continue Shopping</A></td>
<td class="cartlink"><A HREF="checkout.cfm">Check Out</A></td></tr></table>
</center>


<cfinclude template="Templates/bottom.cfm">