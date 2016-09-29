<cfapplication name="cartadminnh" sessionmanagement="yes">
<cfset dsn="northhouse">
<cfset fileloc="F:\northhouse.org\courses\courses\">
<cfset courseurl="http://www.northhouse.org/courses/courses/">
<cfset conf=structNew()>
<cfset conf.CustomConfigurationsPath="/schoolstore/admin/editorconf.js">
<cfset maxwid=250>
<cfset logowid=150>
<cfset logoht=100>

<cfset levels=ArrayNew(1)>
<cfset arrayAppend(levels, "Basic")>
<cfset arrayAppend(levels, "Intermediate")>
<cfset arrayAppend(levels, "Advanced")>
<cfset arrayAppend(levels, "Sponsor")>
<cfset arrayAppend(levels, "Major Sponsor")>
<cfset arrayAppend(levels, "Foundation Support")>


<cfset part_pid="pa">


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
