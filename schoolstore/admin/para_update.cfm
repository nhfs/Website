<cfif not IsDefined("form.mp_text")>
	<cflocation url="additem.cfm">
</cfif>
<!--- first, upload the picture file, if there --->
<cfif val(CGI.CONTENT_LENGTH) gt 300000>
	The photos your are attempting to upload are too large. They should each 
	be less than 150K.  Please go back and try different files.
	<cfabort>
</cfif>

<cfquery datasource="#dsn#" name="GetData">
	select mp_photo1, mp_photo2 from mainpage_data
</cfquery>

<!--- load new picture if designated ----->
<cfif IsDefined("Form.mp_photo1") and Form.mp_photo1 GT "">
	<cffile action="Upload" 
		filefield="Form.mp_photo1" 
		Destination="#fileloc#Images\"
		accept="image/gif,image/jpg,image/jpeg,image/pjpeg"
		nameconflict="MAKEUNIQUE">

	<cfset modname=Replace(CFFILE.ServerFile, " ", "_", "All")>
	<cfset modname=Replace(modname, "&", "_", "All")>
	<cfset modname=Replace(modname, "$", "_", "All")>
	<cfset modname=Replace(modname, ",", "_", "All")>		
	<cfset modname=Replace(modname, "-", "_", "All")>
	<cfset modname=left(modname, 90)>	
	<cfset oldname="#fileloc#images\" & #CFFILE.ServerFile#>
	<cfset newname="#fileloc#images\" & #modname#>
	<cfset idx="1">
	<cfloop condition="FileExists('#newname#') and oldname neq newname">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#images\" & modname>
		<cfset idx=idx+1>
	</cfloop>
	
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
	<cffile action="Rename"
		source="#oldname#" 
		destination="#newname#">
	</cflock>
	
	<!--- resize photo --->
	<cf_resizeimg
		filepath="#fileloc#/images"
		filename="#modname#"
		targetmax="#mainwid#"
		maxtype="w">
	<cfif ResizeError>
		<cfoutput>#ErrMessage#</cfoutput>
		<cffile action="Delete"
			file="#newname#">
		
		<cfabort>
	</cfif>

	<cfset picname="images/" & #modname#>

	
</cfif>

<!--- load second picture if designated ----->
<cfif IsDefined("Form.mp_photo2") and Form.mp_photo2 GT "">
	<cffile action="Upload" 
		filefield="Form.mp_photo2" 
		Destination="#fileloc#Images\"	
		accept="image/gif,image/jpg,image/jpeg,image/pjpeg"
		nameconflict="MAKEUNIQUE">

	<cfset modname=Replace(CFFILE.ServerFile, " ", "_", "All")>
	<cfset modname=Replace(modname, "&", "_", "All")>
	<cfset modname=Replace(modname, "$", "_", "All")>
	<cfset modname=Replace(modname, ",", "_", "All")>		
	<cfset modname=Replace(modname, "-", "_", "All")>
	<cfset modname=left(modname, 90)>		
	<cfset oldname="#fileloc#images\" & #CFFILE.ServerFile#>
	<cfset newname="#fileloc#images\" & #modname#>
	<cfset idx="1">
	<cfloop condition="FileExists('#newname#') and newname neq oldname">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#images\" & modname>
		<cfset idx=idx+1>
	</cfloop>
	
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
	<cffile action="Rename"
		source="#oldname#" 
		destination="#newname#">
	</cflock>
	
	<!--- resize photo --->
	<cf_resizeimg
		filepath="#fileloc#/images"
		filename="#modname#"
		targetmax="#mainwid#"
		maxtype="w">
	<cfif ResizeError>
		<cfoutput>#ErrMessage#</cfoutput>
		<cffile action="Delete"
			file="#newname#">
		
		<cfabort>
	</cfif>

	<cfset picname2="Images/" & #modname#>

</cfif>

<cfif GetData.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into mainpage_data
			(mp_text, mp_photo1, mp_photo2)
			values (
				'#form.mp_text#'
				<cfif isDefined ("picname")>
				, '#picname#'
				<cfelse>
				, ''
				</cfif>
				<cfif isDefined ("picname2")>
				, '#picname2#'
				<cfelse>
				, ''
				</cfif>
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update mainpage_data
			Set mp_text='#form.mp_text#'
			<cfif isDefined("picname")>
				, mp_photo1='#picname#'
			<cfelseif IsDefined("form.delpic1") and form.delpic1 eq "Yes">
				, mp_photo1=''
			</cfif>
			<cfif isDefined("picname2")>
				, mp_photo2='#picname2#'
			<cfelseif IsDefined("form.delpic2") and form.delpic2 eq "Yes">
				, mp_photo2=''
			</cfif>
	</cfquery>
	
	
	
</cfif>

<!-- now delete old pictures, if any ---->
<cfif (isdefined("Form.mp_photo1") and Form.mp_photo1 gt "") 
	or (isdefined("Form.DelPic1") and Form.DelPic1 eq "Yes")>
	<cfif GetData.RecordCount GT 0 and GetData.mp_photo1 GT "">
		<cfset fn="#fileloc#" & Replace(GetData.mp_photo1, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>
</cfif>

<cfif (isdefined("Form.mp_photo2") and Form.mp_photo2 gt "") 
	or (isdefined("Form.DelPic2") and Form.DelPic2 eq "Yes")>
	<cfif GetData.RecordCount GT 0 and GetData.mp_photo2 GT "">
		<cfset fn="#fileloc#" & Replace(GetData.mp_photo2, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>
</cfif>

<cflocation url="addpara.cfm?x=x">
