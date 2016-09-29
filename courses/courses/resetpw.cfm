<cfset masthead="kayaks">
<cfinclude template="Templates/header.cfm">
<cfif isDefined("Form.email")>
<!--- generate temporary password --->
	<cfset err=false>
	<cfset msg="">
<!--- first, check old password --->
	<cfquery name="checkpass" datasource="#dsn#">
		Select email from Customers
			Where Email = <cfqueryparam cfsqltype="CF_SQL_CHAR" value="#form.email#">
	</cfquery>
	<cfif checkpass.recordcount eq 0>
		<cfset err=true>
		<cfset msg="Sorry, this email address was not found in the system">
	<cfelse>
		<cfset newpass="">
		<cfset letters="a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,x,1,2,3,4,5,6,7,8,9,0,!,@,+,$,%">
		<cfloop index="x" from="1" to="8">
			<cfset newpass=newpass & listGetAt(letters, randrange(1,30))>
		</cfloop>
		<cfquery name="setpass" datasource="#dsn#">
			update customers set password='#newpass#' where email='#form.email#'
		</cfquery>
		<cfmail from="#adm_email#" to="#form.email#" subject="North House Password Reset">
			Thank you for your interest in #co_name#.  Your password has been reset to:
			#newpass#
			
			You can log in using this password or can reset it at #co_URL#/schoolstore/changepw.cfm.
			
			Sincerely,
			#co_name#
		</cfmail>
		<cfset msg="Thank you!  Your password has been reset and an email has been sent to your address.">
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
</cfif>

<cfform action="resetpw.cfm" method="POST" scriptsrc="http://kite.boreal.org/cfide/scripts" class="cp">
<label for="email">Your email address</label>
<cfinput type="text" name="email" id="email" required="yes" message="Please enter your email address" value="#form.email#">
<br/>
<label>&nbsp;</label><input type="submit" value="Reset Password" class="sub"/>
<br/><br/>
</cfform>
</cfif>

<br/>
<A HREF="checkout.cfm">Back to checkout</A>

<cfinclude template="Templates/footer.cfm">
