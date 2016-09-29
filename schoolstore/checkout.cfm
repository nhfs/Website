<cfset masthead="kayaks">
<cfinclude template="Templates/topper.cfm">


<cfif isdefined("Form.NewEmail")>
<!--- creating a new account.  Verify info, save it, and move on --->
	<cf_EmailVerify EMAIL="#FORM.NewEmail#">
	<cfif not valid>
		<cfset NEErr="This isn't a valid email address. Please re-enter.">
	<cfelse>
		<cfquery datasource="#dsn#" name="CheckEmail">
			select Email from Customers Where Email='#Form.NewEmail#' and Password > ''
		</cfquery>
		<cfif CheckEmail.RecordCount gt 0>
			<cfset NEErr="Sorry, but this email address is already in our system. You may already have an account - try logging in.  If not, you will need to use a different email address.">
		<cfelseif Form.NewPass1 NEQ Form.NewPass2>
			<cfset NPWErr="Passwords do not match.  Please re-enter your password.">
		<cfelseif Trim(Form.NewPass1) eq "" or Trim(Form.NewPass2) EQ "">
			<cfset NPWErr="Password is missing.  Please enter the same password in both fields.">
		<cfelse>
			<cfquery name="AddCust" datasource="#dsn#">
				insert into Customers
					(Email, Password)
					Values ('#Form.NewEmail#','#Form.NewPass1#')
			</cfquery>
			<cflocation url="checkout1.cfm?E=#Form.NewEmail#">
		</cfif>
	</cfif>
</cfif>

<cfif IsDefined("Form.CurrEmail")>
	<cfif Form.CurrPass LE "">
		<cfset CPErr="You must enter a password.">
	<cfelse>
		<cfquery datasource="#dsn#" name="GetCust">
			select CustID from Customers 
				WHERE Email='#Form.CurrEmail#' and
					Password='#Form.CurrPass#'
		</cfquery>
		<cfif GetCust.RecordCount eq 0>
			<cfset CEErr="Sorry, we don't have an account with this e-mail address and password. Please try again, or click on 'Get Password' to have your password emailed to you.">
		<cfelse>
			<cflocation url="checkout1.cfm?E=#Form.CurrEmail#">
		</cfif>
	</cfif>
</cfif>
<BR>
<div class="data wide">
<DIV ALIGN=CENTER>
<H2>Check-Out</H2><BR>
<FONT SIZE="+1"><B>Welcome to <cfoutput>#co_name#</cfoutput>'s Checkout System!</B></FONT></DIV>
<P>

<cfif ArrayLen(session.Cart) LT 1>
	<DIV ALIGN="Center">
	<HR width="160" >
	<b><FONT SIZE="+1">Your cart is empty.</FONT></b>
	<HR width="160" ><P>
	<A HREF="index.cfm">Back to the catalog</A>
	</P></DIV>
	<cfabort>
</cfif>

You may start placing your order from this page, or you may create an account.  If
you create an account, we'll remember your address information and you 
won't have to put it in again next time place an order.
<P>
</P>

<table width="90%" cellspacing="3" cellpadding="3" align="CENTER">
<TR>
<TD colspan=5 align="Center" class="tier1"><B><FONT SIZE="+1">Please choose</FONT></B></TD>
</TR>
<TR>
<TD width="33%" class="tier2"><B>Create an account</B></TD>
<TD class="tier3" width="1%"></TD>
<td width="32%" class="tier2"><B>Log into an existing account</B></td>
<TD class="tier3" width="1%"></TD>
<td width="33%" class="tier2"><B>Place order without creating an account</B></td>
</TR>
<TR>
<TD ALIGN=CENTER>
<cfform action="checkout.cfm" method="POST" name="newmem">
<cfoutput>
<cfif isdefined("NEErr")><FONT SIZE="+1" color="##BB0000"><B>#NEErr#</B></FONT><BR></cfif>
Email Address: 

<input type="text" name="NewEmail" size="25" <cfif IsDefined("Form.NewEmail")>value="#Form.NewEmail#"</cfif>><BR><BR>
<cfif isdefined("NPWErr")><FONT SIZE="+1" color="##BB0000"><B>#NPWErr#</B></FONT><BR></cfif>
<cfif isdefined("Form.NewPass1")><cfset val=form.NewPass1><cfelse><cfset val=""></cfif>
Choose a password: <cfInput type="password" name="NewPass1" size="25" value="#val#" maxlength="10" message="Your password must be 10 characters or less.  Please pick a shorter password."><BR><BR>
Confirm your password: <Input type="password" name="NewPass2" size="25" <cfif isdefined("Form.NewPass2")>value="#Form.NewPass2#"</cfif>><BR><BR>
</cfoutput>
<input type="Submit" value="Create Account"></cfform>
</TD>

<TD class="tier2"></TD>
<TD ALIGN=CENTER>
<form action="checkout.cfm" method="POST" name="signin">
<cfoutput>
<cfif IsDefined("CEErr")><FONT SIZE="+1" color="##BB0000"><B>#CEErr#</B></FONT><BR></cfif>
Email Adress: <input type="text" name="CurrEmail" size="25" <cfif isdefined("Form.CurrEmail")>value="#Form.CurrEmail#"</cfif>><BR><BR>
<cfif IsDefined("CPErr")><FONT SIZE="+1" color="##BB0000"><B>#CPErr#</B></FONT><BR></cfif>
Password: <input type="password" name="CurrPass" size="25" <cfif IsDefined("Form.CurrPass")>value="#form.CurrPass#"</cfif>><BR><BR>
<input type="Submit" value="Sign In"><BR><BR></cfoutput>
</FORM>
<Form action="changepw.cfm" method="POST" name="Changepw">
<Input type="submit" value="Change Password" onClick="Getpw.CurrEmail.value=signin.CurrEmail.value;">
</form>

Can't remember your password? Click here:
<Form action="Getpw.cfm" method="POST" name="Getpw">
<input type="hidden" name="CurrEmail">
<Input type="submit" value="Get Password" onClick="Getpw.CurrEmail.value=signin.CurrEmail.value;">
</form>
</TD>
<td class="tier2"></td>
<td align="Center">
<form action="checkout1.cfm" method="post">
<input type="submit" value="Start Order">
</form>
</td>
</TR>
</table>
<p style="font-weight: bold; padding: 10px 30px">Notice:  In order to protect your information, we have reset all passwords that were created before July 22, 2013.  If you 
set up your account prior to this date, please reset your password using <a href="resetpw.cfm">this form. (click here)</a></p>

</div>

<cfinclude template="Templates/bottom.cfm">
