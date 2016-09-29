<cfinclude template="Templates/header.cfm">
<cfif not IsDefined("Form.rid")>
	<cflocation url="register.cfm">
</cfif>

<cfquery datasource="#dsn#" name="SaveOrder">
	UPDATE registrations set Confirmed=True
		WHERE rid=<cfqueryparam value="#Form.rid#" cfsqltype="CF_SQL_INTEGER">
</cfquery>

<cfquery datasource="#dsn#" name="GetOrder">
	select *
		from registrations 
		where rid=<cfqueryparam value="#Form.rid#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<cfif GetOrder.CCNbr GT "">
<cf_encrypter SourceNum="#GetOrder.CCNbr#" DCode=True>
<cfif EncryptError>
	<Cfoutput>ERROR! #Message#</CFOUTPUT>
	<cfabort>
</cfif>
<cfelse>
<cfset codednum="">
</cfif>

<cfquery datasource="#dsn#" name="GetCust">
	Select * from Customers
		WHERE CustID = #GetOrder.CustID#
</cfquery>
<cfquery datasource="#dsn#" name="GetItems">
	select rc.*, c.name, c.paybyproject
		from reg_courses rc INNER JOIN courses c on rc.cid=c.cid
		where rc.rid=<cfqueryparam value="#form.rid#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<cfquery datasource="#dsn#" name="GetNames">
	select distinct name from reg_students
		where rid=<cfqueryparam value="#form.rid#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<cfquery datasource="#dsn#" name="getDon">
	select donation from donations where ccnbr='#form.rid#'
</cfquery>
<!--- email order ---->
<cfif GetCust.Email GT "">
	<cfset mailfrom=GetCust.Email>
<cfelse>
	<cfset mailfrom=#adm_email#>
</cfif>

<cfmail to="#reg_email#" 
	from="#mailfrom#"
	subject="Online Registration"
	type="html"
	query="GetOrder">
<style type="text/css">
	h1 {
		font-size: 125%;
		font-weight: bold;
		font-style: italic;
		text-decoration: underline;
		margin-top: 0px;
	}
	
	h2 {
		font-size: 110%;
		font-weight: bold;
		text-decoration: underline;
		margin-bottom: 0px;
	}
	
	.office {
		font-size: 80%;
		font-weight: bold;
		font-style: italic;
		margin-bottom: 0px;
	}
		
	.data {
		text-decoration: underline;
	}
		
</style>
<div class="office">North House Folk School</div>
<div class="office"  style="float: right">
OFFICE USE&nbsp;&nbsp;&nbsp;Reg____ CR____ DB____ MEM____
</div>
<h1>Course Registration Form</h1>

<br clear="all"/>
<div style="float: right">Completed By___________  Today's Date___________</div>
<h2>Student Information</h2>

Name(s) <span class="data"><cfloop query="GetNames">#name#<cfif CurrentRow neq RecordCount>, </cfif></cfloop>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span><br/>
Mailing Address  <span class="data">#GetCust.ShipAddr1#<cfif GetCust.ShipAddr2 GT "">, #GetCust.ShipAddr2#</cfif>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span><br/>
City <span class="data">#GetCust.ShipCity#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>  State <span class="data">#GetCust.ShipState#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Zip <span class="data">#GetCust.ShipZip#<cfif GetCust.ShipCountry EQ "CAN"> Canada</cfif>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<br/>
Home Phone <cfif GetCust.ShipPhone GT ""><span class="data">#GetCust.ShipPhone#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><cfelse>_______________</cfif>
Work Phone <cfif GetCust.WorkPhone GT ""><span class="data">#GetCust.Workphone#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><cfelse>_______________</cfif><br/>
How did you learn about North House? <cfif howheard GT ""><span class="data">#howheard#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><cfelse>____________________</cfif><br/>
Have you registered for a North House course before? <span class="data"><cfif formerstudent eq true>Yes<cfelse>No</cfif>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br/>
Do you want to become a member? <span class="data"><cfif getDon.recordCount GT 0>Yes<cfelse>No</cfif></span><br/>
<br/>
<h2>Enrollment Information</h2>
<table>
	<tr><th>Students</th><th>Under 18</th><th>Course Title</th><th>Course Dates</th><th>Names</th><th>Tuition</th>
	<th>Materials</th><th>Deposit</th></tr>
	<cfset tottu=0>
	<cfset totma=0>
	<cfset totde=0>
	<cfset comms=false>
	<cfloop query="GetItems">
		<tr>
		<td align="center">#students#</td>
		<td align="center">#children#</td>
		<td>#name#</td>
		<td>#DateFormat(startdt, "mm/dd/yyyy")#</td>
		<cfquery name="GetStudents" datasource="#dsn#">
			select * from reg_students where rid=#form.rid# and
				cid=#cid# and startdt=#startdt# order by sid
		</cfquery>
		<cfset sumtu=0>
		<cfset sumde=0>
		<td>
		<cfloop query="getStudents">
			#name#<cfif currentRow LT RecordCount>, </cfif>
			<cfset sumtu=sumtu+tuition>
			<cfset sumde=sumde+mindeposit>
			<cfif comments GT ""><cfset comms=true></cfif>
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
	</cfloop>
	<cfif getdon.recordcount GT 0>
		<cfset totde=totde+getDon.donation>
		<tr>
			<td colspan="7">Membership Donation</td>
			<td align="right">#dollarformat(getdon.donation)#</td>
		</tr>
	</cfif>
	<tr>
		<td colspan="5" align="right">Totals</td>
		<td align="right">#dollarformat(tottu)#</td>
		<td align="right">#dollarformat(totma)#</td>
		<td align="right"></td>
	</tr>
	<tr>
		<td colspan="7" align="right">NSD or Scholarship</td>
		<td align="right">$______</td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<cfif gcdeposit gt 0>
		<td colspan="7" align="right"><b>Deposit Paid by Credit Card</b></td>
		<td align="right"><b>#dollarformat(ccdeposit)#</b></td>
		</tr><tr>
		<td colspan="7" align="right"><b>Deposit Paid by Gift Certificate</b></td>
		<td align="right"><b>#dollarformat(gcdeposit)#</b></td>
		</tr>
		<tr>
		</cfif>
	
		<td colspan="7" align="right"><b>Total Deposit Paid</b></td>
		<td align="right"><b>#dollarformat(deposit)#</b></td>
		<td colspan="2"></td>
	</tr>
	</table>
