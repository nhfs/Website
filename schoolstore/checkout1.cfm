<cfset masthead="kayaks">
<cfinclude template="Templates/topper.cfm">
<div class="data wide">
<script language="JavaScript">
<!--

function copyInfo()
{
	f=document.PersInfo;
	f.BillName.value =f.ShipName.value;
	f.BillCo.value = f.ShipCo.value;
	f.BillAddr1.value = f.ShipAddr1.value;
	f.BillAddr2.value = f.ShipAddr2.value;
	f.BillCity.value = f.ShipCity.value;
	f.BillZip.value = f.ShipZip.value;
	f.BillPhone.value = f.ShipPhone.value;
	f.BillState.selectedIndex = f.ShipState.selectedIndex;
	f.BillCountry.selectedIndex = f.ShipCountry.selectedIndex;
}
function checkAddr()
{
	f=document.PersInfo;
	if (f.ShipAddr1.value.search(/\sBox\s/i) != -1)
	{
		alert("Sorry, we can't ship to a PO Box.  Please specify a physical shipping address.");
		return false;
	}
	return true;
}

<cfif isDefined("URL.ErrCd")>
	alert("Sorry, you changed your email address to one that's being used by another registered member of our system.  Please specify a different email address.");
</cfif>
//-->
</script>
<BR>
<DIV ALIGN=Center><H2>
Check-Out Step 1 - Personal Information</H2></DIV>

Please fill out the following screens completely, so that we may ship
your order as soon as possible.  Your order will not be completed
and your credit card will not be charged until you confirm the order 
on the final screen of the checkout system.
<P>
<cfif ArrayLen(session.Cart) LT 1>
	<DIV ALIGN="Center">
	<HR width="160" >
	<b><FONT SIZE="+1">Your cart is empty.</FONT></b>
	<HR width="160" ><P>
	<A HREF="index.cfm">Back to the catalog.</A>
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
Please enter your shipping and billing information below.  Required fields
are marked with a *.
<P>
<cfform name="PersInfo" action="checkout2.cfm" method="POST" scriptSrc="http://kite.boreal.org/CFIDE/scripts">
<TABLE width="90%" align="Center">
<TR>
<TD COLSPAN="4" align="center" class="tier1"><font size="+1"><b>
Shipping Information</b></font>
</TD></TR>
<TR>
<cfoutput>
<cfif session.Upd>
	<input type="HIDDEN" name="CustID" value="#GetCust.CustID#">
</cfif>
<TD>Name*:</TD>
<TD colspan="3">
<cfif isdefined("GetCust.ShipName")>
	<cfinput type="text" name="ShipName" size="60" required="yes" message="Please enter a shipping name" value="#GetCust.ShipName#">
<cfelse>
	<cfinput type="text" name="ShipName" size="60" required="yes" message="Please enter a shipping name">
</cfif>	
</TD>
</TR>
<TR>
<TD>Company:</TD>
<TD colspan="3"><input type="text" name="ShipCo" size="60" <cfif isdefined("GetCust.ShipCo")>value="#GetCust.ShipCo#"</cfif>></TD>
</TR>
<TR>
<TD>Address Line 1*:</TD>
<TD colspan="3">
<cfif isdefined("GetCust.ShipAddr1")>
	<cfinput type="text" name="ShipAddr1" size="60" required="yes" message="Please enter your shipping address" value="#GetCust.ShipAddr1#">
<cfelse>
	<cfinput type="text" name="ShipAddr1" size="60" required="yes" message="Please enter your shipping address">	
</cfif>
</TD>
</TR>
<TR>
<TD>Address Line 2:</TD>
<TD colspan="3"><input type="text" name="ShipAddr2" size="60" <cfif isDefined("GetCust.ShipAddr2")>value="#GetCust.ShipAddr2#"</cfif>></TD>
</TR>
<TR>
<TD>City*:</TD>
<TD>
<cfif isdefined("GetCust.ShipCity")>
	<cfinput type="text" name="ShipCity" size="30" required="yes" message="Please enter your shipping city" value="#GetCust.ShipCity#">
<cfelse>
	<cfinput type="text" name="ShipCity" size="30" required="yes" message="Please enter your shipping city">
</cfif>
</TD>
<TD>State / Province*:</TD>
<TD>
<cfif isdefined("GetCust.ShipState")>
	<cfselect name="ShipState" required="yes" message="Please select your shipping state or province"
		query="GetStates" value="State" selected="#GetCust.ShipState#"></cfselect>
<cfelse>
	<cfselect name="ShipState" required="yes" message="Please select your shipping state or province"
		query="GetStates" Value="State"></cfselect>
</cfif>
</TD>
</TR>
<TR>
<TD>Zip Code / Postal Code*:</TD>
<TD>
<cfif IsDefined("GetCust.ShipZip")>
	<cfinput type="text" name="ShipZip" size="30" required="yes" message="Please enter your shipping zip code or postal code" value="#GetCust.ShipZip#">
<cfelse>
	<cfinput type="text" name="ShipZip" size="30" required="yes" message="Please enter your shipping zip code or postal code">
