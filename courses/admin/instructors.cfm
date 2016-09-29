<cfinclude template="header.cfm">
<cfquery name="GetInsts" datasource="#dsn#">
	select iid, fname, lname from instructors order by
		lname
</cfquery>
<div class="sidebar">
	<a href="instructors.cfm">New Instructor</a><br/>
		<cfset alpha="">
		<cfoutput query="GetInsts">
			<cfif alpha neq left(lname,1)>
				<cfif alpha neq ""></div><br/></cfif>
				<cfset alpha=left(lname,1)>
				<a href="javascript:toggle('#alpha#')"><b>#alpha#</b></a>
				<div style="display: none;" id="#alpha#">
			</cfif>
			<a href="instructors.cfm?iid=#iid#">#fname# #lname#</a><br/>
		</cfoutput>
		<cfif alpha neq ""></div></cfif>
</div>
<div class="form">
<h2>Update Instructor Information</h2>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Instructor information has been updated!</B><P>
		</P>
	</cfoutput>
</cfif>


<cfoutput>
<cfform action="inst_update.cfm" method="POST" name="inst" enctype="multipart/form-data" scriptsrc="http://kite.boreal.org/cfide/scripts">

<cfif IsDefined("url.iid")>
	<cfquery name="GetInst" datasource="#dsn#">
		select * from instructors where
			iid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.iid#">
	</cfquery>
	<cfif GetInst.recordcount GT 0>
		<cfoutput>
		<input type="hidden" name="iid" value="#url.iid#">
		</cfoutput>
	</cfif>
</cfif>

<input type="submit" value="Save Instructor Information">
<cfif IsDefined("getInst.fname")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the information for #Replace(GetInst.fname,"'","\'","all")# #Replace(GetInst.lname,"'","\'","all")#? If there are any courses for this instructor, the instructor name will be removed from those courses.')) { location.href='inst_update.cfm?iid=#url.iid#&del=yes'; }" value="Delete Instructor">
</cfif>
<br/><br/>
<label for="fname">Instructor name (first last):</label>
<cfif IsDefined("GetInst.fname")><cfset val=getInst.fname><cfelse><cfset val=""></cfif>
<cfinput type="text" name="fname" id="fname" size="18" value="#val#" maxlength="30">
<cfif IsDefined("GetInst.lname")><cfset val=getInst.lname><cfelse><cfset val=""></cfif>
<cfinput type="text" name="lname" id="lname" size="25" value="#val#" maxlength="50" required="yes" message="Please enter the instructor's name">

<br/><br/>

<label for="photo">Instructor photo:</label>
<input type="file" value="Get Photo" size="40" name="photo" id="photo">
<cfif IsDefined("GetInst.photo") and GetInst.Photo GT "">
	<br/><br/>
	<div class="col1">Current Photo:</div>
	<div class="col2"><IMG src="../courses/#GetInst.photo#"></div>
	<br clear="all"/>
	<input type="checkbox" value="Yes" name="delpic" id="delpic" class="checkbox">
	<label for="delpic" class="checkbox">Delete Photo</label>
</cfif>
<br/>	<br/>

<label for="home">Home:</label>
<cfif IsDefined("GetInst.home") and GetInst.home GT "">
	<cfset val=GetInst.home>
<cfelse>
	<cfset val="">
</cfif>
<input type="text" name="home" id="home" size="50" value="#val#">
<br/><br/>

<label for="profile">Profile:</label>
<cfif IsDefined("GetInst.profile") and getInst.profile GT "">
	<cfset val=GetInst.profile>
<cfelse>
	<cfset val="">
</cfif>
<cfmodule template="../../schoolstore/admin/fckeditor/fckeditor.cfm"
   	basePath="../../schoolstore/admin/fckeditor/"
	instanceName="profile"
	width="350"
	height="350"
	value="#val#"
	config="#conf#"
	toolbarset="nhouse"
>
<br/><br/>
<label for="url">Instructor's website:</label>
<cfif IsDefined("GetInst.url") and GetInst.url GT "">
	<cfset val=GetInst.url>
<cfelse>
	<cfset val="">
</cfif>
<input type="text" name="url" id="url" size="50" value="#val#">
<br><br>
<label for="guest">Mark as a guest instructor?</label>
<input type="checkbox" id="guest" value="yes" name="guest"<cfif isDefined("getInst.guest") and getInst.guest eq true> checked</cfif>>
<br clear="all"/><br/>
<input type="submit" value="Save Instructor Information">
<cfif IsDefined("getInst.fname")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the information for #Replace(GetInst.fname,"'","\'","all")# #Replace(GetInst.lname,"'","\'","all")#? If there are any courses for this instructor, the instructor name will be removed from those courses.')) { location.href='inst_update.cfm?iid=#url.iid#&del=yes'; }" value="Delete Instructor">
</cfif>
</cfform>
</cfoutput>
</div>
<br clear="all"/><br/>


<cfinclude template="footer.cfm">