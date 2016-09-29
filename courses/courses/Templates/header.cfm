<cfif isdefined("html5")>
<!DOCTYPE html>
<html lang="en">
<cfelse>
<html>
</cfif>
<cfoutput>
<!-- Deluxe Menu -->
<noscript>
 <a href="http://deluxe-menu.com">JavaScript Menu by Deluxe-Menu.com</a></noscript>
<script type="text/javascript" language="JavaScript1.2" src="#site_url#../../menu.files/dmenu.js"></script>
<!-- Copyright (c) 2008, Deluxe Menu, deluxe-menu.com -->

<meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<meta name="description " content="<cfif isDefined("meta_desc")><cfoutput>#meta_desc#</cfoutput><cfelse>North House Folk School's mission is to enrich lives and build community through the teaching of traditional northern crarts.  Classes focused on timber framing, wooden boat building, woodworking, woodcarving, basketry and more are offered throughout the year.</cfif>">

<meta name="keywords" content="Timber Frame, wooden boat building, fiber arts, basketry, woodcarving, woodworking, timber framing, sustainable living, blacksmithing, knife making, sailing, northern ecology, traditional crafts, classes, courses, workshops">
<link type="text/css" rel="stylesheet" href="#site_url#Templates/nhcourses.css">
<script src="#site_url#Templates/scripts.js" type="text/javascript"></script>
<title><cfif isDefined("title")><cfoutput>#title#</cfoutput><cfelse>North House Folk School - Courses, Classes, Workshops</cfif></title>
<!---style fprolloverstyle>A:hover {color: #D20000}
 </style--->
</head>
<body>
&nbsp;
<div class="page">
<div class="header">
<a href="#site_url#../../index.htm"><img src="#site_url#../../images/mainpage/logo.jpg" alt="North House Folk School" width="106" height="65" border="0"></a>
<div class="searchbox"<cfif isDefined("html5")> style="bottom: 1.7em;"</cfif>>
<form action="http://www.northhouse.org/search.cfm" id="cse-search-box">
  <div>
    <input type="hidden" name="cx" value="001872937123721805829:s1dzwwhxkfu" />
    <input type="hidden" name="cof" value="FORID:9" />
    <input type="hidden" name="ie" value="UTF-8" />
    <input type="text" name="q" size="15" />
    <input type="submit" name="sa" value="Search" />
  </div>
</form>
<script type="text/javascript" src="http://www.google.com/cse/brand?form=cse-search-box&lang=en"></script>
</div>
<div class="left">
<span class="bigger">N</span>orth <span class="bigger">H</span>ouse <span class="bigger">F</span>olk 
<span class="bigger">S</span>chool
</div>
<div class="right">(888)387-9762</div>
<br clear="all"/>
</div> <!--- header --->
<div class="menu">
    <script type="text/javascript" src="#site_url#../../menu.js"></script>
</div> <!--- menu --->
<div class="content">
<div class="imgbar">
<img border="0" src="#site_url#../../images/banner/banner1.jpg" width="100" height="100"><img border="0" src="#site_url#../../images/banner/banner2.jpg" width="100" height="100"><img border="0" src="#site_url#../../images/banner/banner3.jpg" width="100" height="100"><img border="0" src="#site_url#../../images/banner/banner4.jpg" width="100" height="100"><img border="0" src="#site_url#../../images/banner/banner5.jpg" width="100" height="100"><img border="0" src="#site_url#../../images/banner/banner6.jpg" width="97" height="100"><img border="0" src="#site_url#../../images/banner/banner7.jpg" width="100" height="100"><img border="0" src="#site_url#../../images/banner/banner8.jpg" width="100" height="100">
</div>
<cfif isDefined("session.cart") and arraylen(session.cart) GT 0 and findnocase("register",CGI.script_name) eq 0 and findNoCase("checkout",CGI.script_name) eq 0><h2 class="viewcart"><a href="#site_url#register.cfm">View Pending Registrations</a></h2></cfif>
</cfoutput>

