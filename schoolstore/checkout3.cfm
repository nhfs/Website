<cfset masthead="kayaks">
<cfinclude template="Templates/topper.cfm">
<cfif not IsDefined("Form.OrderID") and not IsDefined("URL.OrderID")>
	<cflocation url="catalog.cfm">
</cfif>

<cfif IsDefined("Form.OrderID")>
<cfset CCDt=Form.CCMM & "/" & Form.CCYY>
<cfif Form.CCNbr GT "">
	<cf_encrypter SourceNum="#Form.CCNbr#">
	<cfif EncryptError>
		<Cfoutput>ERROR! #Message#</CFOUTPUT>
		<cfabort>
	</cfif>
<cfelse>
	<cfset CodedNum="">
</cfif>

<cfquery datasource="#dsn#" name="SaveOrder">
	UPDATE Orders set CCNbr='#CodedNum#'
			, CCExp='#CCDt#'
		<cfif form.cvd GT "">
			, cvd='#form.cvd#'
		</cfif>			
		<cfif IsDefined("Form.CallMe") and Form.CallMe eq "Call">
			, CallMe=True
		</cfif>
		WHERE OrderID=<cfqueryparam value="#Form.OrderID#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<cfelse>
<cfquery datasource="#dsn#" name="GetOrder">
	select Orders.* , OrderItems.*, Items.ItemName, Groups.GroupName
		from Orders INNER JOIN 
			(OrderItems INNER JOIN 
			(Items INNER JOIN Groups on Items.GroupID = Groups.GroupID)
			ON OrderItems.ItemID = Items.ItemID)
			on Orders.OrderID=OrderItems.OrderID
		where Orders.OrderID=<cfqueryparam value="#Trim(URL.OrderID)#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<cfif GetOrder.RecordCount GT 0 and GetOrder.CCNbr GT "">
	<cf_encrypter SourceNum="#GetOrder.CCNbr#" DCode=true>
	<cfif EncryptError>
		<Cfoutput>ERROR! #Message#</CFOUTPUT>
		<cfabort>
	</cfif>
<cfelse>
	<cfset CodedNum="">
</cfif>

<cfquery datasource="#dsn#" name="GetCust">
	Select * from Customers
		WHERE CustID = #GetOrder.CustomerID#
</cfquery>
<BR>
<div class="data wide">
<DIV ALIGN=Center><h2>Check-Out Step 3 - Order Confirmation</h2></div>

Thank you!  We're now ready to process your order.  Please verify the 
information below and, if it's correct, click the "Place Order" button to 
place your order.

<P>
<cfform name="Confirm" action="checkout4.cfm" method="POST" scriptSrc="http://kite.boreal.org/CFIDE/scripts">
<cfoutput><input type="hidden" name="OrderID" value="#URL.OrderID#"></cfoutput>
<TABLE width="90%" align="Center">
<TR>
<TD width="49%" align="center" class="tier1"><font size="+1"><b>
Bill To</b></font>
</TD>
<TD width="2%"></TD>
<TD width="49%" align="Center" class="tier1">
<font size="+1"><B>
Ship To</B></font>
</TD>
</TR>
<TR>
<TD valign="top">
<cfoutput query="GetCust">
#BillName#<BR>
<cfif BillCo GT "">#BillCo#<BR></cfif>
#BillAddr1#<BR>
<cfif BillAddr2 GT "">#BillAddr2#<BR></cfif>
#BillCity#, #BillState# #BillZip#<BR>
<cfif BillCountry EQ "CAN">Canada<BR></cfif>
<cfif BillPhone GT "">#BillPhone#<BR></cfif>
<cfif Email GT "">#Email#</cfif>
</TD><TD></TD>
<TD valign="top">
#ShipName#<BR>
<cfif ShipCo GT "">#ShipCo#<BR></cfif>
#ShipAddr1#<BR>
<cfif ShipAddr2 GT "">#ShipAddr2#<BR></cfif>
#ShipCity#, #ShipState# #ShipZip#<BR>
<cfif ShipCountry EQ "CAN">Canada<BR></cfif>
<cfif ShipPhone GT "">#ShipPhone#<BR></cfif>
</TD>
</TR>
</cfoutput>

<TR>
<TD colspan="3"><BR></TD></TR>
<TR>
<TD colspan="3" align="center" class="tier1"><font size="+1"><b>
Charge Order To</b></font>
</TD></TR>
<cfoutput query="GetOrder" maxrows="1">
<TR><TD COLSPAN="3" align="center">
<cfif CCNbr GT "">
	Credit card ending in ...#Right(CodedNum,4)#, Expires #CCExp#
