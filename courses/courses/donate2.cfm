<cfinclude template="templates/header.cfm">
<cfif isDefined("form.ccnbr")>
<cfif Form.CCNbr GT "">
	<cf_encrypter SourceNum="#Form.CCNbr#">
	<cfif EncryptError>
		<Cfoutput>ERROR! #Message#</CFOUTPUT>
		<cfabort>
	</cfif>
<cfelse>
	<cfset CodedNum="">
</cfif>

<cfif left(form.donation, 1) eq "m">
	<cfset form.donation=right(form.donation, len(form.donation)-1)>
	<cfset type="schd">
<cfelse>
	<cfif isdefined("form.giftname") >
		<cfset type="onceg">
	<cfelseif isdefined("form.memname")>
		<cfif form.memname GT "">
			<cfset type="oncem">
		<cfelse>
			<cfset type="oncec">
		</cfif>
	<cfelse>
		<cfset type="once">
	</cfif>
</cfif>
<cfif form.donation eq "other">
	<cfset form.donation = form.donother>
</cfif>

<cfquery name="saveDonation" datasource="#dsn#">
	insert into donations (name, address, city, state, zip, phone, email, maillist, ccnbr, ccmm, ccyy, cvd, 
		donation, type, autorenew
		<cfif isDefined("form.giftname") or isDefined("Form.memname")>
			, name2, addr2, city2, state2, zip2
			<cfif isDefined("form.memname")>
			, name3
			</cfif>
		</cfif>
		)
		values ('#form.name#', '#form.address#', '#form.city#', '#form.state#', '#form.zip#',
			'#form.phone#', '#form.email#'
			<cfif isDefined("Form.maillist")>
				,true
			<cfelse>
				,false
			</cfif>
			, '#codednum#', #form.ccmm#, #form.ccyy#, '#form.cvd#', #form.donation#, '#type#'
			<cfif type eq "schd">
				, true
			<cfelse>
				, false
			</cfif>
		<cfif isDefined("form.giftname")>
			, '#form.giftname#', '#form.giftaddress#', '#form.giftcity#', '#form.giftstate#', 
				'#form.giftzip#'
		</cfif>
		<cfif isDefined("form.memname")>
			<cfif form.memname gt "">
			, '#form.memname#'
			<cfelse>
			, '#form.cmemname#'
			</cfif>
			, '#form.thankaddress#', '#form.thankcity#', '#form.thankstate#', 
				'#form.thankzip#', '#form.thankname#'
		</cfif>
			)
</cfquery>

<cfelse>

<cfif not IsDefined("url.don") or not Isdefined("url.c") or not isDefined("url.n")>
	<cflocation url="http://www.northhouse.org">
</cfif>
<cfquery name="GetDid" datasource="#dsn#">
	select max(did) as maxid from donations where <cfif url.don neq "other"><cfif left(url.don,1) eq "m">donation=#right(url.don,len(url.don)-1)# and <cfelse>donation=#url.don# and </cfif></cfif>cvd=#url.c# and name='#URLdecode(url.n)#'
</cfquery>
<cfquery name="GetDon" datasource="#dsn#">
	select * from donations where did=#GetDid.maxid#
</cfquery>
<cf_encrypter SourceNum="#GetDon.ccnbr#" dcode=true>
<cfif EncryptError>
	<Cfoutput>ERROR! #Message#</CFOUTPUT>
	<cfabort>
</cfif>

<h2>Confirm your Contribution</h2>
Please verify that the information below is correct, and then click the Finish button to 
charge your credit card.  If you find an error, please go back and correct the information.
<BR><BR>
<cfoutput query="GetDon">

<b>Billing information:</b>
<div class="indent">
#name#<BR>
#address#<BR>
#city#, #State# #Zip#
<BR><BR>
<cfif phone GT "">#phone#<BR></cfif>
#Email#<BR>
<cfif Maillist eq true>
	<BR>Requested subscription to email newsletter
</cfif>
<cfif type eq "onceg" or type eq "oncem" or type eq "oncec">
<br><br>
<b>This is a <cfif type eq "onceg">gift<cfelseif type eq "oncem">memorial<cfelse>commemorative gift</cfif> for:</b><br>
#name2#
<cfif type eq "onceg">
<br>
#addr2#<br>
#city2#, #state2# #zip2#
<cfelseif type eq "oncem" or type eq "oncec">
<br><br>
<b>Send a thank you letter to:</b><br>
#name3#<br>
#addr2#<br>
#city2#, #state2# #zip2#
</cfif>
</cfif>

</div>

<form action="#site_url#donate3.cfm" method="POST">
	<input type="hidden" name="did" value="#did#">
<BR>Please charge <b>#dollarFormat(donation)#</b> to my <cfif left(codednum,1) eq 4>Visa<cfelseif left(codednum, 1) EQ 5>Mastercard<cfelseif left(codednum,1) EQ 6>Discover<cfelse>American Express</cfif> ending in <b>#right(codednum,4)#</b>.
<cfif type eq "once">This is for my annual membership.<cfelseif type eq "oncem" or type eq "oncec">

<cfelse>
	This amount will be charged once a month for a total donation of <b>#dollarformat(donation*12)#</b> per year.
</cfif>
	<br/>
	<input type="submit" value="Finish">
</form>
</cfoutput>
</cfif>
<cfinclude template="templates/footer.cfm">