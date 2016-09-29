<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<!-- shopping cart software copyright 2003 Boreal Access.  All rights reserved -->
<cfset bookid="11111"><cfset toolsid="33333"><cfset clothingid="22222"><cfset certid="44444"><cfset cardsid="CARDS"><cfset moreid="OTHER">
<head>
<meta name="Classification" content="Education">
<meta name="Description" content="Folk studies, arts, boatbuilding, crafts, nature, study, sailing, woodworking, music, Lake Superior, Grand Marais, Minnesota, where learning is valued for its own sake.">
<meta name="description " content="North House Folk School is dedicated to promoting and preserving the knowledge, skills, crafts and stories of the past and present">
<meta name="KeyWords" content="Minnesota, Folk Schools, Classes, Lake Superior, education, arts folk, arts, crafts, world, music, boatbuilding, woodworking, nature, study, ecology, sailing, Lake Superior, Minnesota">
<title>North House Folk School</title>	
<link type="text/css" rel="stylesheet" href="templates/schoolstore.css"/>
<script language="JavaScript" src="templates/scripts.js" type="text/javascript"></script>

<cfquery name="GetSubcats" datasource="#dsn#">
	select scatid, catid, scatname, scatorder from subcats
</cfquery>
</HEAD>
<BODY>
<A name="top"></A>
<div class="page">
<div class="border"></div>
<cfif not IsDefined("masthead")><cfset masthead="campus"></cfif><div class="header">
<img src="images/northhouselogo.gif" alt="North House Folk School" width="80" height="45" border="0" class="logo">
<a href="http://www.northhousefolkschool.com/"><img border="0" src="images/<cfoutput>#masthead#</cfoutput>_masthead.jpg" width="432" height="151"></a>
<p class="title">
North House Folk School<br/>
Online Store</p>
<p class="contact">888-387-9762</p>
</div>
<div class="borderbottom">&nbsp;</div><div class="menu">
<div class="dropdown" onmouseover="drop('welcome')" onmouseout="shrink('welcome')" id="welcometab">
<p><span class="cap">W</span>elcome</p>
<div class="submenu" id="welcome">
	<div class="menuitem"><a href="index.cfm">School Store Home Page</a></div>
	<div class="menuitem"><a href="../index.htm">North House Folk School</a></div>
	<div class="menuitem"><a href="shopping.cfm">How Shopping Works</a></div>
	<div class="menuitem"><a href="viewcart.cfm">View Shopping Cart</a></div>
</div>
</div>

<div class="dropdown" onmouseover="drop('featured')" onmouseout="shrink('featured')" id="featuredtab">
<p><span class="cap">F</span>eatured</p>
<div class="submenu" id="featured">
	<div class="menuitem"><a href="catalog.cfm?featured=best">Best Sellers</a></div>
	<div class="menuitem"><a href="catalog.cfm?featured=new">New Arrivals</a></div>
<cfquery name="getFeatured" datasource="#dsn#">
	select pid from monthly_data where showbox order by pid desc
</cfquery>
<cfoutput query="getFeatured">
	<cfif pid eq staff_month>
		<cfset feat="recomended">
		<cfset fname="Staff Picks">
	<cfelseif pid eq tool_month>
		<cfset feat="tools">
		<cfset fname="Tools of the Season">
	<cfelse>
		<cfset feat="instructor">
		<cfset fname="Instructor of the Month">
	</cfif>
	<div class="menuitem"><a href="catalog.cfm?featured=#feat#">#fname#</a></div>
</cfoutput>
	<div class="menuitem"><a href="catalog.cfm?featured=gift">Gift Certificates</a></div>
	<div class="menuitem"><a href="catalog.cfm?featured=sale">On Sale</a></div>
</div>
</div>

<div class="dropdown" onmouseover="drop('books')" onmouseout="shrink('books')" id="bookstab">
<p><span class="cap">B</span>ooks</p>
<div class="submenu" id="books"><cfoutput>
	<div class="menuitem"><a href="catalog.cfm?catid=#bookid#">All Books</a></div>
	<cfquery name="GetBooks" dbtype="query">select * from GetSubCats where catid='#bookid#' order by scatorder, scatname</cfquery>
	<cfloop query="GetBooks">
	<div class="menuitem"><a href="catalog.cfm?catid=#bookid#&scatid=#scatid#">#scatname#</a></div>
	</cfloop></cfoutput>
</div>
</div>

<div class="dropdown" onmouseover="drop('tools')" onmouseout="shrink('tools')" id="toolstab">
<p><span class="cap">T</span>ools</p>
<div class="submenu" id="tools"><cfoutput>
	<div class="menuitem"><a href="catalog.cfm?catid=#toolsid#">All Tools</a></div>
	<cfquery name="GetTools" dbtype="query">select * from GetSubCats where catid='#toolsid#' order by scatorder, scatname</cfquery>
	<cfloop query="GetTools">
	<div class="menuitem"><a href="catalog.cfm?catid=#toolsid#&scatid=#scatid#">#scatname#</a></div>
	</cfloop></cfoutput>
</div>
<!---cfif IsDefined("url.catid") and URL.catid eq toolsid>
	<img src="images/tools_fore.gif" alt="Tools" width="110" height="39" border="0">
<cfelse>
	<img src="images/tools_backa.gif" alt="Tools" width="110" height="39" border="0">
</cfif--->
</div>

<div class="dropdown" onmouseover="drop('clothing')" onmouseout="shrink('clothing')" id="clothingtab">
<p><span class="cap">C</span>lothing</p>
<div class="submenu" id="clothing"><cfoutput>
	<div class="menuitem"><a href="catalog.cfm?catid=#clothingid#">All Clothing</a></div>
	<cfquery name="GetClothing" dbtype="query">select * from GetSubCats where catid='#clothingid#' order by scatorder, scatname</cfquery>
	<cfloop query="GetClothing">
	<div class="menuitem"><a href="catalog.cfm?catid=#clothingid#&scatid=#scatid#">#scatname#</a></div>
	</cfloop></cfoutput>
</div>
</div>

<div class="dropdown" onmouseover="drop('cards')" onmouseout="shrink('cards')" id="cardstab">
<p><span class="cap">C</span>ards</p>
<div class="submenu" id="cards"><cfoutput>
	<div class="menuitem"><a href="catalog.cfm?catid=#cardsid#">All Cards</a></div>
	<cfquery name="GetCards" dbtype="query">select * from GetSubCats where catid='#cardsid#' order by scatorder, scatname</cfquery>
	<cfloop query="GetCards">
	<div class="menuitem"><a href="catalog.cfm?catid=#cardsid#&scatid=#scatid#">#scatname#</a></div>
	</cfloop></cfoutput>
</div>
</div>

<div class="dropdown" onmouseover="drop('more')" onmouseout="shrink('more')" id="moretab">
<p><span class="cap">M</span>ore...</p>
<div class="submenu" id="more"><cfoutput>
	<div class="menuitem"><a href="catalog.cfm?catid=#moreid#">All More...</a></div>
	<cfquery name="GetMore" dbtype="query">select * from GetSubCats where catid='#moreid#' order by scatorder, scatname</cfquery>
	<cfloop query="GetMore">
	<div class="menuitem"><a href="catalog.cfm?catid=#moreid#&scatid=#scatid#">#scatname#</a></div>
	</cfloop></cfoutput>
</div>
</div>
</div>  <!--- menu --->

<br clear="all"/>