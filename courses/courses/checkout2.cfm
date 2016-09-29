<cfif not IsDefined("Form.shipaddr1")>
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

<cfif not isdefined("form.newsletter")><cfset form.newsletter="true"></cfif>

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
			Set ShipAddr1='#ShipAddr1#', 
			ShipAddr2='#ShipAddr2#', 
			ShipCity='#ShipCity#',
			ShipState='#ShipState#', 
			ShipZip='#ShipZip#', 
			ShipCountry='#ShipCountry#', 
			ShipPhone='#ShipPhone#', 
			workphone='#form.workphone#',
			Email='#Email#',
			newsletter=#form.newsletter#
		Where CustID = <cfqueryparam value="#Form.CustID#" cfsqltype="CF_SQL_INTEGER">
	</cfquery>
	
	<cfset CustID="#Form.CustID#">
<cfelse>
	<cftransaction>
		<cfquery datasource="#dsn#" name="AddCust">
			INSERT INTO Customers
				(ShipAddr1, ShipAddr2, ShipCity,
				ShipState, ShipZip, ShipCountry, ShipPhone, 
				workphone, Email, newsletter)
				Values
				('#ShipAddr1#', '#ShipAddr2#', 
				'#ShipCity#', '#ShipState#', '#ShipZip#', '#ShipCountry#', 
				'#ShipPhone#', '#form.workphone#', '#Email#', #form.newsletter#)
		</cfquery>
		<cfquery datasource="#dsn#" name="GetCustID">
			select Max(CustID) as MaxID from Customers
		</cfquery>
		<cfif GetCustID.RecordCount GT 0>
			<cfset CustID="#GetCustID.MaxID#">
		<cfelse>
			<script language="JavaScript">alert("Error recording customer information.  Please contact us.");</script>
			<cflocation url="regsiter.cfm">
		</cfif>
	</cftransaction>
</cfif>

<!--- build the registration in the database ---->
<cftransaction>
	<cfquery name="MakeOrder" datasource="#dsn#">
		Insert into registrations
			(custid, howheard, formerstudent)
			Values (#CustID#, '#Form.howheard#'
			<cfif Isdefined("form.formerstudent") and form.formerstudent eq "true">
				, true
			<cfelse>
				,false
			</cfif>
			)
	</cfquery>
	<cfquery name="GetOrder" datasource="#dsn#">
		Select Max(rid) as MaxID from registrations
	</cfquery>
	<cfif GetOrder.RecordCount EQ 0>
		<script language="JavaScript">alert("Error recording customer information.  Please contact us.");</script>
		<cflocation url="register.cfm">
	</cfif>
	<cfset fees=0>
	<cfloop index="idx" from=1 to="#ArrayLen(session.Cart)#">
		<cfquery datasource="#dsn#" name="AddItems">
			Insert into reg_courses
				(rid, cid, startdt, students, children, materials)
				Values (#GetOrder.MaxID#, #session.Cart[idx].cID#,
					#session.Cart[idx].startdt#, #session.Cart[idx].students#,
					#session.cart[idx].children#,
					#session.cart[idx].materials#
					)
		</cfquery>
		<cfloop index="y" from="1" to="#session.cart[idx].students#">
			<cfquery name="AddStudent" datasource="#dsn#">
				insert into reg_students (rid, cid, startdt, name, under18, tuition, mindeposit, comments)
				values (#getOrder.MaxID#, #session.cart[idx].cid#, #session.cart[idx].startdt#,
					'#form["student#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#"]#'
					<cfif session.cart[idx].paybyproject>
						<cfif y EQ 1>
							,false, #session.cart[idx].tuition#, #round(session.cart[idx].deposit)#
							<cfif structKeyExists("#form#", "comments#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#")>
								, '#form["comments#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#"]#'
							<cfelse>
								, ''
							</cfif>
							<cfset fees=fees+session.cart[idx].deposit>
						<cfelse>
							<cfif session.cart[idx].intergen and y gt (session.cart[idx].students-session.cart[idx].children)>
								, true
							<cfelse>
								, false
							</cfif>
							, 0, 0, ''
						</cfif>
					<cfelse>
						<cfif session.cart[idx].intergen and y gt (session.cart[idx].students-session.cart[idx].children)>
							, true, #numberformat(session.cart[idx].tuition*.75, '9999.00')#, #round(session.cart[idx].kiddeposit)#
							<cfset fees=fees+session.cart[idx].kiddeposit>
						<cfelse>
							, false, #session.cart[idx].tuition#, #round(session.cart[idx].deposit)#
							<cfset fees=fees+session.cart[idx].deposit>
						</cfif>
						<cfif structKeyExists("#form#", "comments#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#")>
							, '#form["comments#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#"]#'
						<cfelse>
							, ''
						</cfif>
						
					</cfif>
					)
			</cfquery>
		</cfloop>
	</cfloop>
</cftransaction>

<!--- if they made a donation, save it --->
<cfset type="once">
<cfif isdefined("form.donation") and form.donation eq "other">
	<cfset form.donation = form.donother>
</cfif>
<cfif isdefined("form.member") and form.member eq 'true' and form.donation GT 0>
<cfset fees=fees+form.donation>

<cfquery name="saveDonation" datasource="#dsn#">
	insert into donations (address, city, state, zip, phone, email,
		donation, type, autorenew, ccnbr)
		values ('#form.shipaddr1#', '#form.shipcity#', '#form.shipstate#', '#form.shipzip#',
			'#form.shipphone#', '#form.email#'
			, #form.donation#, '#type#'
			, false, '#getOrder.MaxID#'
			)
</cfquery>
</cfif>

<cfif fees gt 0>
	<cflocation url="#secure_url#payment.php?rid=#GetOrder.MaxID#" addtoken="no">
<cfelse>
	<cflocation url="checkout3.cfm?orderid=#getOrder.MaxID#" addtoken="No">
</cfif>