<cfset masthead="kayaks">
<cfinclude template="Templates/topper.cfm">
<cfif not IsDefined("Form.OrderID")>
	<cflocation url="catalog.cfm">
</cfif>

<cfquery datasource="#dsn#" name="SaveOrder">
	UPDATE Orders set Confirmed=True
		WHERE OrderID=<cfqueryparam value="#Form.OrderID#" cfsqltype="CF_SQL_INTEGER">
</cfquery>

<cfquery datasource="#dsn#" name="GetOrder">
	select Orders.*
		from Orders 
		where Orders.OrderID=<cfqueryparam value="#Form.OrderID#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<cfif GetOrder.CCNbr GT "">
<cf_encrypter SourceNum="#GetOrder.CCNbr#" DCode=True>
<cfif EncryptError>
	<Cfoutput>ERROR! #Message#</CFOUTPUT>
	<cfabort>
</cfif>
</cfif>

<cfquery datasource="#dsn#" name="GetCust">
	Select * from Customers
		WHERE CustID = #GetOrder.CustomerID#
</cfquery>
<cfquery datasource="#dsn#" name="GetItems">
	Select OrderItems.*, Items.*, GroupName, LineName
		From OrderItems INNER JOIN 
			(Items INNER JOIN 
			(Groups INNER JOIN _lines on Groups.LineID = _lines.LineID)
			on Items.GroupID = Groups.GroupID)
			on OrderItems.ItemID = Items.ItemID
		Where OrderID = <cfqueryparam value="#Form.OrderID#" cfsqltype="CF_SQL_INTEGER">
</cfquery>


<div class="data wide">
<BR>
<DIV ALIGN=Center><h2>
Your Order has been Placed</h2></DIV>

Thank you for your order!  Please print this page for your records.
Please do not use your browser's back button to move off this page,
or you may end up placing the order again.

<P>
<TABLE width="90%" align="Center" class="tier1">
<TR><TD>

<table width="98%" align="center" class="orderform">
<TR>
<cfoutput>
<TD width="49%"><FONT SIZE="+1"><b>#co_name#</b></FONT></TD>
<TD width="2%"></TD>
<TD width="49%" align="Right"><FONT SIZE="+1">Order ##: <b>#Form.OrderID#</b></FONT></TD></cfoutput>
</TR>
<TR class="tier1">
<td height="1" colspan="3"></td>
</TR>
<TR>
<TD><font size="+1"><b>
Bill To</b></font>
</TD>
<TD class="tier1" rowspan="2"></TD>
<TD>
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
</TD>
<TD valign="top">
#ShipName#<BR>
<cfif ShipCo GT "">#ShipCo#<BR></cfif>
#ShipAddr1#<BR>
<cfif ShipAddr2 GT "">#ShipAddr2#<BR></cfif>
#ShipCity#, #ShipState# #ShipZip#<BR>
<cfif ShipCountry EQ "CAN">Canada</cfif>
<cfif ShipPhone GT "">#ShipPhone#<BR></cfif>
</TD>
</TR>
</cfoutput>



<TR>
<TD colspan="3"><BR><BR>

<TABLE WIDTH="100%">
<TR>
<TD colspan="4" height="1" class="tier1"></TD>
</TR>

<TR>
<TD width="40%"><b>Item</b></TD>
<TD width="25%" align=right><b>Price</b></TD>
<TD width="10%" align=right><b>Qty</b></TD>
<TD width="25%" align=right><b>Total</b></TD>

</TR>
<TR>
<TD COLSPAN="4" height="1" class="tier1"></TD>
</TR>
<cfset totqty=0>
<cfset totamt=0>
<cfset taxamt=0>
<cfset saved=0>

<cfoutput query="GetItems">
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
	<cfset saved=saved+(qty*(price-saleprice))>
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

</cfoutput>
<tR>
<TD COLSPAN=4 height=1 class="tier1"></TD>
</tr>

