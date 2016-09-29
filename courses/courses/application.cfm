<cfapplication name="cartadminnh" sessionManagement="Yes">
<cfset dsn="northhouse">
<cfset fileloc="f:\northhouse.org\courses\courses\">
<cfset peak_start=CreateDate(year(now()),5,1)>
<cfset peak_end=CreateDate(year(now()),10,31)>
<cfset site_url="http://www.northhouse.org/courses/courses/">
<cfset secure_url="https://raven.boreal.org/secure/northhouse/">
<cfset adm_email="info@northhouse.org">
<cfset reg_email="info@northhouse.org,kcostello@northhouse.org">
<cfset don_email="info@northhouse.org,kbohlin@northhouse.org">
<cfset cat_email="kcostello@northhouse.org">
<cfset co_phone="888-387-9762">
<cfset co_name="North House Folk School">
<cfset co_url="http://www.northhouse.org">

<cfset levels=ArrayNew(1)>
<cfset arrayAppend(levels, "Basic")>
<cfset arrayAppend(levels, "Intermediate")>
<cfset arrayAppend(levels, "Advanced")>
<cfset arrayAppend(levels, "Sponsor")>
<cfset arrayAppend(levels, "Major Sponsor")>
<cfset arrayAppend(levels, "Foundation Support")>
<cfset foundation=6>

<cfset part_pid="pa">

<cfset releasetime=createTime(8, 0,0)>


<cfif not isdefined("session.Cart")>
	<cfset session.Cart=arraynew(1)>
</cfif>

