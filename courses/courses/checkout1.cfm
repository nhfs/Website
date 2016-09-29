<cfinclude template="Templates/header.cfm">
<script language="JavaScript">
<!--
<cfif isDefined("URL.ErrCd")>
	alert("Sorry, you changed your email address to one that's being used by another registered member of our system.  Please specify a different email address.");
</cfif>
//-->

function checkform(f) {
	err=false;
	msg="";
	if (f.donation[8].checked) {
		if (f.donother.value == "" || isNaN(f.donother.value)) {
			msg+="Please enter the amount of your donation as a number.\n";
			err=true;
			f.donother.focus();
		}
	}
	
	if (err) {
		alert (msg);
		return false;
	} else {
		return true;
	}
	
}

</script>
<BR>
<H2>Step 1 - Contact Information</H2>
<p>
Please fill out the following information to register for your selected courses.  Your registration
will not be completed and your credit card will not be charged until you confirm the order on the final 
screen of the checkout system. Required fields are marked with a *.
</p>
<cfif ArrayLen(session.Cart) LT 1>
	<DIV ALIGN="Center">
	<HR width="160" >
	<b><FONT SIZE="+1">You haven't selected any courses.</FONT></b>
	<HR width="160" ><P>
	<A HREF="coursesbydate.cfm">Back to the course list.</A>
	</P></DIV>
	<cfabort>
</cfif>

<cfif isdefined("URL.E")>
	<cfquery datasource="#dsn#" name="GetCust">
		Select * from Customers
		where Email = '#URL.E#' and
			Password > ''
	</cfquery>
	<cfif GetCust.RecordCount GT 0>
		<cfset session.Upd = True>
	</cfif>
<cfelse>
	<cfset session.Upd=False>
</cfif>

<cfquery datasource="#dsn#" name="GetStates">
	Select * from States
</cfquery>

<cfform name="PersInfo" action="checkout2.cfm" method="POST" scriptSrc="http://kite.boreal.org/CFIDE/scripts" onsubmit="return checkform(_CF_this)">
<fieldset><legend>Student Information</legend>
<cfoutput>
<cfif session.Upd>
	<input type="HIDDEN" name="CustID" value="#GetCust.CustID#">
</cfif>
<label for="shipaddr1">Address Line 1*:</label>
<cfif isdefined("GetCust.ShipAddr1")><cfset val=GetCust.Shipaddr1><cfelse><cfset val=""></cfif>
<cfinput type="text" name="ShipAddr1" id="shipaddr1" required="yes" message="Please enter your mailing address" value="#val#">
<br/>
<label for="shipaddr2">Address Line 2:</label>
<input type="text" name="ShipAddr2" id="shipaddr2" <cfif isDefined("GetCust.ShipAddr2")>value="#GetCust.ShipAddr2#"</cfif>>
<br/>
<label for="shipcity">City*:</label>
<cfif isdefined("GetCust.ShipCity")><cfset val=GetCust.shipcity><cfelse><Cfset val=""></cfif>
<cfinput class="col2" type="text" name="ShipCity" id="shipcity" required="yes" message="Please enter your city" value="#val#">


<label for="shipstate" class="col2">State / Province*:</label>
<cfif isdefined("GetCust.ShipState")><cfset val=GetCust.shipstate><cfelse><cfset val=""></cfif>
<cfselect class="col2" name="ShipState" id="shipstate" required="yes" message="Please select your state or province"
		query="GetStates" value="State" selected="#val#"></cfselect>
<br/>
<label for="shipzip">Zip / Postal Code*:</label>
<cfif IsDefined("GetCust.ShipZip")><cfset val=GetCust.shipzip><cfelse><cfset val=""></cfif>
<cfinput class="col2" type="text" name="ShipZip" id="shipzip" required="yes" value="#val#" message="Please enter your zip code or postal code">

<label for="shipcountry" class="col2">Country:</label>
<cfselect name="ShipCountry" id="shipcountry" class="col2" required="yes" message="Please enter your state">
	<OPTION value="USA" <Cfif isdefined("GetCust.ShipCountry") and GetCust.ShipCountry EQ "USA">selected</CFIF>>United States
	<OPTION value="CAN" <Cfif isdefined("GetCust.ShipCountry") and GetCust.ShipCountry EQ "CAN">selected</CFIF>>Canada
</cfselect>
<br/>
<label for="shipphone">Home Phone*:</label>
<cfif isdefined("GetCust.ShipPhone")><cfset val=GETCust.shipphone><cfelse><cfset val=""></cfif>
<cfinput type="text" name="ShipPhone" id="shipphone" value="#val#" required="Yes" validate="telephone" message="Please enter your home phone number in the format nnn-nnn-nnnn">
<br/>
<label for="workphone">Work /Cell Phone:</label>
<input type="text" name="workphone" id="workphone" <cfif isdefined("GetCust.workPhone")>value="#GetCust.workPhone#"</cfif>>
<br/>
<label for="email">E-Mail Address*:</label>
<cfif isdefined("GetCust.Email")><cfset val=GetCust.Email><cfelse><cfset val=""></cfif>
<cfinput type="text" id="email" name="Email" value="#val#" required="yes" message="Please enter your email address">

