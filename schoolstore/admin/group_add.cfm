<cfif not IsDefined("form.ItemName")>
	<cflocation url="additem.cfm">
</cfif>
<!--- first, upload the picture file, if there --->
<!---cfif val(CGI.CONTENT_LENGTH) gt 100000>
	The file your are attempting to upload is too large. It must
	be less than 100K.  Please go back and try a different file.
	<cfabort>
</cfif--->

<cfif IsDefined("Form.GroupID")>
	<cfquery datasource="#dsn#" name="GetPic">
		select Picture, LgPicture, thumb from Groups 
			Where GroupID=#Form.GroupID#
	</cfquery>
</cfif>

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
	<cfloop condition="(FileExists('#newname#') or fileexists('t#modname#')) and oldname neq newname">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#Images\" & modname>
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
	<cfset lpicname="images/" & #modname#>
	
</cfif>

<!--- if we have an old photo but no thumbnail, create one	--->
<cfif not isdefined("picname") and not isdefined("form.delpic") and isDefined("getpic.thumb") and getPic.thumb eq "">
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
	<cfif IsDefined("Form.GroupID")>
		<cfquery name="UpdGroup" datasource="#dsn#">
			Update Groups
				Set LineID = #Form.LineID#
					, NbrInGroup = NbrInGroup + 1
					, PicturePlace=1
					, GroupName='#FOrm.GroupName#'
					, GroupDesc='#Form.GroupDesc#'
					, cid=#form.cid#
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
			
				<cfif #isdefined("tpicname")#>
					, thumb='#tpicname#'
				<cfelseif isdefined("Form.DelPic") and Form.DelPic eq "Yes">
					, thumb=''	
				</cfif>
			
				<cfif IsDefined("lpicname")>
					, LgPicture='#lpicname#'
				<cfelseif isdefined("Form.DelLPic") and Form.DelLPic eq "Yes">
					, LgPicture=''	
				</cfif>
				WHERE GroupID = #Form.GroupID#
		</cfquery>
	<cfelse>
		<cfquery name="addGroup" datasource="#dsn#">
			Insert Into Groups
				(LineID, GroupOrder, NbrInGroup,
					GroupName, GroupDesc, cid
				
				<cfif #isdefined("picname")#>
					, Picture, thumb
				</cfif>
				, PicturePlace
				<cfif IsDefined("lpicname")>
					, LgPicture
				</cfif>
				)
				VALUES
				(#Form.LineID#
				<cfif IsDefined("Form.GroupOrder") and Form.GroupOrder GT 0>
					, #Form.GroupOrder#
				<cfelse>
					, 0
				</cfif>
				
					, 1
					,'#Form.GroupName#'
					,'#Form.GroupDesc#'
					, #form.cid#
				<cfif #isdefined("picname")#>
					, '#picname#', '#tpicname#'
				</cfif>
				, 1
				<cfif IsDefined("lpicname")>
					, '#lpicname#'
				</cfif>
				)
		</cfquery>
		<cfquery datasource="#dsn#" name="GetGID">
			select Max(GroupID) as MaxGID from Groups
		</cfquery>
		<cfset Form.GroupID=GetGID.MaxGID>
	</cfif>
	
	<cfquery datasource="#dsn#" name="AddItem">
		Insert INTO Items
			(GroupID, ItemName, Price, saleprice, ItemOrder, Grouped
			<cfif IsDefined("Form.ItemDesc") and Form.ItemDesc GT "">
				, ItemDesc
			</cfif>
			<cfif IsDefined("Form.ItemCode") and Form.ItemCode GT "">
				, ItemCode
			</cfif>
			, outofstock, bestseller, newitem, recommended, instructor, weight, toolpic
			)
			VALUES (#Form.GroupID#, '#Form.ItemName#', #Form.Price#, #form.saleprice#
			
			<cfif IsDefined("Form.ItemOrder") and Form.ItemOrder GT 0>
				, #Form.ItemOrder#
			<cfelse>
				, 0
			</cfif>
			
				, True
			
			<cfif IsDefined("Form.ItemDesc") and Form.ItemDesc GT "">
				, '#Form.ItemDesc#'
			</cfif>
			<cfif IsDefined("Form.ItemCode") and Form.ItemCode GT "">
				, '#Form.ItemCode#'
			</cfif>
			<cfif IsDefined("form.outofstock") and form.outofstock eq "yes">
				, true
			<cfelse>
				, false
			</cfif>
			<cfif IsDefined("form.bestseller") and form.bestseller eq "yes">
				, true
			<cfelse>
				, false
			</cfif>
			<cfif IsDefined("form.newitem") and form.newitem eq "yes">
				, true
			<cfelse>
				, false
			</cfif>
			<cfif IsDefined("form.recommended") and form.recommended eq "yes">
				, true
			<cfelse>
				, false
			</cfif>
			<cfif IsDefined("form.instructor") and form.instructor eq "yes">
				, true
			<cfelse>
				, false
			</cfif>
			<cfif form.weight GT "">
				, #form.weight#
			<cfelse>
				, null
			</cfif>
			<cfif IsDefined("form.toolpic") and form.toolpic eq "yes">
				, true
			<cfelse>
				, false
			</cfif>
			
			)
	</cfquery>
	
	<cfquery datasource="#dsn#" name="GetIID">
		Select Max(ItemID) as MaxID from Items
	</cfquery>
	<cfif IsDefined("Form.ItemOpts") and Form.ItemOpts GT "">
		<cfloop list="#Form.ItemOpts#" index="OIdx" delimiters=";">
			<cfquery datasource="#dsn#" name="AddOpts">
				Insert into ItemOptions
					(ItemID, OptionName)
					values (#GetIID.MaxID#, '#OIdx#')
			</cfquery>
		</cfloop>
	</cfif>
	<cfif IsDefined("Form.ItemSize") and Form.ItemSize GT "">
		<cfloop list="#Form.ItemSize#" index="SIdx" delimiters=";">
			<cfquery datasource="#dsn#" name="AddSizes">
				Insert into ItemSizes
					(ItemID, Size)
					values (#GetIID.MaxID#, '#SIdx#')
			</cfquery>
		</cfloop>
	</cfif>
	
</cftransaction>

<!--- now - delete previous group pictures, if any --->
<cfif IsDefined("GetPic.Picture")>
	<cfif (#isdefined("Form.Picture")# and #Form.Picture# gt "") 
		or (isdefined("Form.DelPic") and Form.DelPic eq "Yes")>
		<cfif GetPic.Picture GT "">
			<cfset fn="#fileloc#" & Replace(GetPic.Picture, "/","\","All")>
			<cflock name="FileLock" type="Exclusive" Timeout="30">
				<cffile action="Delete"
					file=#fn#>
			</cflock>
		</cfif>
		<cfif GetPic.thumb GT "">
			<cfset fn="#fileloc#" & Replace(GetPic.thumb, "/","\","All")>
			<cflock name="FileLock" type="Exclusive" Timeout="30">
				<cffile action="Delete"
					file=#fn#>
			</cflock>
		</cfif>
	</cfif>

	<cfif (#isdefined("Form.LgPicture")# and #Form.LgPicture# gt "") 
		or (isdefined("Form.DelLPic") and Form.DelLPic eq "Yes")>

		<cfif GetPic.LgPicture GT "">
			<cfset fn="#fileloc#" & Replace(GetPic.LgPicture, "/","\","All")>
			<cflock name="FileLock" type="Exclusive" Timeout="30">		
				<cffile action="Delete"
					file=#fn#>
			</cflock>
		</cfif>
	</cfif>
</cfif>

<cflocation url="addgroup.cfm?x=#form.ItemName#&LineID=#form.LineID#&GroupID=#Form.GroupID#">