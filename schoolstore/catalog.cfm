<cfif not IsDefined("URL.CatID") and not IsDefined("featured")>
	<cflocation url="index.cfm">
</cfif>

<cfset masthead="raku">
<cfinclude template="Templates/topper.cfm">
<!-- shopping cart software copyright 2003 Boreal Access.  All rights reserved -->
<cfinclude template="Templates/sidebar.cfm">
<script type="text/javascript" src="../courses/admin/jquery/js/jquery-1.4.2.min.js"></script>
<cfif IsDefined("url.catid")>
	<cfquery datasource="#dsn#" name="GetCat">
		Select scatid, catname, catmessage, catpicture, catdesc, scatdesc, scatname
		 from Categories c INNER JOIN subcats sc on c.catid=sc.catid 
		 	WHERE c.CatID = <cfqueryparam cfsqltype="CF_SQL_CHAR" value="#URL.CatID#">
		<cfif Isdefined("url.scatid")>
				And scatid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.scatid#">
		</cfif>
		order by scatorder, scatname
	</cfquery>
<cfelse>
	<cfquery datasource="#dsn#" name="GetCat">
		select 0 as scatid, <cfif url.featured eq "best">'Best Sellers'<cfelseif url.featured eq "new">'New Arrivals'
		<cfelseif url.featured eq "tools">'Tools for the Season'
		<cfelseif url.featured eq "recommended">'This Month\'s Staff Picks'<cfelseif url.featured eq "instructor">'Instructor of the Month'
		<cfelseif url.featured eq "sale">'On Sale'<cfelse>'Gift Certificates'</cfif> as catname, '' as catmessage, '' as catpicture, 
			'' as catdesc, '' as scatdesc,
			'' as scatname from categories
			limit 1
	</cfquery>

</cfif>
<div class="data">

<cfoutput>
<!---cfif IsDefined("url.catid")--->

<cfset ctr=0>
	
