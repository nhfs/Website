<cfif not IsDefined("URL.ItemID")>
	<cflocation url="additem.cfm">
</cfif>
<!--- first, upload the picture file, if there --->
<!---cfif val(CGI.CONTENT_LENGTH) gt 100000>
	The file your are attempting to upload is too large. It must
	be less than 100K.  Please go back and try a different file.
	<cfabort>
</cfif--->

<cfquery datasource="#dsn#" name="GetPic">
	select Picture, LgPicture, thumb from Groups INNER JOIN Items
		ON Items.GroupID = Groups.GroupID
		Where ItemID=#URL.ItemID#
</cfquery>

<!--- load new picture if designated ----->
<cfif IsDefined("Form.Picture") and Form.Picture GT "">
	<cffile action="Upload" 
		filefield="Form.Picture" 
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
	<cfloop condition="(FileExists('#newname#') or fileExists('t#newname#')) and oldname neq newname">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#images\" & modname>
		<cfset idx=idx+1>
	</cfloop>
	
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
	<cffile action="Rename"
		source="#oldname#" 
		destination="#newname#">
	</cflock>
	
		<!-- resize image if needed --->
	<cfset imageCFC = createObject("component","image")>
	<!--- get current dimensions --->
	<cfset imgInfo = imageCFC.getImageInfo("", "#newname#")>
	<cfif imgInfo.width GT fullcatwid>
		<cf_resizeimg
			filepath="#fileloc#/images"
			filename="#modname#"
			targetmax="#fullcatwid#"
			maxtype="w">
		<cfif ResizeError>
			<cfoutput>#ErrMessage#</cfoutput>
			<cffile action="Delete"
				file="#newname#">
			<cfabort>
		</cfif>
	</cfif>

	<cfset picname="images/" & #modname#>

	<!--- create thumbnail --->
	<cfset tfn="#fileloc#images\t#modname#">
	<cfset imageCFC = createObject("component","image")>
	<!--- first, get current dimensions --->
	<cfset imgInfo = imageCFC.getImageInfo("", "#newname#")>
	<cffile action="COPY" 
		source="#newname#" destination="#tfn#">
	<cfif imgInfo.height GT imgInfo.width>
		<cfset mtype="h">
	<cfelse>
		<cfset mtype="w">
	</cfif>
	<cf_resizeimg
		filepath="#fileloc#/images"
		filename="t#modname#"
		targetmax="#catwid#"
		maxtype="#mtype#">
	<cfif ResizeError>
		<cfoutput>#ErrMessage#</cfoutput>
		<cffile action="Delete"
			file="#newname#">
		<cffile action="Delete"
			file="#tfn#">
		<cfabort>
	</cfif>
	<cfset tpicname="images/t#modname#">
</cfif>

<!--- load large picture if designated ----->
<cfif IsDefined("Form.LgPicture") and Form.LgPicture GT "">
	<cffile action="Upload" 
		filefield="Form.LgPicture" 
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
	<cfset lpicname="images/" & #modname#>
</cfif>

<!--- if we have an old photo but no thumbnail, create one	--->
<cfif not isdefined("picname") and not isdefined("form.delpic") and getPic.thumb eq ""  and getpic.picture GT "">
	<cfset modname=listGetAt("#getPic.picture#", ListLen("#getpic.picture#","/"), "/")>
	<cfset tfn="#fileloc#images\t#modname#">
	<cfset imageCFC = createObject("component","image")>
	<!--- first, get current dimensions --->
	<cfset fn=fileloc & getpic.picture>
	<cfset imgInfo = imageCFC.getImageInfo("", "#fn#")>
	<cffile action="COPY" 
		source="#fn#" destination="#tfn#">
	<cfif imgInfo.height GT imgInfo.width>
		<cfset mtype="h">
	<cfelse>
		<cfset mtype="w">
	</cfif>
	<cf_resizeimg
		filepath="#fileloc#/images"
		filename="t#modname#"
		targetmax="#catwid#"
		maxtype="#mtype#">
	<cfif ResizeError>
		<cffile action="Delete"
			file="#tfn#">
	<cfelse>
		<cfset tpicname="images/t#modname#">
	</cfif>
</cfif>

