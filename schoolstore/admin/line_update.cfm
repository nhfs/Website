<cfif not IsDefined("form.LineName") or not isdefined("URL.LineID")>
	<cflocation url="addline.cfm">
</cfif>

<!--- upload file ----->
<cfif val(CGI.CONTENT_LENGTH) gt 100000>
	The file your are attempting to upload is too large. It must
	be less than 100K.  Please go back and try a different file.
	<cfabort>
</cfif>

<cfquery datasource="#dsn#" name="GetPic">
	select LinePicture from _Lines
		Where LineID=#URL.LineID#
</cfquery>

<!--- load new picture if designated ----->
<cfif IsDefined("Form.LinePicture") and Form.LinePicture GT "">
	<cffile action="Upload" 
		filefield="Form.LinePicture" 
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
	<cfset idx="1">
	<cfloop condition="FileExists('#newname#')">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#Images\" & modname>
		<cfset idx=idx+1>
	</cfloop>
	
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
	<cffile action="Rename"
		source="#oldname#" 
		destination="#newname#">
	</cflock>
	<cfset picname="Images/" & #modname#>

	<cfif Len(picname) GT 100><P>
		Your file name is too large.  Please rename the file and try again.
		<cflock name="FileLock" type="Exclusive" timeout="30">
		<cfset fn="#fileloc#" & Replace(picname, "/","\","All")>
		<cffile action="Delete"
			file=#fn#>
		</cflock>

		<cfabort>
	</cfif>
</cfif>

<cfquery name="UpdLine" datasource="#dsn#">
	Update _Lines
		Set sCatID=#Form.sCatID#,		
		 	LineName='#Form.LineName#', 
			LineDesc='#Form.LineDesc#'
			<cfif Isdefined("Form.LinePicture") and Form.LinePicture GT "">
				, LinePicture='#picname#'
			<cfelseif isdefined("Form.DelPic") and Form.DelPic eq "Yes">
				, LinePicture=''	
			</cfif>
			<cfif Val(Form.LineOrder) GT 0>
				, LineOrder=#Form.LineOrder#
			<cfelse>
				, LineOrder=0
			</cfif>
				, taxable=#form.taxable#
		Where LineID =#URL.LineID#
</cfquery>

<!--- now, delete old picture if it's there ---->
<cfif (#isdefined("Form.LinePicture")# and #Form.LinePicture# gt "") 
	or (isdefined("Form.DelPic") and Form.DelPic eq "Yes")>
	<cfif GetPic.LinePicture GT "">
		<cfset fn="#fileloc#" & Replace(GetPic.LinePicture, "/","\","All")>
		<cflock name="FileLock" type="Exclusive" timeout="30">
		<cffile action="Delete"
			file=#fn#>
		</cflock>
	</cfif>
</cfif>

<cflocation url="addline.cfm?y=#form.LineName#">