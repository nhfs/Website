<cfinclude template="header.cfm">
<script type="text/javascript">
function checkform(fm) {
	err=false;
	msg="";
	if (fm.photourl.value == "" && fm.newphoto) {
		msg+="Please specify a photo\n";
		fm.photourl.focus();
		err=true;
	}
	
	if (err) {
		alert (msg);
		return false;
	} else {
		return true;
	}
}
</script>
<div class="sidebar">
	
</div>
<div class="form">
<h2>Update Footer Photo</h2>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Photo has been updated!</B><P>
		</P>
	</cfoutput>
</cfif>


<cfoutput>
<cfform name="part" action="photo_update.cfm" method="POST" scriptsrc="http://kite.boreal.org/cfide/scripts" enctype="multipart/form-data" onsubmit="return checkform(_CF_this);">

<cfquery name="GetPhoto" datasource="#dsn#">
	select * from footerphoto
</cfquery>
<cfif GetPhoto.recordcount EQ 0>
	<cfset GetPhoto.photourl="">
</cfif>

<label for="photourl">Upload Photo:</label>
<input type="file" name="photourl" id="photourl" size="50" required="Yes" value="#getphoto.photourl#">
<br/>
<cfif GetPhoto.Photourl GT "">
	<br/><br/>
	<div class="col1">Current Photo:</div>
	<div class="col2"><IMG src="../courses/#GetPhoto.photourl#"></div>
<cfelse>
	<input type="hidden" name="newphoto" value="yes">
</cfif>
<br/>	<br/>

<input type="submit" value="Save Photo">
</cfform>
</cfoutput>
</div>
<br clear="all"/><br/>
<cfinclude template="footer.cfm">