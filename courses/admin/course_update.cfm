<cfif not isdefined("Form.name") and not IsDefined("url.del")>
	<cflocation url="courses.cfm">
</cfif>
<cfinclude template="header.cfm">

<cfif IsDefined("url.cid") and not IsDefined("form.cid")>
	<cfset form.cid=url.cid>
</cfif>

<!--- upload and check photos --->
<cfset picname=ArrayNew(1)>
<cfloop index="idx" from=1 to=3>
	<cfif isdefined("Form.photo#idx#") and Form["photo#idx#"] gt "">
		<cffile action="Upload" 
			filefield="Form.photo#idx#" 
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
		<cfset idx2="1">
		<cfloop condition="FileExists('#newname#') and oldname neq newname">
			<cfset modname=idx2 & modname>
			<cfset newname="#fileloc#images\" & modname>
			<cfset idx2=idx2+1>
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

		<cfset picname[idx]="images/" & modname>
	<cfelse>
		<cfset picname[idx]="">
	</cfif>
</cfloop>

<!--- upload pdf --->
<cfif isdefined("Form.pdf") and Form.pdf gt "">
	<cffile action="Upload" 
		filefield="Form.pdf" 
		Destination="#fileloc#pdfs\"	
		accept="application/pdf"
		nameconflict="MAKEUNIQUE">
	
	<cfset modname=Replace(CFFILE.ServerFile, " ", "_", "All")>
	<cfset modname=Replace(modname, "&", "_", "All")>
	<cfset modname=Replace(modname, "$", "_", "All")>
	<cfset modname=Replace(modname, ",", "_", "All")>
	<cfset modname=Replace(modname, "-", "_", "All")>
	<cfset modname=left(modname, 98)>
	<cfset oldname="#fileloc#pdfs\" & CFFILE.ServerFile>
	<cfset newname="#fileloc#pdfs\" & modname>
	<cfset idx="1">
	<cfloop condition="FileExists('#newname#') and oldname neq newname">
		<cfset modname=idx & modname>
		<cfset newname="#fileloc#pdfs\" & modname>
		<cfset idx=idx+1>
	</cfloop>
	
	<cfif oldname neq newname>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
		<cffile action="Rename"
			source="#oldname#" 
			destination="#newname#">
		</cflock>
	</cfif>

	<cfset pdfname="pdfs/" & modname>
<cfelse>
	<cfset pdfname="">
</cfif>

<!--- save course info ---->
<cfif isDefined("form.url") and form.url gt "" and left(form.url,7) neq "http://">
	<cfset form.url="http://" & form.url>
</cfif>

<!--- allow for no tuition amounts --->
<cfif isDefined("form.reg_tuition")>
<!---cfif form.peak_tuition le ""><cfset form.peak_tuition=0></cfif>
<cfif form.offpeak_tuition le ""><cfset form.offpeak_tuition=0></cfif--->
<cfif form.early_tuition le ""><cfset form.early_tuition=0></cfif>
<cfif form.reg_tuition le ""><cfset form.reg_tuition=0></cfif>
</cfif>

