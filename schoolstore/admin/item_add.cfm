<cfif not IsDefined("form.ItemName")>
	<cflocation url="additem.cfm">
</cfif>
<!--- first, upload the picture file, if there --->
<!---cfif val(CGI.CONTENT_LENGTH) gt 100000>
	The file you are attempting to upload is too large. It must
	be less than 100K.  Please go back and try a different file.
	<cfabort>
</cfif--->

<cfif #isdefined("Form.Picture")# and #Form.Picture# gt "">
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
	<cfloop condition="(FileExists('#newname#') or fileexists('t#newname#')) and newname neq oldname">
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

<cfif IsDefined("Form.LgPicture") and Form.LgPicture gt "">
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
	<cfset oldname="#fileloc#Images\" & #CFFILE.ServerFile#>
	<cfset newname="#fileloc#Images\" & #modname#>
	<cfset idx="1">
	<cfloop condition="FileExists('#newname#') and newname neq oldname">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#Images\" & modname>
		<cfset idx=idx+1>
	</cfloop>
	
	<cflock name="Filelock" Type="Exclusive" Timeout="30">
	<cffile action="Rename"
		source="#oldname#" 
		destination="#newname#">
	</cflock>
	<cfset lpicname="Images/" & #modname#>

</cfif>

<cftransaction>
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
				, '#Form.ItemName#'
				, '#Form.ItemDesc#'
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
	
	<cfquery datasource="#dsn#" name="AddItem">
		Insert INTO Items
			(GroupID, Price, saleprice, ItemOrder
			
			<cfif IsDefined("Form.ItemCode") and Form.ItemCode GT "">
				, ItemCode
			</cfif>
			, outofstock, bestseller, newitem, recommended, instructor, weight, toolpic
			)
			VALUES (#GetGID.MaxGID#, #Form.Price#, #form.saleprice#, 0
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

<cflocation url="additem.cfm?x=#form.ItemName#&LineID=#form.LineID#">