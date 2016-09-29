<cfif not IsDefined("form.did")>
	<cflocation url="coursesbydate.cfm">
</cfif>
<cfquery name="GetDon" datasource="#dsn#">
	select * from donations where did=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#form.did#">
</cfquery>

<cfif GetDon.email GT "">
	<cf_emailVerify email="#GetDon.email#">
	<cfif not valid>
		<cfset from=adm_email>
	<cfelse>
		<cfset from=GetDon.email>
	</cfif>
<cfelse>
	<cfset from=adm_email>
</cfif>

<cf_encrypter SourceNum="#GetDon.ccnbr#" dcode=true>
<cfif EncryptError>
	<Cfoutput>ERROR! #Message#</CFOUTPUT>
	<cfabort>
</cfif>


<cfmail from="#from#" to="#don_email#" subject="Contribution to North House" query="GetDon">
The following contribution has been submitted:

Name: #name#
Address:
	#Address#
	#City#, #state# #zip#

<cfif Phone GT "">Phone: #phone#
</cfif><cfif email GT "">Email: #email#</cfif><cfif type eq "onceg" or type eq "oncem" or type eq "oncec">

This is a <cfif type eq "onceg">gift<cfelseif type eq "oncem">memorial<cfelse>commemorative gift</cfif> for #name2#<cfif type eq "onceg"> at
#addr2#
#city2#, #state2# #zip2#<cfelse>

Send thank you letter to:
#name3#
#addr2#
#city2#, #state2# #zip2#</cfif></cfif>

Contribution: #Dollarformat(donation)#<cfif type eq "schd"> per month, for a total of #dollarformat(donation*12)#</cfif>

Credit Card information:
	Number: #codednum# Expiration: #CCMM#/#CCYY#
	CVD Code: #CVD#
	  
<cfif maillist eq true>
subscribe to email newsletter
</cfif>
</cfmail>

<cfmail from="#adm_email#" to="#from#" subject="Contribution to North House" query="getDon">
Thank you for your contribution to the North House Folk School!  Please anticipate hearing from North House
in the next week to confirm your contribution of #DollarFormat(donation)#<cfif type eq "schd"> per month</cfif>.

Sincerely,
North House Folk School
</cfmail>

<cfinclude template="Templates/header.cfm">
<h2>Thank you!</h2>
Thank you for contributing to the North House Folk School!
<cfoutput query="getDon">
<BR><BR>
<div class="receipt">
<b>North House Folk School Contribution</b>
<BR><BR>
<div class="col2">Contribution amount:</div>
<div class="col2">#Dollarformat(donation)# <cfif type eq "schd"> per month, for a total of <b>#dollarformat(donation*12)#</b></cfif></div>
<cfif type eq "schd">
<br clear="all"/>This membership will auotmatically renew at the end of the year.  If at any time you want to change your
contribution schedule or amount, please contact North House.
</cfif>
<br><br/>
Charged to <b><cfif left(codednum,1) eq 4>Visa<cfelseif left(codednum,1) EQ 5>Mastercard<cfelseif left(codednum,1) EQ 6>Discover<cfelse>American Express</cfif> ending in <b>#right(codednum,4)#</b>,
Expiration date #CCMM#/#CCYY#
<BR>
</div>
<BR>

<a href="http://www.northhouse.org">Return to North House Home Page</a>
</cfoutput>
<cfinclude template="Templates/footer.cfm">
