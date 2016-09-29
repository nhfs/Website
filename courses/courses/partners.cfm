<cfinclude template="templates/header.cfm">
<cfquery name="getText" datasource="#dsn#">
	select * from pagetext where pid='#part_pid#'
</cfquery>
<cfif getText.RecordCount GT 0>
<cfoutput>
<h2>#getText.title#</h2>
<p>#getText.pagetext#</p>
</cfoutput>
<cfelse>
<h2>North House's Lodging and Business Partners</h2>
<p>
Below are local businesses and lodging establishments who have partnered with the North House Folk School.
Please consider staying with one of our partners or stopping by their place during your stay in the 
Grand Marais area.
</p>
</cfif>
<!--- update partner logo heights --->
<cfquery name="geth" datasource="#dsn#">
	select logourl, logoh, pid from partners
		where logourl > "" and logoh = 0
</cfquery>
<cfset imageCFC = createObject("component","image")>
<cfloop query="geth">
	<!--- get current dimensions --->
	<cfif fileExists("#fileloc##logourl#")>
		<cfset imgInfo = imageCFC.getImageInfo("", "#fileloc##logourl#")>
		<cfquery name="seth" datasource="#dsn#">
			update partners set logoh=#imgInfo.height# where pid=#pid#
		</cfquery>
	</cfif>
</cfloop>

<cfquery name="GetPart" datasource="#dsn#">
	select if(plevel=#foundation#,2,1) as sorter, p.* from partners p
		order by sorter, plevel desc, porder
</cfquery>

<cfset lvl=0>
<cfset photos=ArrayNew(1)>
<div class="col1">
<cfset ctr=1>
<cfoutput query="GetPart">
	<cfif plevel neq lvl>
	<cfquery name="getmax" dbtype="query">
		select max(logoh) as maxh from getPart where plevel=#plevel#
	</cfquery>
	
	<cfif lvl GT 0><cfif ctr mod 3 neq 1><br clear="all"/></cfif></div></div><br clear="all"/></div></cfif>
		<div class="partbox"><div class="banner"<cfif plevel eq foundation> id="found"</cfif>>#levels[plevel]#</div>
		<div class="partline"><div class="partitems">
		
		<cfset lvl=plevel>
		<cfset ctr=1>
	</cfif>
	<div class="partner">
	<cfif purl gt ""><a href="#purl#" target="_new"><cfif levels[lvl] neq "Intermediate" and levels[lvl] neq "Basic" and logourl GT "">
	<div style="padding-top: #getmax.maxh-logoh#px">
	<img src="#logourl#" alt="#pname#" border="0"></div>
	</cfif></cfif>
	<b>#pname#</b><br/><cfif purl gt ""></a>
	
	<a href="#purl#" target="_new"></cfif>
	<cfif levels[lvl] eq "Sponsor" or levels[lvl] eq "Major Sponsor"><div class="pdesc">#pdesc#</div></cfif>
	<cfif levels[lvl] neq "Foundation Support">#phone#</a></cfif>
	</div>
	<cfif ctr mod 3 eq 0><br clear="all"/></div><br clear="all"/></div><div class="partline"><div class="partitems"></cfif>
	<cfset ctr=ctr+1>
</cfoutput>
<cfif lvl GT 0><cfif ctr mod 3 neq 1><br clear="all"/></cfif></div></div><br clear="all"/></div></cfif>
</div>

<cfquery name="getPhotos" datasource="#dsn#">
	select photo, c.cid, name from courses c INNER JOIN course_photos cp ON c.cid=cp.cid
</cfquery>
<cfset ctr=1>
<cfloop query="getPhotos">
	<cfset photos[ctr]=StructNew()>
	<cfset photos[ctr].photo=photo>
	<cfset photos[ctr].cid=cid>
	<cfset photos[ctr].name=name>
	<cfset ctr=ctr+1>
</cfloop>
<cfif ArrayLen(photos) GT 0>
<cfoutput>
	<div class="col2">

		<cfif ArrayLen(photos) le 5>
			<cfloop index="x" from="1" to="#arrayLen(photos)#">
				<a href="course.cfm?cid=#photos[x].cid#"><img src="#photos[x].photo#" alt="#photos[x].name#" border="0"></a><br/><br/>
			</cfloop>
		<cfelse>
		<!--- pull 5 random photos --->
			<cfset used="">
			<cfloop index="x" from="1" to="5">
				<cfset rnd=ceiling(rand()*ArrayLen(photos))>
				<cfloop condition="ListFind(used,rnd) GT 0">
					<cfset rnd=ceiling(rand()*ArrayLen(photos))>
				</cfloop>
				<a href="course.cfm?cid=#photos[rnd].cid#"><img src="#photos[rnd].photo#" alt="#photos[rnd].name#" border="0"></a><br/><br/>
				<cfset used=used & "," & rnd>
			</cfloop>		
		</cfif>
	
	</div>
</cfoutput>
</cfif>

<br clear="all"/><br/>
<cfinclude template="templates/footer.cfm">