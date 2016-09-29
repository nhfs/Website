</div> <!--- content --->
<div class="menubot">
<cfoutput>
			<a href="#site_url#../../friendtofriend.htm">
			<span class="bigger">T</span>ELL
			<span class="bigger">A</span>
			<span class="bigger">F</span>RIEND</a>&nbsp;&nbsp;&nbsp;&nbsp; 
			|&nbsp;&nbsp;&nbsp;&nbsp; 
			
			
			
			<a href="#site_url#../../catalog.htm">
			<span class="bigger">R</span>EQUEST
			<span class="bigger">A C</span>ATALOG</a>
			&nbsp; |&nbsp;&nbsp;&nbsp;&nbsp;<a href="#site_url#../../enewsletter.htm">
			<span class="bigger">E</span>-<span class="bigger">N</span>EWSLETTER</a>
			
			&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="#site_url#../../getinvolved/supportus/index.htm">
			<span class="bigger">S</span>UPPORT
			<span class="bigger">O</span>UR
			<span class="bigger">E</span>FFORTS
			</a>
			<br/>
</div>
<cfquery name="GetPhoto" datasource="#dsn#">
	select photourl from footerphoto
</cfquery>
<cfif getPhoto.recordCount GT 0>
	<img border="0" src="#site_url##getphoto.photourl#" width="797" height="91">
<cfelse>
	<img border="0" src="#site_url#../../images/mainpage/bottombannerwinter2.jpg" width="797" height="91">
</cfif>
</cfoutput>
<div class="footer">
<br/>
North House Folk School | P.O. Box 759 - 500 Highway 61 West | Grand Marais, Minnesota 55604<br>
218-387-9762 | 888-387-9762 | 
<a href="mailto:info@northhouse.org">info@northhouse.org</a>
<p>
<cfoutput>
<a href="#site_url#../../courses/themes/basketry/index.htm">Basketry</a> |
<a href="#site_url#../../courses/themes/boatbuilding/index.htm">Boatbuilding</a> |
<a href="#site_url#../../courses/themes/clothingandjewelry/index.htm">Clothing &amp; Jewelry</a> |
<a href="#site_url#../../courses/themes/fiberarts/index.htm">Fiber Arts</a> |
<a href="#site_url#../../courses/themes/foods/index.htm">Foods</a> |
<a href="#site_url#../../courses/themes/knitting/index.htm">Knitting</a> |
<a href="#site_url#../../courses/themes/music/index.htm">Music</a><br>
<a href="#site_url#../../courses/themes/northernecology/index.htm">Northern Ecology</a> |
<a href="#site_url#../../courses/themes/outdoorskillsandtravel/index.htm">Outdoor Skills &amp; Travel</a> |
<a href="#site_url#../../courses/themes/paintingandphotography/index.htm">Painting &amp; Photography</a> |
<a href="#site_url#../../courses/themes/sailing/index.htm">Sailing</a> |
<a href="#site_url#../../courses/themes/shelter/index.htm">Shelter</a> |
<a href="#site_url#../../courses/themes/sustainableliving/index.htm">Sustainable Living</a> <br>
<a href="#site_url#../../courses/themes/timberframing/index.htm">Timber Framing</a> |
<a href="#site_url#../../courses/themes/toolmaking/index.htm">Tool Making</a> |
<a href="#site_url#../../courses/themes/traditionalcrafts/index.htm">Traditional Crafts</a> |
<a href="#site_url#../../courses/themes/woodcarving/index.htm">Woodcarving</a> |
<a href="#site_url#../../courses/themes/woodworking/index.htm">Woodworking</a></cfoutput>
</p>
&copy;2009&nbsp; North House Folk School<br>
All Rights Reserved

</div> <!--- footer ---->
</div> <!--- page --->
</body>