<cfloop query="GetCat">
<cfif CurrentRow neq 1>
	<cfif ctr GT 0></div><script type="text/javascript">$("###iid# .imgbox").height(#maxh#);</script></cfif>
	<div class="borderbottom">&nbsp;</div>
	<br/>
</cfif>
<div class="leftcol">
<cfif GetCat.CatMessage GT "" and currentrow eq 1><b>#GetCat.CatMessage#</b><cfelse>&nbsp;</cfif>
</div>

<div class="midcol centered">
<cfif GetCat.CatPicture GT " ">
	<IMG SRC="./#GetCat.CatPicture#" alt="#GetCat.CatName#"><BR>
	<cfif GetCat.scatname GT ""><h3>#GetCat.scatname#</h3></cfif>
<cfelse>
	<H2>#GetCat.CatName#<cfif GetCat.scatname GT ""> - #GetCat.scatname#</cfif></h2>
</cfif>
</div>

<div class="rightcol"><cfif currentrow eq 1><cfinclude template="Templates/cartcnt.cfm"></cfif></div>
<br clear="all"/>

<cfif GetCat.CatDesc GT "" and currentrow eq 1>#REReplace(GetCat.CatDesc,"\n[\s]*","<P>","All")#<BR></cfif>

<cfif GetCat.sCatDesc GT "">#REReplace(GetCat.sCatDesc,"\n[\s]*","<P>","All")#<BR></cfif>

<cfif scatid GT 0>
	<cfquery datasource="#dsn#" name="Get_lines">
		SELECT DISTINCT _lines.LineID, LineName, LineOrder
			FROM (_lines INNER JOIN Groups ON _lines.LineID = Groups.LineID)
			INNER JOIN items on groups.groupid = items.groupid
			WHERE _lines.sCatID=#sCatID# AND not outofstock
			ORDER BY LineOrder, LineName
	</cfquery>

	<cfif Get_lines.RecordCount GT 1>
		<table class="linelist">
		<cfloop query="Get_lines">
			<cfif currentrow mod 7 eq 1>
				<cfif currentrow neq 1></tr></cfif>
				<tr>
			</cfif>
			<td class="line"><A HREF="###LineID#">#LineName#</A></td>
		</cfloop>
		</tr></table>
	</cfif>
	<br clear="all"/>
</cfif>
<cfif isDefined("url.featured") and url.featured eq "gift">
	<cfquery name="Getscat" datasource="#dsn#">
		select scatid from subcats where catid='#certid#'
	</cfquery>
</cfif>

<!--------------- code for featured of the month header info ------------------------>
<cfif isDefined("url.featured") and (url.featured eq "recommended" or url.featured eq "instructor" or url.featured eq "tools")>
<!--- get "of the month" information --->
	<cfquery name="GetMonth" datasource="#dsn#">
		select * from monthly_data where 
			<cfif url.featured eq "recommended">
				pid=#staff_month#
			<cfelseif url.featured eq "instructor">
				pid=#inst_month#
			<cfelse>
				pid=#tool_month#
			</cfif>
	</cfquery>
	<cfif GetMonth.RecordCount GT 0>
		<cfquery name="getPHotos" datasource="#dsn#">
			select photo, caption from monthly_pics where 
			<cfif url.featured eq "recommended">
				pid=#staff_month#
			<cfelseif url.featured eq "instructor">
				pid=#inst_month#
			<cfelse>
				pid=#tool_month#
			</cfif>
			order by pord
		</cfquery>
		<div class="monthphoto">
		<cfloop query="getPhotos">
			<cfif photo GT "">
				<img src="#photo#" alt="#getmonth.fname# #getmonth.lname#"><br/>
				<cfif caption gt "">#caption#<br/></cfif>
			</cfif>
			<cfif getmonth.iid GT 0>
			<a href="../courses/courses/instructor.cfm?iid=#getmonth.iid#">Learn more about #getmonth.fname#</a>
			</cfif>
		</cfloop>
		</div>
	<div class="monthstory">
		<b>#getmonth.fname# #getmonth.lname#</b>

		<p>#getmonth.story#</p>
	</div>
	<br clear="all"/><br/>
	<cfif url.featured eq "instructor" and getmonth.iid GT 0>
	<!--- get courses for instructor --->
		<cfquery name="GetCourses" datasource="#dsn#">
			select c.cid, name from courses c INNER JOIN course_inst ci 
				ON c.cid = ci.cid
				WHERE ci.iid=#getmonth.iid#
				ORDER BY name
		</cfquery>
		<cfif getCourses.recordCount GT 0>
			<b>#getmonth.fname# Teaches:</b><br/>

			<cfloop query="GetCourses">
			<a href="../courses/courses/course.cfm?cid=#cid#">#name#</a><br/>
			</cfloop>
			<br/>
		</cfif>

	</cfif>
	<cfif getmonth.iid GT 0>
	<b>#getmonth.fname#'s Picks:</b><br/><br/>
	<cfelse>
	<p>#getmonth.extratext#</p>
	</cfif>
</cfif>
</cfif>
<!------------------------------------------------------------>
<cfquery datasource="#dsn#" name="GetItems">
	Select Items.*, _lines.*, Groups.*
		FROM Items INNER JOIN 
			(Groups INNER JOIN 
			_lines ON Groups.LineID = _lines.LineID)
			ON Items.GroupID = Groups.GroupID
			WHERE not outofstock
		<cfif scatid GT 0>
		AND _lines.sCatID=#sCatID#
		<cfelseif isDefined("URL.featured")>
			AND
			<cfswitch expression="#url.featured#">
			<cfcase value="new">newitem</cfcase>
			<cfcase value="best">bestseller</cfcase>
			<cfcase value="recommended">recommended</cfcase>
			<cfcase value="instructor">instructor</cfcase>
			<cfcase value="gift">_lines.scatid=#GetScat.scatid#</cfcase>
			<cfcase value="sale">saleprice > 0</cfcase>
			<cfcase value="tools">toolpic</cfcase>
			</cfswitch>
		</cfif>
		
		ORDER BY LineOrder, LineName, GroupOrder, Groups.GroupID, ItemOrder,
		ItemName
</cfquery>
<cfset AID=0>
<cfset GRP=1>
<cfset ctr=0>

<cfloop query="GetItems">
<cfif LineID neq AID and IsDefined("Get_lines.recordcount") and Get_lines.recordCount GT 1>
	<!---- Put in Line Information ----->
	<!---- if not the first Line, put a "back to the top" link at the bottom
    	   of the last Line's items. ---->
		<cfif AID neq 0>
			<div class="border">&nbsp;</div>
		</cfif>
		
		<A NAME="#LineID#"></A>
		<h3>#LineName#</h3>
		
		<cfif LinePicture GT "" or LineDesc GT "">
			<BR>
			<div class="centered"><A HREF="Lineinfo.cfm?LineID=#LineID#">More Information on #LineName#</A></div>
		</cfif>
		<cfset AID=LineID>
		<cfif ctr GT 0></div><script type="text/javascript">$("###iid# .imgbox").height(#maxh#);</script></cfif>
		<cfset ctr=0>
</cfif>

<a name="i#itemid#"></a>
<cfif grp eq 1>
<cfif ctr mod 4 eq 0>
	<cfif ctr GT 0>
		</div>
		<script type="text/javascript">$("###iid# .imgbox").height(#maxh#);</script>
	</cfif>
	<div class="itemrow" id="irow#ctr##lineid#"><cfset iid="irow#ctr##lineid#"><cfset maxh=0>
</cfif>
<div class="itemcol">
<cfif Picture gt "" and fileExists("#fileloc##picture#") and (thumb eq "" or not fileExists("#fileloc##thumb#"))>
	<cfset imageCFC = createObject("component","image")>
	<!--- first, get current dimensions --->
	<cfset imgInfo = imageCFC.getImageInfo("", "#fileloc##picture#")>
	<cfif imgInfo.width gt catwid or imginfo.height GT catwid>
		<cfif imgInfo.height GT imgInfo.width>
			<cfset h=catwid>
			<cfset w=(imgInfo.width/imgInfo.height)*catwid>
		<cfelse>
			<cfset w=catwid>
			<cfset h=(imgInfo.height/imginfo.width)*catwid>		
		</cfif>
	<cfelse>
		<cfset w=imgInfo.width>
		<cfset h=imgInfo.height>
	</cfif>
<cfelseif thumb GT "" and fileExists("#fileloc##thumb#")>
	<cfset imageCFC = createObject("component","image")>	
	<cfset imgInfo = imageCFC.getImageInfo("", "#fileloc##thumb#")>
	<cfset w=imgInfo.width>
	<cfset h=imgInfo.height>
</cfif>
<cfif picture GT "" and fileExists("#fileloc##picture#")>
	<div class="imgbox">
	<a href="item.cfm?<cfif grouped>Groupid=#groupid#<cfelse>ItemID=#itemid#</cfif>">
	<cfif thumb GT "">
		<img src="#thumb#" alt="#groupName#" title="#groupname#" border="0" style="left: #(catwid-w)/2#px;"/>
	<cfelse>
		<img src="#picture#" alt="#GroupName#" title="#groupname#" width="#w#" height="#h#" border="0" style="left: #(catwid-w)/2#px;">
	</cfif>
	</a>
	</div>
	<cfif h GT maxh><cfset maxh=h></cfif>
</cfif>
<a href="item.cfm?<cfif grouped>Groupid=#groupid#<cfelse>ItemID=#itemid#</cfif>"><b>#groupName#</b></a><br/>
<cfif newitem eq true><span class="red">*NEW ARRIVAL*</span><br/></cfif>
<cfif bestseller eq true><span class="red">*BEST SELLER*</span><br/></cfif>
<cfif recommended eq true or instructor eq true><span class="red">*RECOMMENDED*</span><br/></cfif>
<cfif outofstock>
	Sorry, this item is temporarily out of stock
<cfelseif Price eq 0>	
	Please call for pricing: #co_phone#
<cfelse>
	<cfif saleprice GT 0><span class="struck"></cfif>
	#DollarFormat(Price)#
	<cfif saleprice GT 0></span><br/>
	<span class="sale">On sale #DollarFormat(saleprice)#</span>
	</cfif>
</cfif>
<br/>
<a href="item.cfm?<cfif grouped>Groupid=#groupid#<cfelse>ItemID=#itemid#</cfif>">More info...<br/>
</div>
<cfset ctr=ctr+1>
</cfif>

<cfif Grouped and GRP NEQ NbrInGroup>
	<cfset GRP = GRP + 1>
<cfelse>
	<cfset GRP = 1>
</cfif>


</cfloop>
</cfloop>
<cfif ctr GT 0></div><script type="text/javascript">$("###iid# .imgbox").height(#maxh#);</script></cfif>
</cfoutput>
<br clear="all"/>
<p class="centered"><A HREF="#top">Back to the Top</A> | <A href="index.cfm">Back to the Main Page</A></p>

</div> <!--- data --->
<script type="text/javascript">
	$(document).ready(function() {
//		$(".imgbox img").each(function() {
//			var w=$(this).width();
//			var offset=(<cfoutput>#catwid#</cfoutput>-w)/2;
//			alert(offset);
//			$(this).css("left",offset);
//		});
/*		$(".itemrow").each(function() {
			var maxh=0;
			$(this).find("img").each(function() {
				if ($(this).height() > maxh) {
					maxh=$(this).height();
				}
			});
			$(this).find(".imgbox").height(maxh);
		});
*/
	});
</script>
<cfinclude template="Templates/bottom.cfm">