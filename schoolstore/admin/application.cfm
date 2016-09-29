<cfapplication name="cartadminnh" sessionmanagement="yes">
<cfset dsn="northhouse">
<cfset fileloc="f:\northhouse.org\schoolstore\">

<cfset ordering_code="o">
<cfset guarantee_code="g">
<cfset return_code="r">
<cfset delivery_code="d">
<cfset shipping_code="s">
<cfset privacy_code="p">
<cfset security_code="c">
<cfset gift_code="x">

<cfset staff_month=1>
<cfset inst_month=2>
<cfset tool_month=3>
<cfset monthwid=250>
<cfset catwid=145>
<cfset fullcatwid=525>
<cfset mainwid=390>

<cflogin>
	<cfif not IsDefined("cflogin")>
		<!--- display login screen ---->
		<cfinclude template="login.cfm">
	<cfelse>
		<cfquery datasource="#dsn#" name="GetPW">
			select * from users where
				Username='#cflogin.name#'
		</cfquery>
		<cfif GetPW.RecordCount eq 0>
			<font color="#FFFFFF"><B>Invalid user name / password.  Please try again.</B></font>
			<cfinclude template="login.cfm">
		<cfelse>
			<cf_encrypter SourceNum="#GetPW.password#" DCode=true>
			<cfif CodedNum neq cflogin.password>
				<font color="#FFFFFF"><B>Invalid user name / password.  Please try again.</B></font>
				<cfinclude template="login.cfm">
			<cfelse>
				<cfloginuser name="#cflogin.name#" Password = "#cflogin.password#"
					roles="#GetPW.Access#">
			</cfif>
		</cfif>
	</cfif>
</cflogin>
