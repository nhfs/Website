<cfif not isdefined("Form.pname") and not IsDefined("url.del")>
	<cflocation url="partners.cfm">
</cfif>
<cfinclude template="header.cfm">

<cfif IsDefined("url.pid") and not IsDefined("form.pid")>
	<cfset form.pid=url.pid>
</cfif>

<!--- upload and check logo --->
<cfif isdefined("Form.logourl") and Form.logourl gt "">
	<cffile action="Upload" 
		filefield="Form.logourl" 
		Destination="#fileloc#images\"	
		accept="image/gif,image/jpg,image/jpeg,image/pjpeg"
		nameconflict="MAKEUNIQUE">
	
	<cfset modname=Replace(CFFILE.ServerFile, " ", "_", "All")>
	<cfset modname=Replace(modname, "&", "_", "All")>
	<cfset modname=Replace(modname, "$", "_", "All")>
	<cfset modname=Replace(modname, ",", "_", "All")>
	<cfset modname=Replace(modname, "-", "_", "All")>
	<cfset modname=left(modname, 98)>
	<cfset oldname="#fileloc#images\" & CFFILE.ServerFile>
	<cfset newname="#fileloc#images\" & modname>
	<cfset idx="1">
	<cfloop condition="FileExists('#newname#') and oldname neq newname">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#images\" & modname>
		<cfset idx=idx+1>
	</cfloop>
	
	<cfif oldname neq newname>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
		<cffile action="Rename"
			source="#oldname#" 
			destination="#newname#">
		</cflock>
	</cfif>
	
	<!--- resize logo --->
	<cfset imageCFC = createObject("component","image")>
	<!--- get current dimensions --->
	<cfset imgInfo = imageCFC.getImageInfo("", "#newname#")>
	<cfif imgInfo.height GT imgInfo.width>
		<cfset maxtype="h">
		<cfset max=logoht>
	<cfelse>
		<cfif imgInfo.height*(logowid/imgInfo.width) GT logoht>
			<cfset maxtype="h">
			<cfset max=logoht>
		<cfelse>
			<cfset maxtype="w">
			<cfset max=logowid>
		</cfif>
	</cfif>
	<cf_resizeimg
		filepath="#fileloc#/images"
		filename="#modname#"
		targetmax="#max#"
		maxtype="#maxtype#">
	<cfif ResizeError>
		<cfoutput>#ErrMessage#</cfoutput>
		<cfabort>
	</cfif>
	<cfset imgInfo = imageCFC.getImageInfo("", "#fileloc#/images/#modname#")>
	<cfset pich=imgInfo.height>
	<cfset picname="images/" & modname>
<cfelse>
	<cfset picname="">
	<cfset pich=0>
</cfif>

<cfif isDefined("form.purl") and Form.purl GT "">
	<cfif left(form.purl,7) neq "http://" and left(form.purl,8) neq "https://">
		<cfset form.purl="http://" & form.purl>
	</cfif>
</cfif>

<cfif IsDefined("form.pid")>
	<!--- updating an existing partner --->
	<cfif IsDefined("url.del") and url.del eq "yes">
		<cfquery name="GetLogo" datasource="#dsn#">
			select logourl from partners where pid=#form.pid#
		</cfquery>
		<cfif getLogo.logourl GT "">
			<cfset form.oldlogo=getlogo.logourl>
		</cfif>
		<!--- delete instructor --->
		<cfquery name="DelPart" datasource="#dsn#">
			delete from partners where 
				pid=#form.pid#
		</cfquery>
	<cfelse>
		<!--- update partner --->
		<cfif picname eq "" and not IsDefined("form.delpic")>
			<cfif isdefined("form.oldlogo")>
				<cfset picname=form.oldlogo>
				<cfset pich=form.oldh>
			</cfif>
		</cfif>
		<cfquery name="Savepart" datasource="#dsn#">
			update partners set
				pname='#form.pname#',
				plevel=#form.plevel#,
				type='#form.type#',
				logourl='#picname#',
				logoh=#pich#,
				purl='#form.purl#',
				phone='#form.phone#',
				pdesc='#form.pdesc#'
			where pid=#form.pid#
		</cfquery>
	</cfif>

	<!--- delete old logo, if any --->
	<cfif isDefined("form.delpic") or(isDefined("form.oldlogo") and picname neq form.oldlogo) or (isDefined("url.del") and url.del eq "yes")>
		<cfif isDefined("form.oldlogo") and form.oldlogo GT "" and fileexists("#fileloc##form.oldlogo#")>
			<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="DELETE"
				file="#fileloc##form.oldlogo#">
			</cflock>
		</cfif>
	</cfif>
	
<cfelseif form.pname GT "">
	<!--- adding a new partner --->
	
	<cfquery datasource="#dsn#" name="Addpart">
		Insert into partners
			(pname, plevel, type, logourl, logoh, purl, phone, pdesc, porder)
			values ('#form.pname#', #form.plevel#, '#form.type#', '#picname#', #pich#,
				'#form.purl#', '#form.phone#', '#form.pdesc#', 99999)
	</cfquery>
</cfif>

<!--- update page information --->
<cfif isDefined("form.title")>
	<cfquery name="DelOld" datasource="#dsn#">
		delete from pagetext where pid='#part_pid#'
	</cfquery>
	<cfquery name="AddText" datasource="#dsn#">
		insert into pagetext (pid, title, pagetext) values
		('#part_pid#', '#form.title#', '#form.pagetext#')
	</cfquery>
</cfif>

<cflocation url="partners.cfm?x=x" addtoken="no">
