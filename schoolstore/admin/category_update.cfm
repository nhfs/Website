<cfif not isdefined("Form.CatName") OR NOT isDefined("URL.CatID")>
	<cflocation url="addcategory.cfm">
</cfif>
<cfinclude template="topper.cfm"><P>
<!--- upload file ----->
<cfif val(CGI.CONTENT_LENGTH) gt 100000>
	The file your are attempting to upload is too large. It must
	be less than 100K.  Please go back and try a different file.
	<cfabort>
</cfif>

<cfquery datasource="#dsn#" name="GetPic">
	select CatPicture, CatID from Categories
		Where CatID='#URL.CatID#'
</cfquery>


<!--- load new picture if deisgnated ----->
<cfif IsDefined("Form.CatPicture") and Form.CatPicture GT "">
	<cffile action="Upload" 
		filefield="Form.CatPicture" 
		Destination="#fileloc#Images\"	
		accept="image/gif,image/jpg,image/jpeg,image/pjpeg"
		nameconflict="MAKEUNIQUE">
		
	<cfset modname=Replace(CFFILE.ServerFile, " ", "_", "All")>
	<cfset modname=Replace(modname, "&", "_", "All")>
	<cfset modname=Replace(modname, "$", "_", "All")>
	<cfset modname=Replace(modname, ",", "_", "All")>
	<cfset modname=Replace(modname, "-", "_", "All")>
	<cfset oldname="#fileloc#Images\" & #CFFILE.ServerFile#>
	<cfset newname="#fileloc#Images\" & #modname#>
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
	<cfset idx="1">
	<cfloop condition="FileExists('#newname#')">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#Images\" & modname>
		<cfset idx=idx+1>
	</cfloop>
	
	<cffile action="Rename"
		source="#oldname#" 
		destination="#newname#">
	</cflock>
	<cfset picname="Images/" & #modname#>
	
	<cfif Len(picname) GT 100>
		Your file name is too large.  Please rename the file and try again.
		<cflock name="FileLock" type="Exclusive" timeout="30">
		<cfset fn="#fileloc#" & Replace(picname, "/","\","All")>
		<cffile action="Delete"
			file=#fn#>
		</cflock>

		<cfabort>
	</cfif>

</cfif>

<cfquery datasource="#dsn#" name="UpdCat">
	Update Categories
		Set CatName='#Form.CatName#',
			CatID='#Form.CatID#',
			CatMessage='#Form.CatMessage#',
			CatDesc='#Form.CatDesc#'
			<cfif Isdefined("Form.CatPicture") and Form.CatPicture GT "">
				, CatPicture='#picname#'
			<cfelseif isdefined("Form.DelPic") and Form.DelPic eq "Yes">
				, CatPicture=''	
			</cfif>
		WHERE CatID='#URL.CatID#'
</cfquery>
<cfif GetPic.Catid neq form.catid>
	<!--- need to update line --->
	<cfquery name="UpdLine" datasource="#dsn#">
		update _lines set Catid='#form.catid#'
			where catid='#GetPic.catId#'
	</cfquery>
</cfif>

<cfif (#isdefined("Form.CatPicture")# and #Form.CatPicture# gt "") 
	or (isdefined("Form.DelPic") and Form.DelPic eq "Yes")>
	<!--- delete old picture if it's there ---->
	<cfif GetPic.CatPicture GT "">
		<cfset fn="#fileloc#" & Replace(GetPic.CatPicture, "/","\","All")>
		<cflock name="FileLock" type="Exclusive" timeout="30">
		<cffile action="Delete"
			file=#fn#>
		</cflock>
	</cfif>
</cfif>
<cflocation url="addcategory.cfm?y=#Form.CatName#">