</cfif>
</TD>
<TD>Country:</TD>
<TD COLSPAN="3"><cfselect name="ShipCountry" required="yes" message="Please enter your shipping state">
	<OPTION value="USA" <Cfif isdefined("GetCust.ShipCountry") and GetCust.ShipCountry EQ "USA">selected</CFIF>>United States
	<OPTION value="CAN" <Cfif isdefined("GetCust.ShipCountry") and GetCust.ShipCountry EQ "CAN">selected</CFIF>>Canada
	</cfselect>
</TD>
</TR>
<TR>
<TD>Phone Number:</TD>
<TD COLSPAN="3">
<input type="text" name="ShipPhone" size="60" <cfif isdefined("GetCust.ShipPhone")>value="#GetCust.ShipPhone#"</cfif>>
</TD>
</TR>
<TR>
<TD>E-Mail Address:</TD>
<cfif isdefined("GetCust.Email")><cfset val=GetCust.Email><cfelse><cfset val=""></cfif>
<TD COLSPAN="3"><cfinput type="text" name="Email" value="#val#" size="60" required="yes" message="Please enter your email address"></TD>
</TR>
</TABLE>

<TABLE width="90%" align="Center">
<TR>
<TD COLSPAN="4" align="center" class="tier1"><font size="+1"><b>
Billing Information</b></font>
</TD></TR>
<TR>
<TD Colspan="4">
	<input type="Checkbox" onClick="copyInfo()">Copy Shipping Information
</TD>
</TR>
<TR>
<TD>Name*:</TD>
<TD colspan="3">
<cfif isdefined("GetCust.BillName")>
	<cfinput type="text" name="BillName" size="60" required="yes" message="Please enter a billing name" value="#GetCust.BillName#">
<cfelse>
	<cfinput type="text" name="BillName" size="60" required="yes" message="Please enter a billing name">
</cfif>	
</TD>
</TR>
<TR>
<TD>Company:</TD>
<TD colspan="3"><input type="text" name="BillCo" size="60" <cfif isdefined("GetCust.BillCo")>value="#GetCust.BillCo#"</cfif>></TD>
</TR>
<TR>
<TD>Address Line 1*:</TD>
<TD colspan="3">
<cfif isdefined("GetCust.BillAddr1")>
	<cfinput type="text" name="BillAddr1" size="60" required="yes" message="Please enter your billing address" value="#GetCust.BillAddr1#">
<cfelse>
	<cfinput type="text" name="BillAddr1" size="60" required="yes" message="Please enter your billing address">	
</cfif>
</TD>
</TR>
<TR>
<TD>Address Line 2:</TD>
<TD colspan="3"><input type="text" name="BillAddr2" size="60" <cfif isDefined("GetCust.BillAddr2")>value="#GetCust.BillAddr2#"</cfif>></TD>
</TR>
<TR>
<TD>City*:</TD>
<TD>
<cfif isdefined("GetCust.BillCity")>
	<cfinput type="text" name="BillCity" size="30" required="yes" message="Please enter your billing city" value="#GetCust.BillCity#">
<cfelse>
	<cfinput type="text" name="BillCity" size="30" required="yes" message="Please enter your billing city">
</cfif>
</TD>
<TD>State / Province*:</TD>
<TD>
<cfif isdefined("GetCust.BillState")>
	<cfselect name="BillState" required="yes" message="Please select your billing state or province"
		query="GetStates" value="State" selected="#GetCust.BillState#"></cfselect>
<cfelse>
	<cfselect name="BillState" required="yes" message="Please select your billing state or province"
		query="GetStates" value="State"></cfselect>
</cfif>
</TD>
</TR>
<TR>
<TD>Zip Code / Postal Code*:</TD>
<TD>
<cfif IsDefined("GetCust.BillZip")>
	<cfinput type="text" name="BillZip" size="30" required="yes" message="Please enter your billing zip code or postal code" value="#GetCust.BillZip#">
<cfelse>
	<cfinput type="text" name="BillZip" size="30" required="yes" message="Please enter your billing zip code or postal code">
</cfif>
</TD>
<TD>Country:</TD>
<TD COLSPAN="3"><cfselect name="BillCountry" required="yes" message="Please enter your billing state">
	<OPTION value="USA" <Cfif isdefined("GetCust.BillCountry") and GetCust.BillCountry EQ "USA">selected</CFIF>>United States
	<OPTION value="CAN" <Cfif isdefined("GetCust.BillCountry") and GetCust.BillCountry EQ "CAN">selected</CFIF>>Canada
	</cfselect>
</TD>
</TR>
<TR>
<TD>Phone Number:</TD>
<TD COLSPAN="3">
<input type="text" name="BillPhone" size="60" <cfif isdefined("GetCust.BillPhone")>value="#GetCust.BillPhone#"</cfif>>
</TD>
</TR>
<TR>
<TD COLSPAN="4" align="center" class="tier1"><font size="+1"><b>
Comments / Special Instructions</b></font>
</TD></TR>
<TR>
<TD>
Comments or Special Instructions:
</TD>
<td colspan="3">
<textarea name="Comments" cols="40" rows="5"></textarea>
</td>
</TR>
</TABLE>

<TABLE width="90%" align="Center">
<TR>
<td colspan=2 align="Center">
<input type="Submit" value="Step 2 - Secure Credit Card Info">
</td>
</TR></TABLE>
</cfoutput>
</cfform>
</div>
<cfinclude template="Templates/bottom.cfm">