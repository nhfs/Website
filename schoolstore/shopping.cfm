<cfinclude template="Templates/topper.cfm">
<cfquery datasource="#dsn#" name="GetData">
	Select * from shopping_data
</cfquery>

<div class="data wide">
<h2>How Shopping Works</h2>
<cfoutput>
<div class="shopcol">
<cfquery name="GetText" dbtype="query">
	select sh_text from GetData where pid='#ordering_code#'
</cfquery>
<cfif GetText.RecordCount GT "" and GetText.sh_text GT "">
<h3>Ordering Info</h3>
#GetText.sh_text#

</cfif>

<cfquery name="GetText" dbtype="query">
	select sh_text from GetData where pid='#guarantee_code#'
</cfquery>
<cfif GetText.RecordCount GT "" and GetText.sh_text GT "">
<h3>Guarantee</h3>
#GetText.sh_text#

</cfif>

<cfquery name="GetText" dbtype="query">
	select sh_text from GetData where pid='#privacy_code#'
</cfquery>
<cfif GetText.RecordCount GT "" and GetText.sh_text GT "">
<h3>Privacy</h3>
#GetText.sh_text#
</cfif>

<cfquery name="GetText" dbtype="query">
	select sh_text from GetData where pid='#security_code#'
</cfquery>
<cfif GetText.RecordCount GT "" and GetText.sh_text GT "">
<h3>Security</h3>
#GetText.sh_text#
</cfif>

</div>

<div class="shopcol">

<cfquery name="GetText" dbtype="query">
	select sh_text from GetData where pid='#return_code#'
</cfquery>
<cfif GetText.RecordCount GT "" and GetText.sh_text GT "">
<h3>Returns and Refunds</h3>
#GetText.sh_text#

</cfif>

<cfquery name="GetText" dbtype="query">
	select sh_text from GetData where pid='#delivery_code#'
</cfquery>
<cfif GetText.RecordCount GT "" and GetText.sh_text GT "">
<h3>Delivery</h3>
#GetText.sh_text#

</cfif>

<cfquery name="GetText" dbtype="query">
	select sh_text from GetData where pid='#shipping_code#'
</cfquery>
<cfif GetText.RecordCount GT "" and GetText.sh_text GT "">
<h3>Shipping</h3>
#GetText.sh_text#
</cfif>

<cfquery name="GetText" dbtype="query">
	select sh_text from GetData where pid='#gift_code#'
</cfquery>
<cfif GetText.RecordCount GT "" and GetText.sh_text GT "">
<h3>Ordering a Gift</h3>
#GetText.sh_text#
</cfif>
</div>

</cfoutput>
</div>
<cfinclude template="Templates/bottom.cfm">