<cfinclude template="Templates/header.cfm">
<cfif not IsDefined("Form.orderid") and not IsDefined("URL.orderid")>
	<cflocation url="register.cfm">
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

<cfset ccdep=replace(form.ccdeposit, "$","","All")>
<cfif ccdep eq ""><cfset ccdep=0></cfif>
<cfset gcdep=replace(form.gcdeposit, "$","","All")>
<cfif gcdep eq ""><cfset gcdep=0></cfif>

<cfquery datasource="#dsn#" name="SaveOrder">
	UPDATE registrations set CCNbr='#CodedNum#'
			, expdt='#CCDt#'
			, cvd='#form.cvd#'
			, deposit=#form.deposit#
			, ccdeposit=<cfqueryparam value="#ccdep#" cfsqltype="CF_SQL_DECIMAL">
			, gcdeposit=<cfqueryparam value="#gcdep#" cfsqltype="CF_SQL_DECIMAL">
			, gcname='#form.gcname#'
			, gcnbr='#form.gcnbr#'
		WHERE rid=<cfqueryparam value="#Form.OrderID#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<cfquery datasource="#dsn#" name="GetCID">
	select custid from registrations where
		rid=<cfqueryparam value="#Form.OrderID#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<cfquery datasource="#dsn#" name="BillName">
	update customers set billname='#form.billname#' where custid=#GetCID.custid#
</cfquery>

<cfelse>

<cfquery datasource="#dsn#" name="GetOrder">
	select r.* , rc.*, c.name, c.paybyproject
		from registrations r INNER JOIN 
			(reg_courses rc INNER JOIN courses c on rc.cid=c.cid)
			ON r.rid = rc.rid
		where r.rid=<cfqueryparam value="#Trim(URL.OrderID)#" cfsqltype="CF_SQL_INTEGER">
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
		WHERE CustID = #GetOrder.CustID#
</cfquery>
<BR>
<h2>Step 3 - Review and Confirm Registration Details</h2>
<p>
We are almost there! You are now ready to finalize your registration. Please
VERIFY that the information below is correct and THEN click the "Register"
button to complete your registration. 
</p><p>
Please note that upon registering you will receive BOTH a confirmation
e-mail (almost immediately) and then a confirmation mailing from North House
(within ten days).  If either of these does not arrive, please contact us at
218-387-9762.  
</p>

<cfform name="Confirm" action="checkout4.cfm" method="POST" scriptSrc="http://kite.boreal.org/CFIDE/scripts">
<cfoutput><input type="hidden" name="rid" value="#URL.OrderID#"></cfoutput>
<div class="regform">
<div class="title">Student Information</div>

<cfoutput query="GetCust">
#ShipAddr1#<BR>
<cfif BillAddr2 GT "">#ShipAddr2#<BR></cfif>
#ShipCity#, #ShipState# #ShipZip#<BR>
<cfif ShipCountry EQ "CAN">Canada<BR></cfif>
<cfif ShipPhone GT "">#ShipPhone#<BR></cfif>
<cfif workphone GT "">#workPhone#<BR></cfif>
<cfif Email GT "">#Email#</cfif>
<br/><br/>
</cfoutput>

<div class="title">Enrollment Information</div>
<cfoutput>
<table class="cartlist">
	<tr><th>Students</th><th>Under 18</th><th>Course Title</th><th>Course Dates</th><th>Names</th><th>Tuition</th>
	<th>Materials</th><th>Deposit</th></tr>
	<cfset tottu=0>
	<cfset totma=0>
	<cfset totde=0>
	<cfloop query="GetOrder">
		<tr>
		<td align="right">#students#</td>
		<td align="right">#children#</td>
		<td>#name#</td>
		<td>#DateFormat(startdt, "mm/dd/yyyy")#</td>
		<cfquery name="GetStudents" datasource="#dsn#">
			select * from reg_students where rid=#url.orderid# and
				cid=#cid# and startdt=#startdt# order by sid
		</cfquery>
		<cfset sumtu=0>
		<cfset sumde=0>
		<td>
		<cfloop query="getStudents">
			<cfif currentRow GT 1>, </cfif>#name#
			<cfset sumtu=sumtu+tuition>
			<cfset sumde=sumde+mindeposit>
		</cfloop>
		</td>
		<td align="right">#Dollarformat(sumtu)#<cfset tottu=tottu+sumtu></td>
		<td align="right"><cfif paybyproject>
				#Dollarformat(materials)#
				<cfset totma=totma+materials>
			<cfelse>
				#DollarFormat(materials*students)#
				<cfset totma=totma+(materials*students)>
			</cfif></td>
		<td align="right">#Dollarformat(sumde)#
				<cfset totde=totde+sumde></td>
		</tr>
		<tr class="cartdiv"><td colspan="8"></td></tr>
	</cfloop>
	
	<!--- made a donation - bring it in --->
	<cfquery name="getDon" datasource="#dsn#">
		select donation from donations where ccnbr='#url.orderid#'		
	</cfquery>
	<cfif getDon.recordcount GT 0>
		<tr>
		<td colspan="7">Your membership donation - thank you!</td>
		<td align="right">#dollarformat(getDon.donation)#</td>
		</tr>
		<tr class="cartdiv"><td colspan="8"></td></tr>
		<cfset totde=totde+getDon.donation>
	</cfif>
	
	<tr class="carttot">
		<td colspan="5">Totals</td>
		<td align="right">#dollarformat(tottu)#</td>
		<td align="right">#dollarformat(totma)#</td>
		<td align="right">#dollarformat(totde)#</td>
	</tr>
	<tr class="carttot">
		<cfif getOrder.gcdeposit gt 0>
		<td colspan="7">Deposit Paid by Credit Card</td>
		<td align="right">#dollarformat(GetOrder.ccdeposit)#</td>
		</tr><tr class="carttot">
		<td colspan="7">Deposit Paid by Gift Certificate</td>
		<td align="right">#dollarformat(GetOrder.gcdeposit)#</td>
		</tr>
		<tr class="cartdiv"><td colspan="8"></td></tr>
		<tr class="carttot">
		</cfif>
		<td colspan="7">Total Deposit Paid</td>
		<td align="right">#dollarformat(GetOrder.deposit)#</td>
	</tr>
	</table>
</cfoutput>
<br/><br/>

<cfif getorder.ccdeposit GT 0 or getorder.gcdeposit GT 0>
<div class="title">Payment Information</div>
<cfoutput query="GetOrder" maxrows="1">
#GetCust.Billname#<br/>
<cfif ccdeposit gt 0>
Credit card ending in ...#Right(CodedNum,4)#<cfif gcdeposit gt 0> (#dollarformat(ccdeposit)#)</cfif><br/>
Expires #expdt#<br/>
</cfif>
<cfif gcdeposit GT 0>
Gift certificate #gcnbr# for #gcname#<cfif ccdeposit gt 0> (#dollarformat(gcdeposit)#)</cfif><br/>
</cfif>
</cfoutput>
</cfif>
<br/>

<input type="Submit" value="Register">
</div>
</cfform>
</cfif>
<cfinclude template="Templates/footer.cfm">