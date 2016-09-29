<div class="sidebar"><cfset scid=0>
<cfif Isdefined("url.catid")>
<!--- display subcategories in sidebar --->
	<cfquery name="getScats" datasource="#dsn#">
		select distinct sc.*, l.linename, l.lineid from ((subcats sc inner join _lines l on sc.scatid = l.scatid)
			inner join groups g on l.lineid = g.lineid) inner join items i on g.groupid = i.groupid
			where catid='#url.catid#' and not outofstock
			order by scatorder, scatname, lineorder, linename
	</cfquery>
	<cfoutput query="getScats">
		<cfif scid neq scatid>
			<cfif scid neq 0></div></div></cfif>
			<div class="sb_button" onMouseover="drop('sc#scatid#')" onmouseout="shrink('sc#scatid#')">
				<div class="buttontop"></div>
				<div class="main_button<cfif IsDefined("url.scatid") and url.scatid eq scatid> clicked</cfif>">
				<a href="catalog.cfm?catid=#catid#&scatid=#scatid#">#scatname#</a>
				<!---div class="buttonbot"></div--->
				</div>
			<div class="submenu" id="sc#scatid#">
			<cfset scid=scatid>
		</cfif>
		<div class="menuitem"><a href="catalog.cfm?catid=#url.catid#&scatid=#scatid####lineid#">#linename#</a></div>
	</cfoutput>
	</div> <!--- last submenu --->
	</div> <!--- last sb_button --->
<cfelse>
	<!--- must be the featured page - make features sidebar --->
	<div class="sb_button<cfif url.featured eq "best"> clicked</cfif>">
	<div class="buttontop"></div>
	<a href="catalog.cfm?featured=best">Best Sellers</a>
	<!---div class="buttonbot">&nbsp;</div--->
	</div>
	<div class="sb_button<cfif url.featured eq "new"> clicked</cfif>">
	<div class="buttontop"></div>
	<a href="catalog.cfm?featured=new">New Arrivals</a>
	<!---div class="buttonbot">&nbsp;</div--->
	</div>
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
	<div class="sb_button<cfif url.featured eq feat> clicked</cfif>">
	<div class="buttontop"></div>
	<a href="catalog.cfm?featured=#feat#">#fname#</a>
	<!---div class="buttonbot">&nbsp;</div--->
	</div>
</cfoutput>
	
	<div class="sb_button<cfif url.featured eq "gift"> clicked</cfif>">
	<div class="buttontop"></div>
	<a href="catalog.cfm?featured=gift">Gift Certificates</a>
	<!---div class="buttonbot">&nbsp;</div--->
	</div>
	<div class="sb_button<cfif url.featured eq "sale"> clicked</cfif>">
	<div class="buttontop"></div>
	<a href="catalog.cfm?featured=sale">On Sale</a>
	<!---div class="buttonbot">&nbsp;</div--->
	</div>
</cfif>
</div>