<cfoutput query="GetOrder" maxrows="1">
<TR>
<TD COLSPAN=2>
<B>Subtotals</B>
</TD>
<TD align=right>
<B>#totqty#</B>
</TD>
<td align=right>
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
<B><cfif ShipQuote gt 0>#DollarFormat(ShipQuote)#<cfelse>FREE</cfif></B>
</td>
</TR>

<tR>
<TD COLSPAN=4 height=1 class="tier1"></TD>
</tr>

<TR>
<TD COLSPAN=3>
<B>Total</B>
</TD>
<td align=right>
<B>#DollarFormat(totamt+ShipQuote+stax)#</B>
	</td>
	
	</TR>
<tR>
<TD COLSPAN=4 height=1 class="tier1"></TD>
</tr>

<TR>
<TD COLSPAN="4">
<B>
* This is an estimated shipping amount.  In the event that shipping charges differ from the estimate, we 
will contact you prior to charging your order.  Please allow 7-10 days for shipping.
</B>
</TD>
</TR>
<cfif saved GT 0>
<tr>
<td colspan="4">
<br/>
<span class="sale">You saved #dollarformat(saved)# on this order!</span>
</td>
</tr>
</cfif>

</TABLE>

</TD></TR>
<TR>
<TD COlspan="3">
<BR><BR>
<FONT SIZE="+1"><B>
<cfif CCNbr GT "">
	Charged to credit card ending in ...#right(CodedNum,4)#, Exp. #CCExp#
<cfelse>
	Payment is pending
</cfif>

</B></FONT></cfoutput>
</TD>
</TR>

</TABLE></TD></TR></TABLE><DIV ALIGN="CENTER">
<P>
<A HREF="index.cfm">Return to catalog</A>
</P>
</DIV>
</div>

<!--- email order ---->
<cfif GetCust.Email GT "">
	<cfset mailfrom=GetCust.Email>
<cfelse>
	<cfset mailfrom=#adm_email#>
</cfif>

<cfmail to="#adm_email#" 
	from="#mailfrom#"
	subject="Order from Online Catalog"
	query="GetOrder">
Customer Information:<cfloop query="GetCust"><cfif Email GT ""> #Email#</cfif>
Ship to:
    #ShipName#<cfif ShipCo GT "">
    #ShipCo#</cfif>
    #ShipAddr1#<cfif ShipAddr2 GT "">
    #ShipAddr2#</cfif>
    #ShipCity#, #ShipState# #ShipZip#<cfif ShipCountry EQ "CAN">
    Canada</cfif><cfif ShipPhone GT "">
    #ShipPhone#</cfif>

Bill to:
    #BillName#<cfif BillCo GT "">
    #BillCo#</cfif>
    #BillAddr1#<cfif BillAddr2 GT "">
    #BillAddr2#</cfif>
    #BillCity#, #BillState# #BillZip#<cfif BillCountry EQ "CAN">
    Canada</cfif><cfif BillPhone GT "">
    #BillPhone#</cfif>
</cfloop>	
Order ##: #OrderID#	
Items Ordered:<cfloop query="GetItems">
    #GroupName#<cfif ItemName GT "">-#ItemName#</cfif> from #LineName# <cfif ItemCode GT "">(Item ##: #ItemCode#) </cfif>
       - Quantity: #Qty# at <cfif saleprice GT 0>#dollarformat(saleprice)#<cfelse>#DollarFormat(Price)#</cfif><cfif _Option GT "">
       - Option: #_Option#</cfif><cfif Size GT "">
       - Size: #Size#</cfif>
</cfloop><cfif stax GT 0>
Quoted Sales Tax: #Dollarformat(stax)#</cfif>	
Quoted Shipping Price: #DollarFormat(ShipQuote)#
	
Comments/Special Instructions: #Comments#

Charge Information:
    <cfif CCNBR GT "">Credit Card Number: #CodedNum#, Expires: #CCExp#, CVD Code: #cvd#<cfelse>
    Please call the Billing Phone number for credit card information</cfif>
</cfmail>

<cfset session.cart=ArrayNew(1)>

<cfinclude template="Templates/bottom.cfm">

