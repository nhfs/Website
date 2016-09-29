<cfinclude template="Templates/topper.cfm">
<script type="text/javascript" src="../courses/admin/jquery/js/jquery-1.4.2.min.js"></script>
<div class="data wide">

<cffunction name="getImg">
	<cfargument name="picture" required="Yes" type="string">
	<cfargument name="thumb" required="Yes" type="string">
	<cfargument name="alt" type="string" default="">
	<cfoutput>
	<cfif thumb eq "" and picture GT "" and fileExists("#fileloc##picture#")>
		<!--- resize picture --->
		<cfset imageCFC = createObject("component","image")>
		<!--- get current dimensions --->
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
		<img src="#picture#" alt="#alt#" title="#alt#" width="#w#" height="#h#" border="0"/>
	<cfelseif thumb GT "">
		<img src="#thumb#" alt="#alt#" title="#alt#" border="0"/>
	</cfif>
	</cfoutput>

</cffunction>
<div class="homeside">
<!--- handcrafted items box --->
<cfquery name="GetHC" datasource="#dsn#">
	select groupname, thumb, picture from items i inner join (groups g
		INNER JOIN (_lines l 
		INNER JOIN subcats s on l.scatid = s.scatid)
		ON g.lineid = l.lineid)
		on i.groupid = g.groupid
		where s.scatid=#handcraftsid#
</cfquery>
<cfset ctr=1>
<cfset items=arrayNew(1)>
<cfloop query="getHC">
	<cfset items[ctr]=structNew()>
	<cfset items[ctr].groupname=groupname>
	<cfset items[ctr].thumb=thumb>
	<cfset items[ctr].picture=picture>
	<cfset ctr=ctr+1>
</cfloop>
<cfif ArrayLen(items) GT 0>
<cfoutput>
	<div class="pbox">
		<!--- pull a random item--->
		<a href="catalog.cfm?catid=#handcraftcid#&scatid=#handcraftsid#">
		<b>Handcrafted Items</b><br/>
		</a>
		<cfset rnd=ceiling(rand()*ArrayLen(items))>
		<a href="catalog.cfm?catid=#handcraftcid#&scatid=#handcraftsid#">
		<cfset foo=getImg(items[rnd].picture, items[rnd].thumb, items[rnd].groupname)>
		</a>
		<br/>
		<a href="catalog.cfm?catid=#handcraftcid#&scatid=#handcraftsid#">
		#items[rnd].groupname#
		</a>

	</div>
</cfoutput>
</cfif>

<!--- power boxes --->
<cfquery name="GetPB" datasource="#dsn#">
	select * from pbox order by bid
</cfquery>
<cfoutput query="GetPB">
	<div class="pbox">
	<a href="#url#" title="#title#"><b>#title#</b></a><br/>
	<cfif photo GT "">
		<a href="#url#" title="#title#"><img src="#photo#" alt="#title#" title="#title#" border="0"></a><br/>
	</cfif>
	</div>
</cfoutput>

</div>

<div class="homemid">
	<cfquery name="GetData" datasource="#dsn#">
		select * from mainpage_data
	</cfquery>
	<cfif GetData.RecordCount GT 0>
		<cfoutput query="Getdata">
		<cfif mp_photo1 GT "" or mp_photo2 GT "">
			<cfif mp_photo1 GT ""><img src="#mp_photo1#" border="0" alt="North House Folk School Store">
			<cfelseif mp_photo2 GT ""><img src="#mp_photo2#" border="0" alt="North House Folk School Store">
			</cfif>				
		</cfif>
		<H2>Welcome to our Online School Store</H2>
		#mp_text#
		<cfif mp_photo1 GT "" and mp_photo2 GT "">
			<img src="#mp_photo2#" border="0" alt="North House Folk School Store">
		</cfif>
		</cfoutput>
	</cfif>
	<div class="highlighted"><a href="catalog.cfm?featured=sale">ON SALE</a></div>
</div>

<div class="homeside">
<!--- instructor and staff of month --->
<cfquery name="Getmonths" datasource="#dsn#">
	select * from monthly_data where showbox
		order by pid desc
</cfquery>
<cfoutput query="Getmonths">
	<div class="pbox">
	<cfif pid eq staff_month><cfset feat="recommended"><cfelseif pid eq inst_month><cfset feat="instructor"><cfelse>
		<cfset feat="tools"></cfif>
	<a href="catalog.cfm?featured=#feat#"><b>
	<cfif pid eq staff_month>Staff Picks<cfelseif pid eq inst_month>Instructor of the Month<cfelse>Tools for the Season</cfif></b></a><br/>
	<a href="catalog.cfm?featured=#feat#">
	<cfquery name="getPhoto" datasource="#dsn#">
		select photo, caption from monthly_pics where pid=#pid# order by pord limit 1
	</cfquery>
	<cfif pid eq tool_month>
		<cfset capt=GetPhoto.caption>
	<cfelse>
		<cfset capt=fname & " " & lname>
	</cfif>
	<cfset foo=getImg(getPhoto.photo, "", capt)>
	</a><br/>
	<a href="catalog.cfm?featured=#feat#">#capt#</a>
	</div>

</cfoutput>

<!-- random item --->
<cfquery name="getItems" datasource="#dsn#">
	select groupname, itemid, thumb, picture from 
		groups inner join items on groups.groupid = items.groupid
</cfquery>

<cfset ctr=1>
<cfset items=arrayNew(1)>
<cfloop query="getItems">
	<cfset items[ctr]=structNew()>
	<cfset items[ctr].groupname=groupname>
	<cfset items[ctr].thumb=thumb>
	<cfset items[ctr].picture=picture>
	<cfset items[ctr].itemid=itemid>
	<cfset ctr=ctr+1>
</cfloop>
<cfif ArrayLen(items) GT 0>
<cfoutput>
	<div class="pbox">
		<!--- pull a random item--->
		<cfset rnd=ceiling(rand()*ArrayLen(items))>
		<a href="item.cfm?itemid=#items[rnd].itemid#"><b>Selection from Our Store</b></a><br/>
		<a href="item.cfm?itemid=#items[rnd].itemid#">
		<cfset foo=getImg(items[rnd].picture, items[rnd].thumb, items[rnd].groupname)>
		</a>
		<br/>
		<a href="item.cfm?itemid=#items[rnd].itemid#">
		#items[rnd].groupname#
		</a>
	</div>
</cfoutput>
</cfif>


</div>
<script type="text/javascript">
// equalize pbox heights
$(function() {
	var maxh=0;
	$(".pbox").each(function() {
		if ($(this).height() > maxh) {
			maxh=$(this).height();
		}
	});
	$(".pbox").each(function() {
		thish=$(this).height();
		if (thish < maxh) {
			$(this).css("padding-top", (maxh-thish)/2);
			$(this).css("padding-bottom", (maxh-thish)/2);
		};
	});
	
	if ($('.homemid').height() < (maxh * 3)) {
		$('.homemid').height((maxh * 3) + 20);
	}
});
		
</script>

<BR clear="all"/>
</div>



<cfinclude template="Templates/bottom.cfm">