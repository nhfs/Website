<cfapplication name="shopcartnh" sessionManagement="Yes">
<cfset dsn="northhouse">
<cfset adm_email="abeavers@northhouse.org,info@northhouse.org">
<!---cfset adm_email="sandys@boreal.org"--->
<cfset co_phone="1-218-387-9762">
<cfset co_name="North House Folk School">
<cfset co_URL="http://www.northhouse.org">
<cfset secure_url="https://raven.boreal.org/secure/northhouse/">
<cfset fileloc="F:\northhouse.org\schoolstore\">
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

<cfset catwid=145>

<cfset handcraftcid="OTHER">
<cfset handcraftsid=40>

<cfset taxrate=.06875>
<cfif now() lt createDate(2010,4,1)>
	<cfset localtax=.06875>
<cfelse>
	<cfset localtax=.07875>
</cfif>


<cfset cert_cat="44444">

<cfif not isdefined("session.Cart")>
	<cfset session.Cart=arraynew(1)>
</cfif>