<br/>
<cfif comms>
<h2>Comments</h2>
<table>
<tr><th>Course Title</th><th>Student</th><th>Comment</th></tr>
	<cfloop query="GetItems">
		<cfquery name="GetStudents" datasource="#dsn#">
			select comments, rs.name, commtitle from reg_students rs inner join courses c on rs.cid=c.cid
				where rid=#form.rid# and
				rs.cid=#cid# and startdt=#startdt# order by sid
		</cfquery>
		<cfset cname=name>
		<cfloop query="GetStudents">
			<cfif comments GT "">
				<tr>
				<td align="center">#cname#</td><td align="center">#name#</td><td align="center">#commtitle#: #comments#</td>
				</tr>
			</cfif>
		</cfloop>
	</cfloop>
</table>
<br/>
</cfif>

<h2>Payment Information</h2>
<cfif ccdeposit gt 0 or gcdeposit gt 0>
Deposit PAID by <cfif ccdeposit GT 0><b>Credit Card</b><cfif gcdeposit GT 0> and </cfif></cfif><cfif gcdeposit GT 0><b>Gift Certificate</b></cfif><br/>
<cfif ccdeposit GT 0>
<b>Credit Card Information</b><br/>
<div style="float: left; width: 100px;"><cfif left(CodedNum,1) eq "4"><b>X </b><cfelse>__</cfif>VISA</div>
<div style="float: left; width: 100px;"><cfif left(CodedNum,1) eq "5"><b>X </b><cfelse>__</cfif>MC</div>
<div style="float: left; width: 100px;"><cfif left(CodedNum,1) eq "6"><b>X </b><cfelse>__</cfif>DISCOVER</div>
<div style="float: left; width: 100px;"><cfif left(CodedNum,1) eq "3"><b>X </b><cfelse>__</cfif>AMX</div>
<br/>
CC## <span class="data">#CodedNum#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>  Exp. Date <span class="data">#expdt#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
 Amount <span class="data">#dollarformat(ccdeposit)#&nbsp;&nbsp;&nbsp;&nbsp;</span><br/>
Name <span class="data">#GetCust.BillName#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> V-Code <span class="data">#cvd#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<br/>
</cfif>
<cfif gcdeposit GT 0><cfif ccdeposit GT 0><br/></cfif>
<b>Gift Certificate Information</b><br/>
Gift Certificate## <span class="data">#gcnbr#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>  
 Amount <span class="data">#dollarFormat(gcdeposit)#&nbsp;&nbsp;&nbsp;&nbsp;</span><br/>
Name <span class="data">#gcname#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<br/>
</cfif>
<cfelse>
N/A
</cfif>
<h2>Additional Payments</h2>
Info Taken 	By  _______	Date   _______  Amount Due  ________   Payment ______
<br/>
Form of Payment: Cash ___  Check ________  Credit Card  ______________________________
<br/>
<div class="office">
Reg____ CR____ DB____
</div>
<h2>Course Cancellation</h2>
Date  ___________  NH Cancelled  ______   Student Called By _____   
<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student Requests Cancellation _________
<div class="office">
Reg____ Refund Amt_________ Date Sent___________ DB____
</div>
</cfmail>

<cfmail to="#mailfrom#" 
	from="#adm_email#"
	subject="North House Folk School Registration"
	query="GetOrder">

Thanks for your registration with North House Folk School!  Please anticipate hearing from North House in the 
coming weeks to confirm your registration for the following courses. Please note that we're transitioning to a new internal 
database system, and it will take a little time for us to get the mailed confirmation packet process back on track. In the 
meantime, required tools information can be found on individual course pages on the website, and don't hesitate to call us 
with any questions about your tuition balance, or anything else. Thanks for your patience with us and technology!

