<cfif not isdefined("Form.lname") and not IsDefined("url.del")>
	<cflocation url="instructors.cfm">
</cfif>
<cfinclude template="header.cfm">

<cfif IsDefined("url.iid") and not IsDefined("form.iid")>
	<cfset form.iid=url.iid>
</cfif>

<!--- upload and check photo --->
<cfif isdefined("Form.photo") and Form.photo gt "">
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
	
	<!--- resize photo --->
	<cf_resizeimg
		filepath="#fileloc#/images"
		filename="#modname#"
		targetmax="#maxwid#"
		maxtype="w">
	<cfif ResizeError>
		<cfoutput>#ErrMessage#</cfoutput>
		<cfabort>
	</cfif>

	<cfset picname="images/" & modname>
<cfelse>
	<cfset picname="">
</cfif>

<cfif isDefined("form.url") and Form.url GT "">
	<cfif left(form.url,7) neq "http://">
		<cfset form.url="http://" & form.url>
	</cfif>
</cfif>

<cfif IsDefined("form.iid")>
	<!--- updating an exisiting instructor --->
	<cfquery name="GetPhoto" datasource="#dsn#">
		select photo from instructors where iid=#form.iid#
	</cfquery>
	<cfif IsDefined("url.del") and url.del eq "yes">
		<!--- delete instructor --->
		<cfquery name="DelInst" datasource="#dsn#">
			delete from instructors where 
				iid=#form.iid#
		</cfquery>
	<cfelse>
		<!--- update instructor --->
		<cfif picname eq "" and not IsDefined("form.delpic")>
			<cfset picname=GetPhoto.photo>
		</cfif>
		<cfquery name="SaveInst" datasource="#dsn#">
			update instructors set
				fname='#form.fname#',
				lname='#form.lname#',
				home='#form.home#',
				profile='#form.profile#',
				photo='#picname#',
				url='#form.url#'
				<cfif isDefined("Form.guest")>
					, guest=true
				<cfelse>
					, guest=false
				</cfif>
			where iid=#form.iid#
		</cfquery>
	</cfif>

	<!--- delete old photo, if any --->
	<cfif isDefined("form.delpic") or picname neq GetPhoto.photo or (isDefined("url.del") and url.del eq "yes")>
		<cfif GetPhoto.photo GT "">
			<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="DELETE"
				file="#fileloc##GetPhoto.photo#">
			</cflock>
		</cfif>
	</cfif>
	
<cfelse>
	<!--- adding a new instructor --->
	
	<cfquery datasource="#dsn#" name="AddInst">
		Insert into instructors
			(fname, lname, home, photo, profile, url, guest)
			values ('#form.fname#', '#form.lname#', '#form.home#', '#picname#',
				'#form.profile#', '#form.url#'
			<cfif isDefined("Form.guest")>
				, true
			<cfelse>
				, false
			</cfif>
				)
	</cfquery>
</cfif>

<cflocation url="instructors.cfm?x=x" addtoken="no">
