<cfinclude template="header.cfm">
<script type="text/javascript">
function checkform(fm) {
	var msg="";
	var err=false;
	var tchkd=false;
	for (var i=0; i<fm.type.length; i++) {
		if (fm.type[i].checked) {
			tchkd = true;
		}
	}

	if (fm.pname.value == "" && 
		(fm.plevel.selectedIndex > 0 || tchkd || fm.logourl.value > "" || typeof fm.oldlogo != "undefined" || 
		fm.purl.value > "" || fm.phone.value > "" || fm.pdesc.value > "")) {
		err=true;
		msg+="Please enter the partner's name.\n";
		fm.pname.focus();
	}

	if (fm.plevel.selectedIndex == 0 && fm.pname.value > "") {
		msg+="Please select a level.\n";
		if (!err) {
			fm.plevel.focus();
			err=true;
		}
	}
	
	if (!tchkd && fm.pname.value > "") {
		msg+="Please indicate the type of partner.\n";
		if (!err) {
			fm.type[0].focus();
			err=true;
		}
	}
	
	if (fm.plevel.selectedIndex > 2 && fm.plevel.selectedIndex < 6) {
		if ((fm.logourl.value == "" && typeof fm.oldlogo == "undefined") || (fm.logourl.value == "" && fm.delpic.checked)) {
			msg+="Please specify a logo for Advanced and above partners.\n";
			if (!err) {
				fm.logourl.focus();
				err=true;
			}
		}
	} 
	
	if (err) {
		alert(msg);
		return false;
	} else {
		return true;
	}
}
</script>
<cfquery name="GetParts" datasource="#dsn#">
	select pid, plevel, pname from partners order by
		plevel, pname
</cfquery>
<div class="sidebar">
	<a href="partners.cfm">New Business/Lodging Partner</a><br/>
		<cfset lvl="">
		<cfoutput query="GetParts">
			<cfif lvl neq plevel>
				<cfif lvl neq ""></div><br/></cfif>
				<cfset lvl=plevel>
				<a href="javascript:toggle('lev#lvl#')"><b>#levels[lvl]#</b></a>
				<div style="display: none;" id="lev#lvl#">
			</cfif>
			<a href="partners.cfm?pid=#pid#">#pname#</a><br/>
		</cfoutput>
		<cfif lvl neq ""></div></cfif>
</div>
<div class="form">
<h2>Update Partner Information</h2>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Partner information has been updated!</B><P>
		</P>
	</cfoutput>
</cfif>


<cfoutput>
<cfform action="part_update.cfm" method="POST" name="inst" enctype="multipart/form-data" scriptsrc="http://kite.boreal.org/cfide/scripts" onsubmit="return checkform(_CF_this)">

<cfif IsDefined("url.pid")>
	<cfquery name="GetPart" datasource="#dsn#">
		select * from partners where
			pid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.pid#">
	</cfquery>
	<cfif Getpart.recordcount GT 0>
		<cfoutput>
		<input type="hidden" name="pid" value="#url.pid#">
		</cfoutput>
	</cfif>
</cfif>

<cfquery name="getPage" datasource="#dsn#">
	select * from pagetext where pid='#part_pid#'
</cfquery>

<input type="submit" value="Save Partner Information">
<cfif IsDefined("getpart.pname")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the information for #Replace(GetPart.pname,"'","\'","all")#?')) { location.href='part_update.cfm?pid=#url.pid#&del=yes'; }" value="Delete Partner">
</cfif>
<br/><br/>
<label for="title">Page title:</label>
<cfif getPage.recordcount GT 0><cfset val=getPage.title><cfelse><cfset val=""></cfif>
<cfinput type="text" name="title" id="title" size="50" value="#val#" required="yes" message="Please enter the partner page title.">
<br/><br/>
<label for="pagetext">Page text:</label>
<cfif getpage.recordcount gt 0>
	<cfset val=Getpage.pagetext>
<cfelse>
	<cfset val="">
</cfif>
<cfmodule template="../../schoolstore/admin/fckeditor/fckeditor.cfm"
   	basePath="../../schoolstore/admin/fckeditor/"
	instanceName="pagetext"
	width="350"
	height="170"
	value="#val#"
	config="#conf#"
	toolbarset="nhousebasic"
>

<br/><br/>
<label for="plevel">Level:</label>
<select name="plevel" id="plevel">
	<option value="0">--Select--</option>
	<cfloop index="x" from="1" to="#arraylen(levels)#">
		<option value="#x#"<cfif isDefined("GetPart.plevel") and getPart.plevel eq x> selected</cfif>>#levels[x]#</option>
	</cfloop>
</select>

<label for="pname">Partner name:</label>
<cfif IsDefined("GetPart.pname")><cfset val=getPart.pname><cfelse><cfset val=""></cfif>
<input type="text" name="pname" id="pname" value="#val#" size="50" maxlength="80">
<br/><br/>

<label for="type">Partner type:</label>
<div class="input">
	<input type="radio" value="Business" name="type" id="btype" class="radio"<cfif isDefined("getPart.type") and getPart.type eq "Business"> checked</cfif>>
	<label class="col2" for="btype">Business</label><br/>
	<input type="radio" value="Lodging" name="type" id="ltype" class="radio"<cfif isDefined("getPart.type") and getPart.type eq "Lodging"> checked</cfif>>
	<label class="col2" for="ltype">Lodging</label>
</div>
<br/><br/>

<label for="logourl">Partner's Logo:</label>
<input type="file" value="Get Logo" size="40" name="logourl" id="logourl">
<cfif IsDefined("GetPart.logourl") and GetPart.logourl GT "">
	<input type="hidden" name="oldlogo" value="#GetPart.logourl#">
	<input type="hidden" name="oldh" value="#GetPart.logoh#">
	<br/><br/>
	<div class="col1">Current Logo:</div>
	<div class="col2"><IMG src="../courses/#GetPart.logourl#"></div>
	<br clear="all"/>
	<input type="checkbox" value="Yes" name="delpic" id="delpic" class="checkbox">
	<label for="delpic" class="checkbox">Delete Logo</label>
</cfif>
<br/>	<br/>

<label for="purl">Partner's Website:</label>
<cfif IsDefined("GetPart.purl") and GetPart.purl GT "">
	<cfset val=getPart.purl>
<cfelse>
	<cfset val="">
</cfif>
<input type="text" name="purl" id="purl" size="50" value="#val#">
<br/><br/>

<label for="phone">Partner's Phone:</label>
<cfif IsDefined("GetPart.phone") and GetPart.phone GT "">
	<cfset val=getPart.phone>
<cfelse>
	<cfset val="">
</cfif>
<cfinput type="text" name="phone" id="phone" size="50" value="#val#" validate="telephone" message="Please enter the phone number in the format 111-222-3333">
<br/><br/>

<label for="pdesc">Description:</label>
<cfif IsDefined("GetPart.pdesc") and GetPart.pdesc GT "">
	<cfset val=getPart.pdesc>
<cfelse>
	<cfset val="">
</cfif>
<input type="text" value="#val#" name="pdesc" id="pdesc" size="50" maxlength="50">

<br clear="all"/><br/>
<input type="submit" value="Save Partner Information">
<cfif IsDefined("getpart.pname")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the information for #Replace(GetPart.pname,"'","\'","all")#?')) { location.href='part_update.cfm?pid=#url.pid#&del=yes'; }" value="Delete Partner">
</cfif>
</cfform>
</cfoutput>
</div>
<br clear="all"/><br/>


<cfinclude template="footer.cfm">