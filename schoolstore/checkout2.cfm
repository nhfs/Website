

<cfif not IsDefined("Form.ShipName")>
	<cflocation url="checkout.cfm">
</cfif>

<!-- revalidate email address -->
<cf_emailVerify email="#FOrm.Email#">
<cfif Not valid>
	<script language="Javascript">
		alert ("You've entered an invalid email address - <cfoutput>#why#</cfoutput>.");
		history.back();
	</script>
	<cfabort>
</cfif>
<cfif session.Upd>
<!--- first, make sure they didn't change their email address to 
      one that is already in the database  ----->
	<cfquery datasource="#dsn#" name="GetPW">
		Select Email from Customers
			where CustID=<cfqueryparam value="#Form.CustID#" cfsqltype="CF_SQL_INTEGER">
	</cfquery>
	<cfif GetPw.Email neq Form.Email>
		<cfquery datasource="#dsn#" name="CheckEmail">
			select Email from Customers
				where Email='#Form.Email#' and Password > ''
		</cfquery>
		<cfif CheckEmail.RecordCount gt 0>
			<cflocation url="checkout1.cfm?E=#GetPW.Email#&ErrCd=Yes">	
		</cfif>
	</cfif>
	<cfquery datasource="#dsn#" name="UpdCust">
		Update Customers
			Set ShipName='#ShipName#', 
			ShipCo='#ShipCo#', 
			ShipAddr1='#ShipAddr1#', 
			ShipAddr2='#ShipAddr2#', 
			ShipCity='#ShipCity#',
			ShipState='#ShipState#', 
			ShipZip='#ShipZip#', 
			ShipCountry='#ShipCountry#', 
			ShipPhone='#ShipPhone#', 
			Email='#Email#',
			BillName='#BillName#', 
			BillCo='#BillCo#', 
			BillAddr1='#BillAddr1#', 
			BillAddr2='#BillAddr2#', 
			BillCity='#BillCity#',
			BillState='#BilLState#', 
			BillZip='#BillZip#', 
			BillCountry='#BillCountry#', 
			BillPhone='#BillPhone#'
		Where CustID = <cfqueryparam value="#Form.CustID#" cfsqltype="CF_SQL_INTEGER">
	</cfquery>
	
	<cfset CustID="#Form.CustID#">
<cfelse>
	<cftransaction>
<!---		<cfinsert datasource="#dsn#"
			tablename="Customers"> --->
		<cfquery datasource="#dsn#" name="AddCust">
			INSERT INTO Customers
				(ShipName, ShipCo, ShipAddr1, ShipAddr2, ShipCity,
				ShipState, ShipZip, ShipCountry, ShipPhone, Email,
				BillName, BillCo, BillAddr1, BillAddr2, BillCity,
				BillState, BillZip, BillCountry, BillPhone)
				Values
				('#ShipName#', '#ShipCo#', '#ShipAddr1#', '#ShipAddr2#', 
				'#ShipCity#', '#ShipState#', '#ShipZip#', '#ShipCountry#', 
				'#ShipPhone#', '#Email#',
				'#BillName#', '#BillCo#', '#BillAddr1#', '#BillAddr2#', 
				'#BillCity#', '#BillState#', '#BillZip#', '#BillCountry#', 
				'#BillPhone#')
							
		</cfquery>
		<cfquery datasource="#dsn#" name="GetCustID">
			select Max(CustID) as MaxID from Customers
		</cfquery>
		<cfif GetCustID.RecordCount GT 0>
			<cfset CustID="#GetCustID.MaxID#">
		<cfelse>
			<script language="JavaScript">alert("Error recording customer information.  Please contact us.");</script>
			<cflocation url="index.cfm">
		</cfif>
	</cftransaction>
</cfif>
<cfdump var=#Session.Cart#>
<!--- build the order in the database ---->
<cftransaction>
	<cfquery name="MakeOrder" datasource="#dsn#">
		Insert into Orders
			(CustomerID, Comments, ShipQuote)
			Values (#CustID#, '#Form.Comments#', #session.shi#)
	</cfquery>
	<cfquery name="GetOrder" datasource="#dsn#">
		Select Max(OrderID) as MaxID from Orders
	</cfquery>
	<cfif GetOrder.RecordCount EQ 0>
		<script language="JavaScript">alert("Error recording customer information.  Please contact us.");</script>
		<cflocation url="index.cfm">
	</cfif>
	<cfloop index="cnt" from=1 to=#ArrayLen(session.Cart)#>
		<cfquery datasource="#dsn#" name="AddItems">
			Insert into OrderItems
				(OrderID, ItemID, Price, saleprice, Qty, _Option
				<cfif session.Cart[cnt].Size GT "">
					, Size
				</cfif>
				, taxable
				)
				Values (#GetOrder.MaxID#, #session.Cart[cnt].ItemID#,
					#session.Cart[cnt].Price+session.cart[cnt].saved#
				<cfif session.cart[cnt].saved GT 0>
					, #session.cart[cnt].price#
				<cfelse>
					, 0
				</cfif>
					, #session.Cart[cnt].Qty#
				<cfif session.Cart[cnt].Opt GT "">
					, '#session.Cart[cnt].Opt#'
				<cfelse>
					, ''
				</cfif>
				<cfif session.Cart[cnt].Size GT "">
					, '#session.Cart[cnt].Size#'
				</cfif>
				, #session.cart[cnt].Taxable#
				)
		</cfquery>
	</cfloop>
</cftransaction>

<cflocation url="#secure_url#checkoutcc.html?#URLEncodedFormat("OID=" & GetOrder.MaxID & "&Phone=" & Form.BillPhone)#">