<br/><br/>
<label for="howheard">How did you learn about North House?</label>
<textarea name="howheard" id="howheard" cols="50" rows="3"></textarea>
<br/ clear="all">
Have you registered for a North House course before?<br/>
<input type="radio" class="radio" name="formerstudent" value="true">yes
<br clear="all"/>
<input type="radio" class="radio" name="formerstudent" value="false">no<br/>
<br/ clear="all">
Would you like to receive our email newsletter? (If you are already subscribed, this will have no effect on your subscription)<br/>
<input type="radio" class="radio" name="newsletter" value="true" checked>yes
<br clear="all"/>
<input type="radio" class="radio" name="newsletter" value="false">no<br/>
<br/ clear="all">
Would you like to become a North House member?  <a href="##" onclick="node=returnObjById('learnmore'); node.style.display='block'; return false;">Learn more</a><br/>
<input type="radio" class="radio" name="member" value="true">yes
<br clear="all"/>
<input type="radio" class="radio" name="member" value="false">no<br clear="all"/>
<input type="radio" class="radio" name="member" value="false">I'm already a member<br/>
<div id="learnmore">
<a href="##" onclick="node=this.parentNode; node.style.display='none'; return false;");>Close this window</a>
<p>
We invite you to join North House's community of supporters! Your contribution will help create a solid 
foundation for the school's evolving &amp; expanding efforts. North House is a 501-c-3 nonprofit - your 
contribution is tax deductible!
</p><p>
Membership benefits include:
<ul>
<li>Receive each of North House's two annual course catalogs</li>
<li>Get our newsletter Shavings, the North House Annual Report, and special announcements &amp; invitations</li>
<li>Automatically qualify for an "event pass" during each of North House's special events weekends (donors at 
or above the $50 level qualify for passes for all family members) </li>
</ul>
</p>
<a href="##" onclick="node=this.parentNode; node.style.display='none'; return false;");>Close this window</a>
</div>
</fieldset>
<br/>
<fieldset id="donate"><legend>Annual Membership / Regular Contribution Options</legend>
<p><b>Thank you!</b></p>
<p>* regular contributions are received and acknowledged as a one-time gift and will be initiated within 
one week of gift's submission.</p>
<b>Support Levels - Select One:</b><br/>
	<input type="radio" name="donation" id="donation0" value="2000" class="radio"><label for="donation0" class="radio">$2000.00 - Friend</label><BR clear="all"/>
	<input type="radio" name="donation" id="donation1" value="1000" class="radio"><label for="donation1" class="radio">$1000.00 - Patron</label><BR clear="all"/>
	<input type="radio" name="donation" id="donation2" value="500" class="radio"><label for="donation2" class="radio">$500.00 - Pathfinder</label><BR clear="all"/>
	<input type="radio" name="donation" id="donation3" value="250" class="radio"><label for="donation3" class="radio">$250.00 - Sponsor</label><BR clear="all"/>
	<input type="radio" name="donation" id="donation4" value="100" class="radio"><label for="donation4" class="radio">$100.00 - Leader</label><BR clear="all"/>
	<input type="radio" name="donation" id="donation5" value="75" class="radio"><label for="donation5" class="radio">$75.00 - Supporter</label><BR clear="all"/>
	<input type="radio" name="donation" id="donation6" value="50" class="radio"><label for="donation6" class="radio">$50.00 - Donor</label><BR clear="all"/>
	<input type="radio" name="donation" id="donation7" value="25" class="radio"><label for="donation7" class="radio">$25.00 - Basic</label><BR clear="all"/>
	<input type="radio" name="donation" id="donation8" value="other" class="radio"><label for="donation8" class="radio">other</label>
	<label for="donother" class="col2a">Amount:</label><input name="donother" id="donother" class="col2">
	<BR clear="all"/>
</fieldset>
<br/>
<fieldset><legend>Enrollment Information</legend>
<cfloop index="idx" from="1" to="#arraylen(session.cart)#">
<b>#session.cart[idx].name# - #Dateformat(session.cart[idx].startdt, 'mm/dd/yyyy')#</b><br/>
<div class="indent">
	<cfloop index="y" from="1" to="#session.cart[idx].students#">
		<cfif session.cart[idx].intergen and session.cart[idx].children GT 0>
			<cfif y eq 1><b>Adults</b><br/>
			<cfelseif y eq session.cart[idx].students-session.cart[idx].children+1><br/><b>Under 18</b><br/></cfif>
		</cfif>
		<label for="student#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#">Student Name*:</label>
		<cfif IsDefined("GetCust.Shipname") and getCust.Shipname GT "" and y eq 1><cfset val=GetCust.Shipname><cfelse><cfset val=""></cfif>
		<cfinput type="text" name="student#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#" id="student#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#" value="#val#" required="yes" message="Please enter the name of student ###y# registering for #session.cart[idx].name#">
		<br clear="all"/>
		<cfif session.cart[idx].comment>
			<cfif (session.cart[idx].paybyproject eq true and y eq 1) or session.cart[idx].paybyproject eq false>
				<label for="comments#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#">#session.cart[idx].commtitle#</label>
				<textarea name="comments#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#" id="comments#session.cart[idx].cid#-#DateDiff("s", CreateDate(1970,1,1), session.cart[idx].startdt)#-#y#" cols="50" rows="3"></textarea>
				<br/>
			</cfif>
		</cfif>
	</cfloop>
</div>
<br clear="all"/>
</cfloop>
</fieldset>
<br/>
<input type="Submit" value="Step 2 - Secure Payment Information">
</cfoutput>
</cfform>
<cfinclude template="Templates/footer.cfm">