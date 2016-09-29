<cfset masthead="kayaks">
<cfinclude template="Templates/topper.cfm">
<cfif isDefined("Form.newpass")>
<!--- update password --->
	<cfset err=false>
	<cfset msg="">
<!--- first, check old password --->
	<cfquery name="checkpass" datasource="#dsn#">
		Select Password from Customers
			Where Email = '#Form.email#'
	</cfquery>
	<cfif checkpass.recordcount eq 0>
		<cfset err=true>
		<cfset msg="Sorry, this email address was not found in the system">
	<cfelseif checkpass.password neq form.oldpass>
		<cfset err=true>
		<cfset msg="The password on file does not match the password you provided for this email address.">
	<cfelseif form.newpass neq form.newpass2 or form.newpass eq "">
		<cfset err=true>
		<cfset msg="The new passwords do not match.">
	<cfelse>
		<cfquery name="setpass" datasource="#dsn#">
			update customers set password='#form.newpass#' where email='#form.email#'
		</cfquery>
		<cfset msg="Thank you!  Your password has been updated.">
	</cfif>
<cfelse>
	<cfset err=true>
	<cfset msg="">
</cfif>

<style type="text/css">
.cp label {
	float: left;
	width: 150px;
}

.cp input {
	float: left;
	width: 300px;
}

input.sub {
	width: auto;
}

form br {
	clear: both;
}
.msg {
	color: #b00;
	font-weight: bold;
}
</style>

<cfif isdefined("msg") and msg gt "">
	<cfoutput>
	<div class="msg">
	<cfif err>An error has occurred: </cfif>#msg#
	</div>
	</cfoutput>
</cfif>

<cfif err>

<cfif not isdefined("form.email")>
	<cfset form.email="">
	<cfset form.oldpass="">
	<cfset form.newpass="">
	<cfset form.newpass2="">
</cfif>
<p><b>
Notice: In order to protect your information, we have reset all passwords that were created before July 22, 2013. If you 
set up your account prior to this date and have not reset your password since then, please <a href="resetpw.cfm">click here </a>
to reset your password instead of using the form below.
</b></p>

<cfform action="changepw.cfm" method="POST" scriptsrc="http://kite.boreal.org/cfide/scripts" class="cp">
<label for="email">Email address</label>
<cfinput type="text" name="email" id="email" required="yes" message="Please enter your email address" value="#form.email#">
<br/>
<label for="oldpass">Current password</label>
<cfinput type="password" name="oldpass" id="oldpass" required="yes" message="Please enter your current password" value="#form.oldpass#">
<br/>
<label for="newpass">New password</label>
<cfinput type="password" name="newpass" id="newpass" required="yes" message="Please enter your new password" value="#form.newpass#">
<br/>
<label for="newpass2">Retype the new password</label>
<cfinput type="password" name="newpass2" id="newpass2" required="yes" message="Please re-enter your new password"  value="#form.newpass2#">
<br>
<label>&nbsp;</label><input type="submit" value="Update Password" class="sub"/>
<br/><br/>
</cfform>
</cfif>

<br/>
<A HREF="checkout.cfm">Back to checkout</A>

<cfinclude template="Templates/bottom.cfm">