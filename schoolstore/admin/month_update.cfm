<cfif not IsDefined("form.pid") or form.pid eq 0>
	<cflocation url="addmonth.cfm">
</cfif>

<cfquery datasource="#dsn#" name="GetData">
	select mp.photo, md.iid from monthly_data md left join monthly_pics mp
		on md.pid=mp.pid
		where md.pid=#form.pid#
</cfquery>

<cfset picname=arrayNew(1)>
<cfloop index="x" from="1" to="3">
<!--- load new picture if designated ----->
<cfif IsDefined("Form.photo#x#") and Form["photo#x#"] GT "">
	<cffile action="Upload" 
		filefield="Form.photo#x#" 
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
		targetmax="#monthwid#"
		maxtype="w">
	<cfif ResizeError>
		<cfoutput>#ErrMessage#</cfoutput>
		<cffile action="Delete"
			file="#newname#">
		
		<cfabort>
	</cfif>

	<cfset picname[x]="images/" & #modname#>

<cfelseif getData.recordCount ge x and getData.photo[x] GT "" and not isDefined("form.delpic#x#") and 
	(not isDefined("form.iid") or form.iid eq getdata.iid or form.iid eq 0)>
	<cfset picname[x]=GEtdata.photo[x]>
<cfelseif isDefined("form.instphoto")>
	<cfset picname[x]="../courses/courses/" & form.instphoto>
<cfelseif x eq 1 and isdefined("form.iid") and form.iid GT 0>
	<cfquery name="getInst" datasource="#dsn#">
		select photo from instructors where iid=#form.iid#
	</cfquery>
	<cfif getInst.photo GT "">
		<cfset picname[x]="../courses/courses/" & getInst.photo>
	<cfelse>
		<cfset picname[x]="">
	</cfif>
<cfelse>
	<cfset picname[x]="">
</cfif>
</cfloop>



<cfif GetData.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into monthly_data
			(pid, fname, lname, story, iid, extratext, showbox )
			values (
				#form.pid#, 
				<cfif isDefined("form.fname")>
					'#form.fname#',
					'#form.lname#', 
				<cfelse>
					'','',
				</cfif>
				'#form.story#', 
				<cfif isDefined("form.iid")>
				#form.iid#, 
				<cfelse>
				0,
				</cfif>
				<cfif IsDefined("form.extratext")>
				'#extratext#', 
				<cfelse>
				'',
				</cfif>
				<cfif isDefined("form.showbox")>true<cfelse>false</cfif>
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update monthly_data
			Set 
			<cfif isDefined("form.fname")>
				fname='#form.fname#', lname='#form.lname#',
			<cfelse>
				fname='', lname='',
			</cfif>
			story='#form.story#', 
			<cfif isDefined("form.extratext")>
				extratext='#form.extratext#',
			</cfif>
			<cfif isDefined("form.iid")>
			iid=#form.iid#,
			<cfelse>
			iid=0,
			</cfif>
			showbox=<cfif isDefined("form.showbox")>true<cfelse>false</cfif>
		where pid=#form.pid#
	</cfquery>	
</cfif>
<!--- photos  --->
<cfquery name="delpics" datasource="#dsn#">
	delete from monthly_pics where pid=#form.pid#
</cfquery>

<cfloop index="x" from="1" to="3">
	<cfif picname[x] gt "">
		<cfquery name="addpic" datasource="#dsn#">
			insert into monthly_pics (pid, photo, caption, pord) values
				(#form.pid#, '#picname[x]#', '#form["caption#x#"]#', #x#)
		</cfquery>
	</cfif>
</cfloop>

<!-- now delete old picture, if any ---->
<cfif GetData.RecordCount GT 0>
<cfset ctr=1>
<cfloop query="getData">
<cfif photo GT "" and left(photo,7) eq "images/">
	<cfif (isdefined("Form.photo#ctr#") and Form["photo#ctr#"] gt "") 
		or (isdefined("Form.DelPic#ctr#") and Form["DelPic#ctr#"] eq "Yes") or
		(isDefined("form.iid") and getData.iid neq form.iid and form.iid gt 0)>
		<cfset fn="#fileloc#" & Replace(photo, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>
</cfif>
<cfset ctr=ctr+1>
</cfloop>
</cfif>

<cflocation url="addmonth.cfm?x=x&pid=#form.pid#" addtoken="No">