We have you signed up for:
<cfloop query="GetItems">
#name# on #DateFormat(startdt, "mm/dd/yyyy")# for<cfquery name="GetStudents" datasource="#dsn#">
			select * from reg_students where rid=#form.rid# and
				cid=#cid# and startdt=#startdt# order by sid
		</cfquery><cfloop query="getStudents">
	- #name#</cfloop>
</cfloop>
Mailing Address: 
   #GetCust.ShipAddr1#<cfif GetCust.ShipAddr2 GT "">, #GetCust.ShipAddr2#</cfif>
   #GetCust.ShipCity#, #GetCust.ShipState# Zip #GetCust.ShipZip#<cfif GetCust.ShipCountry EQ "CAN"> Canada</cfif>
<cfif GetCust.ShipPhone GT "">
Home Phone: #GetCust.ShipPhone#</cfif><cfif GetCust.WorkPhone GT "">
Work Phone: #GetCust.Workphone#</cfif>

Sincerely, 
North House Folk School
</cfmail>

<cfif getDon.recordcount GT 0>
<cfmail from="#adm_email#" to="#mailfrom#" subject="Contribution to North House">
Thank you for your contribution to the North House Folk School!  Please anticipate hearing from North House
in the next week to confirm your contribution of #DollarFormat(getdon.donation)#.

Sincerely,
North House Folk School
</cfmail>
</cfif>

<cfif getCust.newsletter eq true>
	<!---cfmail to="majordomo@northhouse.org"
		from="#getCust.email#"
		subject="">
	subscribe news <cfoutput>#getCust.email#</cfoutput>
	</cfmail--->
	<cfset rname=reverse(getCust.billname)>
	<cfset space=find(" ", rname)>
	<cfif space gt 0>
		<cfset ln=left(rname, space-1)>
		<cfset fn=right(rname, len(rname)-space)>
		<cfset ln=reverse(ln)><cfset fn=reverse(fn)>
	<cfelse>
		<cfset ln=getCust.billname>
		<cfset fn="">
	</cfif>
	<cfhttp url="http://oi.vresp.com/index.html" method="POST" result="nlresult">
		<cfhttpparam type="FORMFIELD" name="fid" value="26ad0f66b8">
		<cfhttpparam type="FORMFIELD" name="email_address" value="#getCust.email#">
		<cfhttpparam type="FORMFIELD" name="first_name" value="#fn#">
		<cfhttpparam type="FORMFIELD" name="last_name" value="#ln#">
		<cfhttpparam type="FORMFIELD" name="General" value="1">
	</cfhttp>
</cfif>

<cfset session.cart=ArrayNew(1)>
<BR/>
<h2>Your Registration has been Sent</h2>
<p>
Thank you for your registration!  Please print this page for your records.
Please do not use your browser's back button to move off this page,
or you may end up registering again.
</p>
<cfif getCust.newsletter eq true>
<p>You have been signed up for North House's general newsletter. Want to sign up for news about a 
specific coursework theme? <a href="<cfoutput>#site_url#</cfoutput>../../enewsletter.htm">Click here.</a>
</p><p>
(Please note: A newsletter confirmation will be sent to 
your address. In order to complete the newsletter sign-up process, you will need to verify your email address 
by clicking on the link sent to you in that email.)</p>
</cfif>
<div class="orderform">
<div class="title">Student Information</div>

<cfoutput query="GetCust">
#ShipAddr1#<BR>
<cfif ShipAddr2 GT "">#ShipAddr2#<BR></cfif>
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
	<cfloop query="GetItems">
		<tr>
		<td align="right">#students#</td>
		<td align="right">#children#</td>
		<td>#name#</td>
		<td>#DateFormat(startdt, "mm/dd/yyyy")#</td>
		<cfquery name="GetStudents" datasource="#dsn#">
			select * from reg_students where rid=#form.rid# and
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
	<cfif getDon.recordcount GT 0>
		<tr>
			<td colspan="7">Your membership donation - thank you!</td>
			<td align="right">#dollarformat(getdon.donation)#</td>
		</tr>
		<cfset totde=totde+getDon.donation>
	</cfif>
	<tr class="carttot">
		<td colspan="5">Totals</td>
		<td align="right">#dollarformat(tottu)#</td>
		<td align="right">#dollarformat(totma)#</td>
		<td align="right">#dollarformat(totde)#</td>
	</tr>
	<tr class="carttot">
		<cfif getorder.gcdeposit gt 0>
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

<cfif getOrder.ccdeposit GT 0 or getOrder.gcdeposit GT 0>
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
<br/><br/>
</cfif>
<b>If you need confirmation letters to go to different addresses, please contact us at <cfoutput>#co_phone#</cfoutput></b>
</div>

<input type="button" value="Plan Your Stay" onclick="location.href='../../planyourstay.htm';">
<P>
<A HREF="coursesbydate.cfm">Return to course listing</A>
</P>
<cfinclude template="Templates/footer.cfm">