<cfif IsDefined("form.cid")>
	<!--- updating an existing course --->
	<cfquery name="GetPDF" datasource="#dsn#">
		select pdf, isevent from courses where cid=#form.cid#
	</cfquery>
	<cfquery name="GetPhoto" datasource="#dsn#">
		select photo from course_photos where cid=#form.cid#
			order by porder
	</cfquery>
	<cfquery name="DelCInsts" datasource="#dsn#">
		delete from course_inst where cid=#form.cid#		
	</cfquery>
	<cfquery name="DelCThemes" datasource="#dsn#">
		delete from course_themes where cid=#form.cid#		
	</cfquery>
	<cfquery name="DelCDates" datasource="#dsn#">
		delete from course_dates where cid=#form.cid#		
	</cfquery>
	<cfquery name="DelCPhotos" datasource="#dsn#">
		delete from course_photos where cid=#form.cid#		
	</cfquery>
	<cfif IsDefined("url.del") and url.del eq "yes">
		<!--- delete course --->
		<cfquery name="DelCourse" datasource="#dsn#">
			delete from courses where 
				cid=#form.cid#
		</cfquery>
		<!--- if this was an event, take it off any courses that were a part of it --->
		<cfif GetPDF.isevent eq true>
			<cfquery name="DelEvent" datasource="#dsn#">
				update courses set eventid=0 where eventid=#form.cid#
			</cfquery>
		</cfif>
	<cfelse>
		<!--- update course --->
		<cfif pdfname eq "" and not IsDefined("form.delpdf")>
			<cfset pdfname=GetPDF.pdf>
		</cfif>
		<cfquery name="SaveCourse" datasource="#dsn#">
			update courses set
				name='#form.name#',
				tagline='#form.tagline#',
				descrip='#form.descrip#',
				<cfif now() lt createDate(2012, 3, 1)>
					peak_tuition=#form.peak_tuition#,
					peak_desc='#form.peak_desc#',
					offpeak_tuition=#form.offpeak_tuition#,
					offpeak_desc='#form.offpeak_desc#',
				</cfif>
				early_tuition=#form.early_tuition#,
				early_desc='#form.early_desc#',
				reg_tuition=#form.reg_tuition#,
				reg_desc='#form.reg_desc#',
				<cfif IsDefined("form.paybyproject") and form.paybyproject eq "true">
					paybyproject=true,
				<cfelse>
					paybyproject=false,
				</cfif>
				materials='#form.materials#',
				<cfif form.material_amt GT "">
					material_amt=#form.material_amt#,
				<cfelse>
					material_amt=0
				</cfif>
				tools='#form.tools#',
				length=#form.length#,
				length_desc='#form.length_desc#',
				hours='#form.hours#',
				levels='#form.levels#',
				intergen='#form.intergen#',
				<cfif isDefined("form.isevent")>
					isevent=true,
				<cfelse>
					isevent=false,
				</cfif>
<!---				eventid=#form.eventid#,  --->
				url='#form.url#',
				pdf='#pdfname#',
				pdftitle='#form.pdftitle#',
				<cfif isDefined("form.daily")>
					daily=true
				<cfelse>
					daily=false
				</cfif>
				<cfif isDefined("form.comments")>
					, comment=true
				<cfelse>
					, comment=false
				</cfif>
				<cfif form.commtitle GT "">
					, commtitle='#form.commtitle#'
				<cfelse>
					, commtitle='Comments'
				</cfif>
				<cfif isDefined("form.hidden")>
					, hidden=true
				<cfelse>
					, hidden=false
				</cfif>
				<cfif isDefined("form.payinfull")>
					, payinfull=true
				<cfelse>
					, payinfull=false
				</cfif>
			where cid=#form.cid#
		</cfquery>
	</cfif>

	<!--- delete old photos and pdf, if any --->
	<cfloop index="idx" from=1 to=3>
		<cfif isDefined("form.delpic#idx#") or (GetPhoto.RecordCount ge idx and picname[idx] neq GetPhoto["photo"][idx] and picname[idx] GT "") or (isDefined("url.del") and url.del eq "yes")>
			<cfif GetPhoto['photo'][idx] GT "" and FileExists("#fileloc##GetPhoto['photo'][idx]#")>
				<cflock name="Filelock" Type="Exclusive" Timeout="30">
				<cffile action="DELETE"
					file="#fileloc##GetPhoto['photo'][idx]#">
				</cflock>
			</cfif>
		</cfif>
	</cfloop>
	<cfif isDefined("form.delpdf") or pdfname neq GetPDF.pdf or (isDefined("url.del") and url.del eq "yes")>
		<cfif GetPDF.pdf GT "" and FileExists("#fileloc##GetPDF.pdf#")>
			<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="DELETE"
				file="#fileloc##GetPDF.pdf#">
			</cflock>
		</cfif>
	</cfif>
	
