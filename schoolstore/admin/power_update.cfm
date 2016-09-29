<cfif not IsDefined("form.bid") or form.bid eq 0>
	<cflocation url="addpower.cfm">
</cfif>

<cfquery datasource="#dsn#" name="GetData">
	select photo from pbox
		where bid=#form.bid#
</cfquery>

<!--- load new picture if designated ----->
<cfif IsDefined("Form.photo") and Form.photo GT "">
	<cffile action="Upload" 
		filefield="Form.photo" 
		Destination="#fileloc#images\"
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
		targetmax="#catwid#"
		maxtype="w">
	<cfif ResizeError>
		<cfoutput>#ErrMessage#</cfoutput>
		<cffile action="Delete"
			file="#newname#">
		
		<cfabort>
	</cfif>

	<cfset picname="images/" & #modname#>

<cfelseif isDefined("getdata.photo") and getData.photo GT "" and not isDefined("form.delpic")>
	<cfset picname=GEtdata.photo>
<cfelse>
	<cfset picname="">
</cfif>

<cfif left(Form.url, 7) neq "http://" and left(form.url,8) neq "https://">
	<cfset form.url="http://" & form.url>
</cfif>

<cfif GetData.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into pbox
			(bid, title, photo, url)
			values (
				#form.bid#, '#form.title#',
				'#picname#', '#form.url#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update pbox
			Set title='#form.title#', photo='#picname#',
			url='#form.url#'
		where bid=#form.bid#
	</cfquery>	
</cfif>

<!-- now delete old picture, if any ---->
<cfif GetData.RecordCount GT 0 and GetData.photo GT "">
	<cfif (isdefined("Form.photo") and Form.photo gt "") 
		or (isdefined("Form.DelPic") and Form.DelPic eq "Yes")>

		<cfset fn="#fileloc#" & Replace(GetData.photo, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>
</cfif>

<cflocation url="addpower.cfm?x=x&bid=#form.bid#" addtoken="No">
