<div class="sidebar"><cfset scid=0>
<cfif Isdefined("url.catid")>
<!--- display subcategories in sidebar --->
	<cfquery name="getScats" datasource="#dsn#">
		select sc.*, l.linename, l.lineid from subcats sc inner join _lines l on sc.scatid = l.scatid
			where catid='#url.catid#'
			order by scatorder, scatname, lineorder, linename
	</cfquery>
	<cfoutput query="getScats">
		<cfif scid neq scatid>
			<cfif scid neq 0></div></div></div></cfif>
			<div class="sb_button<cfif IsDefined("url.scatid") and url.scatid eq scatid> clicked</cfif>" onMouseover="drop('sc#scatid#')" onmouseout="shrink('sc#scatid#')">
				<div class="buttontop"></div>
				<a href="catalog.cfm?catid=#catid#&scatid=#scatid#">#scatname#</a>
				<div class="buttonbot"></div>
			<div class="menucont">
			<div class="submenu" id="sc#scatid#">
			<cfset scid=scatid>
		</cfif>
		<div class="menuitem"><a href="catalog.cfm?catid=#url.catid#&scatid=#scatid####lineid#">#linename#</a></div>
	</cfoutput>
	</div> <!--- last submenu --->
	</div> <!--- last menucont --->
	</div> <!--- last sb_button --->
<cfelse>
	<!--- must be the featured page - make features sidebar --->
	<div class="sb_button<cfif url.featured eq "best"> clicked</cfif>">
	<div class="buttontop">&nbsp;</div>
	<a href="catalog.cfm?featured=best">Best Sellers</a>
	<div class="buttonbot">&nbsp;</div>
	</div>
	<div class="sb_button<cfif url.featured eq "new"> clicked</cfif>">
	<div class="buttontop">&nbsp;</div>
	<a href="catalog.cfm?featured=new">New Arrivals</a>
	<div class="buttonbot">&nbsp;</div>
	</div>
	<div class="sb_button<cfif url.featured eq "recommended"> clicked</cfif>">
	<div class="buttontop">&nbsp;</div>
	<a href="catalog.cfm?featured=recommended">Recommended</a>
	<div class="buttonbot">&nbsp;</div>
	</div>
	<div class="sb_button<cfif url.featured eq "gift"> clicked</cfif>">
	<div class="buttontop">&nbsp;</div>
	<a href="catalog.cfm?featured=recommended">Gift Certificates</a>
	<div class="buttonbot">&nbsp;</div>
	</div>
</cfif>
</div>
