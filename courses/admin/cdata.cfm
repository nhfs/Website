<cfinclude template="header.cfm">
<script type="text/javascript">
function checkform(fm) {
	var msg="";
	var err=false;

/*	if (fm.etext.value <= "") {
		msg+="Please enter the text for this item.\n";
		if (!err) {
			fm.etext.focus();
			err=true;
		}
	}*/
	
	if (err) {
		alert(msg);
		return false;
	} else {
		return true;
	}
}
</script>
<cfquery name="GetData" datasource="#dsn#">
	select * from course_extra
</cfquery>
<div class="sidebar">
	<a href="cdata.cfm">Add a bullet</a><br/>
	<cfoutput query="GetData">
		<a href="cdata.cfm?eid=#eid#">#etitle#</a><br/>
	</cfoutput>
</div>
<div class="form">
<h2>Update Information at Bottom of Course Pages</h2>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>General course information has been updated!</B><P>
		</P>
	</cfoutput>
</cfif>


<cfoutput>
<cfform action="cdata_update.cfm" method="POST" name="inst" scriptsrc="http://kite.boreal.org/cfide/scripts" onsubmit="return checkform(_CF_this)">

<cfif IsDefined("url.eid")>
	<cfquery name="GetInfo" datasource="#dsn#">
		select * from course_extra where
			eid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.eid#">
	</cfquery>
	<cfif GetInfo.recordcount GT 0>
		<cfoutput>
		<input type="hidden" name="eid" value="#url.eid#">
		</cfoutput>
	</cfif>
</cfif>

<input type="submit" value="Save Information">
<cfif IsDefined("getinfo.etitle")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the information for #Replace(getinfo.etitle,"'","\'","all")#?')) { location.href='cdata_update.cfm?eid=#url.eid#&del=yes'; }" value="Delete Information">
</cfif>
<br/><br/>
<label for="title">Title:</label>
<cfif isDefined("getinfo.etitle")><cfset val=getinfo.etitle><cfelse><cfset val=""></cfif>
<cfinput type="text" name="etitle" id="etitle" size="50" value="#val#" required="yes" message="Please enter the title.">
<br/><br/>
<label for="etext">Text:</label>
<cfif isDefined("getinfo.etext")>
	<cfset val=Getinfo.etext>
<cfelse>
	<cfset val="">
</cfif>
<cfmodule template="../../schoolstore/admin/fckeditor/fckeditor.cfm"
   	basePath="../../schoolstore/admin/fckeditor/"
	instanceName="etext"
	width="350"
	height="170"
	value="#val#"
	config="#conf#"
	toolbarset="nhousebasic"
>

<br/><br/>
<br clear="all"/><br/>
<input type="submit" value="Save Information">
<cfif IsDefined("getinfo.etitle")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the information for #Replace(Getinfo.etitle,"'","\'","all")#?')) { location.href='cdata_update.cfm?eid=#url.eid#&del=yes'; }" value="Delete Information">
</cfif>
</cfform>
</cfoutput>
</div>
<br clear="all"/><br/>


<cfinclude template="footer.cfm">