<cfelse>
	Please call me at #GetCust.BillPhone# for Credit Card information.
	I understand that my order will not be shipped until you have this
	information.
</cfif>

</TD></TR>
</cfoutput>

<TR>
<TR>
<TD colspan="3"><BR></TD></TR>
<TR>

<TD colspan="3" align="center" class="tier1"><font size="+1">
<b>Order Information</b></font>
</TD>

</TR>
<TR><TD COLSPAN="3">

<TABLE WIDTH="100%">
<TR>
<TD width="40%" class="tier2">Item</FONT></TD>
<TD width="25%" align=right class="tier2">Price</TD>
<TD width="10%" align=right class="tier2">Qty</TD>
<TD width="25%" align=right class="tier2">Total</TD>

</TR>
<cfset totqty=0>
<cfset totamt=0>
<cfset taxamt=0>

<cfoutput query="GetOrder">
<TR>
<TD>
	#GroupName#<cfif ItemName GT "">-#ItemName#</cfif>
	<cfif _Option GT ""> (#_Option#</cfif>
	<cfif Size GT "">
		<cfif _Option GT "">
			, #Size#
		<cfelse>
			(#Size#
		</cfif>
	</cfif>
	<cfif _Option GT "" OR Size GT "">)</cfif>
</TD>
<TD align=right>
	<cfset amt=price>
	<cfif saleprice GT 0>
	<span class="struck">#dollarformat(price)#</span>
	<span class="sale">
	<cfset amt=saleprice>
	#DollarFormat(saleprice)#
	</span>
	<cfelse>
	#dollarformat(price)#
	</cfif>

</TD>
<TD align=right>
	#Qty#
	<cfset totqty=totqty+#Qty#>
</TD>
<TD align=right>
	<cfset tot=#Qty# * #amt#>
	#DollarFormat(tot)#
	<cfset totamt = totamt+tot>
	<cfif taxable>
		<cfset taxamt=taxamt+tot>
	</cfif>
</TD>
</TR>
<cfif CurrentRow lt RecordCount>
<tR>
<TD COLSPAN=4 height=1 class="tier2"></TD>
</tr>
</cfif>

</cfoutput>
<cfoutput query="GetOrder" maxrows="1">
<TR>
<TD COLSPAN=2 class="tier2">
<B>Subtotals</B>
</TD>
<TD align=right class="tier2">
<B>#totqty#</B>
</TD>
<td align=right class="tier2">
<B>#DollarFormat(totamt)#</B>
</td>
</TR>

<cfif GetCust.ShipState eq "Minnesota">
	<cfif GetCust.shipzip eq "55604" or
		GetCust.shipzip eq "55605" or
		GetCust.shipzip eq "55606" or
		GetCust.shipzip eq "55612" or
		GetCust.shipzip eq "55613" or
		GetCust.shipzip eq "55615">
		<cfset stax=taxamt*localtax>
		<cfset tax=localtax>
	<cfelse>
		<cfset stax=taxamt*taxrate>
		<cfset tax=taxrate>
	</cfif>

	<tr>
	<TD COLSPAN="3">
	<B>Minnesota Sales Tax (#tax*100#%)</B>
	</TD>
	<TD align="right">
	<B>#DollarFormat(stax)#</B>
	</TD>
	</tr>
<cfelse>
	<cfset stax=0>
</cfif>

<TR>
<TD COLSPAN=3>
<B>Shipping, Handling and Insurance*</B>
</TD>
<td align=right>
<B><cfif ShipQuote GT 0>#DollarFormat(ShipQuote)#
	<cfelse>FREE</cfif></B>
</td>
</TR>

<TR>
<TD COLSPAN=3 class="tier2">
<B>Total</B>
</TD>
<td align=right class="tier2">
<B>#DollarFormat(totamt+ShipQuote+stax)#</B>
	</td>
	
	</TR>

<TR>
<TD COLSPAN="4">
<B>*This is an estimated shipping amount.  In the event that shipping charges differ from the estimate, we 
will contact you prior to charging your order.  Please allow 7-10 days for shipping.

</B>
</TD>
</TR>

</TABLE>
</cfoutput>
</TD></TR>
<TR>
<TD COLSPAN="3" ALIGN=CENTER>
<input type="Submit" value="Place Order">
</TD>
</TR>
</TABLE>
</cfform>
</cfif>
</div>
<cfinclude template="Templates/bottom.cfm">