<cftransaction>
	<cfquery name="GetGID" datasource="#dsn#">
		select GroupID, Grouped from Items
			Where ItemID=#URL.ItemID#
	</cfquery>
	<cfquery name="UpdGroup" datasource="#dsn#">
		Update Groups
			Set LineID = #Form.LineID#
				, PicturePlace=1
			<cfif IsDefined("Form.GroupOrder") and Form.GroupOrder GT 0>
				, GroupOrder = #Form.GroupOrder#
			<cfelse>
				, GroupOrder = 0
			</cfif>
			
			<cfif #isdefined("picname")#>
				, Picture='#picname#'
			<cfelseif isdefined("Form.DelPic") and Form.DelPic eq "Yes">
				, Picture=''	
			</cfif>
			<cfif isDefined("tpicname")>
				, thumb='#tpicname#'
			<cfelseif isDefined("form.delpic")>
				, thumb=''
			</cfif>
			
			<cfif IsDefined("lpicname")>
				, LgPicture='#lpicname#'
			<cfelseif isdefined("Form.DelLPic") and Form.DelLPic eq "Yes">
				, LgPicture=''	
			</cfif>
			<cfif IsDefined("Form.GroupName")>
				, GroupName='#Form.GroupName#'
			<cfelse>
				, GroupName='#Form.ItemName#'
			</cfif>
			<cfif IsDefined("Form.GroupDesc")>
				, GroupDesc='#Form.GroupDesc#'
			<cfelse>
				, GroupDesc='#Form.ItemDesc#'
			</cfif>
				, cid=#form.cid#
			WHERE GroupID = #GetGID.GroupID#
	</cfquery>
	
	<cfquery datasource="#dsn#" name="UpdItem">
		Update Items
			Set Price=#Form.Price#, saleprice=#form.saleprice#
			<cfif IsDefined("GroupName")>
				, ItemName = '#Form.ItemName#'
			<cfelse>
				, ItemName = ''
			</cfif>

			<cfif IsDefined("Form.ItemOrder")>
				, ItemOrder=#Form.ItemOrder#
			<cfelse>
				, ItemOrder=0
			</cfif>
			<cfif IsDefined("Form.GroupDesc")>
				, ItemDesc='#Form.ItemDesc#'
			<cfelse>
				, ItemDesc=''
			</cfif>
			<cfif IsDefined("Form.ItemCode")>
				, ItemCode='#Form.ItemCode#'
			<cfelse>
				, ItemCode=''
			</cfif>
			<cfif IsDefined("form.outofstock") and form.outofstock eq "yes">
				, Outofstock=true
			<cfelse>
				, Outofstock=false
			</cfif>
			<cfif IsDefined("form.bestseller") and form.bestseller eq "yes">
				, bestseller=true
			<cfelse>
				, bestseller=false
			</cfif>
			<cfif IsDefined("form.newitem") and form.newitem eq "yes">
				, newitem=true
			<cfelse>
				, newitem=false
			</cfif>
			<cfif IsDefined("form.recommended") and form.recommended eq "yes">
				, recommended=true
			<cfelse>
				, recommended=false
			</cfif>
			<cfif IsDefined("form.instructor") and form.instructor eq "yes">
				, instructor=true
			<cfelse>
				, instructor=false
			</cfif>
			<cfif IsDefined("form.toolpic") and form.toolpic eq "yes">
				, toolpic=true
			<cfelse>
				, toolpic=false
			</cfif>
			<cfif form.weight GT "">
				, weight=#form.weight#
			<cfelse>
				, weight=null
			</cfif>
			WHERE ItemID = #URL.ItemID#
	</cfquery>
	
	<!-- delete old options and sizes ---->
	<cfquery datasource="#dsn#" name="DelOpts">
		delete from ItemOptions where ItemID = #URL.ItemID#
	</cfquery>
	<cfquery datasource="#dsn#" name="DelSizes">
		delete from ItemSizes where ItemID = #URL.ItemID#
	</cfquery>
	
	<cfif IsDefined("Form.ItemOpts") and Form.ItemOpts GT "">
		<cfloop list="#Form.ItemOpts#" index="OIdx" delimiters=";">
			<cfquery datasource="#dsn#" name="AddOpts">
				Insert into ItemOptions
					(ItemID, OptionName)
					values (#URL.ItemID#, '#OIdx#')
			</cfquery>
		</cfloop>
	</cfif>
	<cfif IsDefined("Form.ItemSize") and Form.ItemSize GT "">
		<cfloop list="#Form.ItemSize#" index="SIdx" delimiters=";">
			<cfquery datasource="#dsn#" name="AddSizes">
				Insert into ItemSizes
					(ItemID, Size)
					values (#URL.ItemID#, '#SIdx#')
			</cfquery>
		</cfloop>
	</cfif>
	
</cftransaction>

<!-- now delete old pictures, if any ---->
<cfif (#isdefined("Form.Picture")# and #Form.Picture# gt "") 
	or (isdefined("Form.DelPic") and Form.DelPic eq "Yes")>
	<cfif GetPic.Picture GT "">
		<cfset fn="#fileloc#" & Replace(GetPic.Picture, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>
	<cfif GetPic.thumb GT "">
		<cfset fn="#fileloc#" & Replace(GetPic.thumb, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>
</cfif>

<cfif (#isdefined("Form.LgPicture")# and #Form.LgPicture# gt "") 
	or (isdefined("Form.DelLPic") and Form.DelLPic eq "Yes")>
	<cfif GetPic.LgPicture GT "">
		<cfset fn="#fileloc#" & Replace(GetPic.LgPicture, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>
</cfif>

<cfif GetGID.Grouped>
	<cflocation url="addgroup.cfm?y=#form.ItemName#">
<cfelse>
	<cflocation url="additem.cfm?y=#form.ItemName#">
</cfif>