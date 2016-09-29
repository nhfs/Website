<cfif not isdefined("Form.photourl")>
	<cflocation url="footerphoto.cfm">
</cfif>
<cfinclude template="header.cfm">

<!--- upload and check photo --->
<cfif isdefined("Form.photourl") and Form.photourl gt "">
	<cffile action="Upload" 
		filefield="Form.photourl" 
		Destination="#fileloc#images\"	
		accept="image/gif,image/jpg,image/jpeg,image/pjpeg"
		nameconflict="MAKEUNIQUE">
	
	<cfset modname=Replace(CFFILE.ServerFile, " ", "_", "All")>
	<cfset modname=Replace(modname, "&", "_", "All")>
	<cfset modname=Replace(modname, "$", "_", "All")>
	<cfset modname=Replace(modname, ",", "_", "All")>
	<cfset modname=Replace(modname, "-", "_", "All")>
	<cfset modname=right(modname, 98)>
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
		targetw=797
		targeth=91>
	<cfif ResizeError>
		<cfoutput>#ErrMessage#</cfoutput>
		<cfabort>
	</cfif>

	<cfset picname="images/" & modname>
<cfelse>
	<cfset picname="">
</cfif>

<cfif IsDefined("form.newphoto")>
	<!--- adding a new photo --->
	<cfquery name="AddPhoto" datasource="#dsn#">
		insert into footerphoto values ('#picname#')
	</cfquery>
		
<cfelse>
	<!--- update photo --->
	<cfquery name="GetPhoto" datasource="#dsn#">
		select * from footerphoto
	</cfquery>
	<cfif picname eq "">
		<cfset picname=GetPhoto.photourl>
	</cfif>
	<cfquery name="SavePhoto" datasource="#dsn#">
		update footerphoto set photourl='#picname#'
	</cfquery>

	<!--- delete old photo, if any --->
	<cfif picname neq GetPhoto.photourl>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
		<cffile action="DELETE"
			file="#fileloc##GetPhoto.photourl#">
		</cflock>
	</cfif>
	
</cfif>

<cflocation url="footerphoto.cfm?x=x" addtoken="no">
