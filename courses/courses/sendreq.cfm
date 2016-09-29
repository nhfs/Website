<cfif not isDefined("form.realname")>
	<cflocation url="http://www.northhouse.org" addtoken="No">
</cfif>

<cfinclude template="templates/header.cfm">
<cfset err=false>
<cfset msg="">
<cfif form.realname eq "">
	<cfset err=true>
	<cfset msg=msg & "Please enter your name.<br/>">
</cfif>
<cfif form.address eq "">
	<cfset err=true>
	<cfset msg=msg & "Please enter your address.<br/>">
</cfif>
<cfif form.city eq "">
	<cfset err=true>
	<cfset msg=msg & "Please enter your city.<br/>">
</cfif>
<cfif form.state eq "">
	<cfset err=true>
	<cfset msg=msg & "Please enter your state.<br/>">
</cfif>
<cfif form.zip eq "">
	<cfset err=true>
	<cfset msg=msg & "Please enter your zip code.<br/>">
</cfif>

<cfif form.email eq "" and isDefined("form.newsletter")>
	<cfset err=true>
	<cfset msg=msg & "Please enter your email address.<br/>">
</cfif>

<cfif err>
	<div class="err">Oops!  Some errors have occurred.  Please use your back button to go back and correct the 
	following issues:<br/> <cfoutput>#msg#</cfoutput></div>
<cfelse>
	<cfmail from="#adm_email#" to="#cat_email#" subject="Catalog Request">
You have received a request for a catalog from:

Name: #form.realname#
Address: #form.address#
         #form.city#, #form.state# #form.zip#<cfif form.phone GT "">
Phone: #form.phone#</cfif>
<cfif form.email GT "">
Email address: #form.email#
</cfif><cfif form.how GT "">
How did you hear about North House?
#form.how#</cfif>
	</cfmail>

	<cfif isDefined("form.newsletter")>
		<cfset rname=reverse(form.realname)>
		<cfset space=find(" ", rname)>
		<cfset ln=left(rname, space-1)>
		<cfset fn=right(rname, len(rname)-space)>
		<cfset ln=reverse(ln)><cfset fn=reverse(fn)>
		<cfhttp url="http://oi.vresp.com/index.html" method="POST" result="nlresult">
			<cfhttpparam type="FORMFIELD" name="fid" value="26ad0f66b8">
			<cfhttpparam type="FORMFIELD" name="email_address" value="#form.email#">
			<cfhttpparam type="FORMFIELD" name="first_name" value="#fn#">
			<cfhttpparam type="FORMFIELD" name="last_name" value="#ln#">
			<cfhttpparam type="FORMFIELD" name="General" value="1">
		</cfhttp>
	</cfif>
	<cflocation url="http://www.northhouse.org/thanks.htm" addtoken="no">

</cfif>

<cfinclude template="Templates/footer.cfm">