<cfelse>
	<!--- adding a new course --->
	
	<cfquery datasource="#dsn#" name="Addcourse">
		Insert into courses
			(name, tagline,	descrip, <cfif now() lt createdate(2012,3,1)>peak_tuition, peak_desc, offpeak_tuition, offpeak_desc,</cfif>
				early_tuition, early_desc, reg_tuition, reg_desc,
				paybyproject, material_amt, materials, length, length_desc, hours, levels, intergen,
				isevent, <!---eventid, --->url, pdf, pdftitle, daily, comment, commtitle, hidden, tools, payinfull)
			values (
				'#form.name#', '#form.tagline#', '#form.descrip#', 
				<cfif now() lt createdate(2012,3,1)>
				#form.peak_tuition#,
				'#form.peak_desc#', #form.offpeak_tuition#, '#form.offpeak_desc#',
				</cfif>
				#form.early_tuition#,
				'#form.early_desc#',
				#form.reg_tuition#,
				'#form.reg_desc#',
				<cfif IsDefined("form.paybyproject") and form.paybyproject eq "true">
					true,
				<cfelse>
					false,
				</cfif>
				<cfif form.material_amt GT "">
					#form.material_amt#, 
				<cfelse>
					0,
				</cfif>
				'#form.materials#', #form.length#, '#form.length_desc#', '#form.hours#',
				'#form.levels#', '#form.intergen#', 
				<cfif isDefined("form.isevent")>
					true,
				<cfelse>
					false,
				</cfif>
				<!---#form.eventid#,---> '#form.url#', '#pdfname#', '#form.pdftitle#',
				<cfif isDefined("form.daily")>
					true
				<cfelse>
					false
				</cfif>
				<cfif isDefined("form.comments")>
					, true
				<cfelse>
					, false
				</cfif>
				<cfif form.commtitle GT "">
					, '#form.commtitle#'
				<cfelse>
					, 'Comments'
				</cfif>
				<cfif isDefined("form.hidden")>
					, true
				<cfelse>
					, false
				</cfif>
				, '#form.tools#'
				<cfif isDefined("form.payinfull")>
					, true
				<cfelse>
					, false
				</cfif>
				)
	</cfquery>
	<cfquery name="GetID" datasource="#dsn#">
		select max(cid) as maxid from courses
			where name='#form.name#'
	</cfquery>
	<cfset form.cid=GetID.maxid>
</cfif>

<cfif not IsDefined ("url.del")>
	<!--- save instructors --->
	<cfloop index="idx" from=1 to=3>
		<cfif form["iid#idx#"] gt 0>
			<cfquery name="SaveInst" datasource="#dsn#">
				insert into course_inst (cid, iid)
					values (#form.cid#, #form["iid#idx#"]#)
			</cfquery>
		</cfif>
	</cfloop>
	
	<!--- save themes --->
	<cfloop index="idx" from=1 to=2>
		<cfif form["tid#idx#"] gt 0>
			<cfquery name="SaveThemes" datasource="#dsn#">
				insert into course_themes (cid, tid, rank)
					values (#form.cid#, #form["tid#idx#"]#, #idx#)
			</cfquery>
		</cfif>
	</cfloop>

	<!--- save dates --->
	<cfloop index="idx" from=1 to=10>
		<cfif form["startdt#idx#"] gt "">
			<cfquery name="SaveDate" datasource="#dsn#">
				insert into course_dates (cid, startdt, releasedt, new, nearly_full, course_full, cancelled, eventid)
					values (#form.cid#, #createODBCDate(form["startdt#idx#"])#,
						<cfif form["releasedt#idx#"] GT "">
							#createODBCDate(form["releasedt#idx#"])#,
						<cfelse>
							null,
						</cfif>
						<cfif StructKeyExists(form,"new#idx#")>
							true,
						<cfelse>
							false,
						</cfif>
						<cfif StructKeyExists(form,"nearly_full#idx#")>
							true,
						<cfelse>
							false,
						</cfif>
						<cfif StructKeyExists(form,"course_full#idx#")>
							true,
						<cfelse>
							false,
						</cfif>
						<cfif StructKeyExists(form,"cancelled#idx#")>
							true,
						<cfelse>
							false,
						</cfif>
						#form["eventid#idx#"]#
					)
			</cfquery>
		</cfif>
	</cfloop>
	<!--- save photos --->
	<cfset porder=1>
	<cfloop index="idx" from=1 to=3>
		<cfif picname[idx] eq "" and not IsDefined("form.delpic#idx#") and IsDefined("GetPhoto.photo") and 
			GetPhoto.RecordCount ge idx>
			<cfset picname[idx]=GetPhoto["photo"][idx]>
		</cfif>
		<cfif picname[idx] gt "">
			<cfquery name="SavePhoto" datasource="#dsn#">
				insert into course_photos (cid, photo, porder)
					values (#form.cid#, '#picname[idx]#', #porder#)
			</cfquery>
			<cfset porder=porder+1>
		</cfif>
	</cfloop>
</cfif>

<cflocation url="courses.cfm?x=x" addtoken="no">
