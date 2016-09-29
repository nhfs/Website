<cfif not isdefined("Form.CatName")>
	<cflocation url="addcategory.cfm">
</cfif>
<cfinclude template="topper.cfm"><P>
<!--- upload file ----->
<cfif val(CGI.CONTENT_LENGTH) gt 100000>
	The file your are attempting to upload is too large. It must
	be less than 100K.  Please go back and try a different file.
	<cfabort>
</cfif>

<cfif #isdefined("Form.CatPicture")# and #Form.CatPicture# gt "">
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
<cfelse>
	<cfset picname="">
</cfif>

<cfif Len(picname) GT 100><P>
	Your file name is too large.  Please rename the file and try again.
	<cflock name="FileLock" type="Exclusive" timeout="30">
	<cfset fn="#fileloc#" & Replace(picname, "/","\","All")>
	<cffile action="Delete"
		file=#fn#>
	</cflock>

	<cfabort>
</cfif>

<cfquery name="AddCat" datasource="#dsn#">
	Insert into Categories
		(CatID, CatName, CatPicture, CatMessage, CatDesc)
		Values ('#Form.CatID#', '#Form.CatName#', '#picname#', '#CatMessage#', '#CatDesc#')
</cfquery>

<cflocation url="addcategory.cfm?x=#Form.CatName#">