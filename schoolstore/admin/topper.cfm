<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<!-- shopping cart software copyright 2003 Boreal Access.  All rights reserved -->
<head>
<title>North House Folk School Shopping Cart Administration</title>	
<!-- shopping cart software copyright 2003 Boreal Access.  All rights reserved -->
<!--- Client side cache prevention --->
<meta http-equiv="Expires" content="0">

<!--- Setup our expire times for Netscape and Internet Explorer --->
<cfoutput>
        <!--- Internet Explorer Date Formate: (Fri, 30 Oct 1998 14:19:41 GMT) --->
        <cfset MSIEtimestamp='#dateformat(now(),"DDD")#,#dateformat(now(),"DD")# #dateformat(now(),"Mmm")# #timeformat(now(),"HH:MM:SS")#'>

        <!--- Netscape Date Formate: Netscape (Wednesday, Apr 26 2000 17:45:25 PM) --->
        <cfset NETSCAPEtimestamp='#dateformat(now(),"DDDD")#,#dateformat(now(),"MMM")# #dateformat(now(),"dd")# #dateformat(now(),"YYY")# #timeformat(now(),"HH:MM:SS tt")#'>
</cfoutput>

<!--- Tell HTTP Header to force expire of page - nocache --->
<cfif HTTP_USER_AGENT contains "MSIE">
        <cfheader name="Expires" value="<cfoutput>#MSIEtimestamp#</cfoutput>">
        <cfheader name="Pragma" value="no-cache">
        <cfheader name="cache-control" value="no-cache, no-store, must-revalidate">
<cfelse>
        <cfheader name="Expires" value="<cfoutput>#NETSCAPEtimestamp#</cfoutput>">
        <cfheader name="Pragma" value="no-cache">
        <cfheader name="cache-control" value="no-cache, no-store, must-revalidate">
</cfif>

<link type="text/css" rel="stylesheet" href="../templates/schoolstore.css"/>
<link type="text/css" rel="stylesheet" href="ssadmin.css"/>
<script type="text/javascript" src="../../courses/admin/jquery/js/jquery-1.4.2.min.js"></script>
<script language="JavaScript" src="../templates/scripts.js" type="text/javascript"></script>

</HEAD>
<BODY>
<A name="top"></A>
<div class="page">
<div class="border"></div>
<div class="header">
<img src="../images/northhouselogo.gif" alt="North House Folk School" width="80" height="45" border="0" class="logo">
<a href="http://www.northhouse.org/"><img border="0" src="../images/campus_masthead.jpg" width="432" height="151"></a>
<p class="title">
North House Folk School<br/>
Online Store Administration</p>
</div>
<div class="borderbottom">&nbsp;</div><div class="menu">
<div class="dropdown" id="cattab">
<p><a href="addcategory.cfm"><span class="cap">C</span>ategories</a></p>
</div>
<div class="dropdown" id="scattab">
<p><a href="addscat.cfm"><span class="cap">S</span>ub-categories</a></p>
</div>
<div class="dropdown" id="linetab">
<p><a href="addline.cfm"><span class="cap">L</span>ines</a></p>
</div>
<!---div class="submenu" id="cat">
	<div class="menuitem">Categories</a></div>
	<div class="menuitem"><a href="addscat.cfm">Sub-categories</a></div>
	<div class="menuitem"><a href="addline.cfm">Lines</a></div>
</div>
</div--->

<div class="dropdown" onmouseover="drop('items')" onmouseout="shrink('items')" id="itemtab">
<p><span class="cap">I</span>tems</p>
<div class="submenu" id="items">
	<div class="menuitem"><a href="additem.cfm">Individual Items</a></div>
	<div class="menuitem"><a href="addgroup.cfm">Grouped Items</a></div>
</div>
</div>

<div class="dropdown" onmouseover="drop('pages')" onmouseout="shrink('pages')" id="pagetab">
<p><span class="cap">P</span>ages</p>
<div class="submenu" id="pages">
	<div class="menuitem"><a href="addpara.cfm">Launch Page Text</a></div>
	<div class="menuitem"><a href="addpower.cfm">Power Boxes</a></div>
	<div class="menuitem"><a href="addopara.cfm">How Shopping Works</a></div>
</div>
</div>

<div class="dropdown" id="monthtab">
<p><a href="addmonth.cfm"><span class="cap">M</span>onthly Staff/Instr/Tool</a></p>
<!---cfif IsDefined("url.catid") and URL.catid eq toolsid>
	<img src="images/tools_fore.gif" alt="Tools" width="110" height="39" border="0">
<cfelse>
	<img src="images/tools_backa.gif" alt="Tools" width="110" height="39" border="0">
</cfif--->
</div>


</div>  <!--- menu --->

<br clear="all"/>
<div class="data wide">