<cfif not isdefined("Form.theme") and not IsDefined("url.del")>
	<cflocation url="themes.cfm">
</cfif>
<cfinclude template="header.cfm">

<cfif IsDefined("url.tid") and not IsDefined("form.tid")>
	<cfset form.tid=url.tid>
</cfif>

<cfif IsDefined("form.tid")>
	<!--- updating an exisiting theme --->
	<cfif IsDefined("url.del") and url.del eq "yes">
		<!--- delete theme --->
		<!--- first, check for courses associated with ONLY this theme --->
		<cfquery name="GetCourses" datasource="#dsn#">
			select c.cid, count(tid), sum(if(tid=#form.tid#,1,0)) from courses c
				INNER JOIN course_themes t on c.cid=t.cid
				GROUP BY c.cid
				HAVING count(tid)=1 AND sum(if(tid=#form.tid#,1,0))=1
		</cfquery>
		<cfquery name="DelCat" datasource="#dsn#">
			delete from themes where 
				tid=#form.tid#
		</cfquery>
		<!--- delete associated courses and associated files, if any --->
		<cfloop query="GetCourses">
			<cfquery name="GetPDF" datasource="#dsn#">
				select pdf from courses where cid=#cid#
			</cfquery>
			<cfquery name="GetPhotos" datasource="#dsn#">
				select photo from course_photos where cid=#cid#
			</cfquery>
			<cfquery name="DelCInst" datasource="#dsn#">
				delete from course_inst where cid=#cid#
			</cfquery>
			<cfquery name="DelCDate" datasource="#dsn#">
				delete from course_dates where cid=#cid#
			</cfquery>
			<cfquery name="DelCPhoto" datasource="#dsn#">
				delete from course_photos where cid=#cid#
			</cfquery>
			<!---cfquery name="DelCTheme" datasource="#dsn#">
				delete from course_themes where cid=#cid#
			</cfquery--->
			<cfquery name="DelCourse" datasource="#dsn#">
				delete from courses where cid=#cid#
			</cfquery>			
			<cfif FileExists("#fileloc##GetPDF.pdf#")>
				<cflock name="Filelock" Type="Exclusive" Timeout="30">
				<cffile action="DELETE"
					file="#fileloc##GetPDF.pdf#">
				</cflock>
			</cfif>
			<cfloop query="GetPhotos">
				<cfif FileExists("#fileloc##GetPhotos.photo#")>
					<cflock name="Filelock" Type="Exclusive" Timeout="30">
					<cffile action="DELETE"
						file="#fileloc##GetPhotos.photo#">
					</cflock>
			</cfif>
				
			</cfloop>
		</cfloop>
		<cfquery name="DelCTheme" datasource="#dsn#">
			delete from course_themes where tid=#form.tid#
		</cfquery>
		<cfquery name="DelTheme" datasource="#dsn#">
			delete from themes where tid=#form.tid#
		</cfquery>
	<cfelse>
		<cfquery name="SaveTheme" datasource="#dsn#">
			update themes set
				theme='#form.theme#',
				page_title='#form.page_title#',
				page_desc='#form.page_desc#'
			where tid=#form.tid#
		</cfquery>
	</cfif>	
<cfelse>
	<!--- adding a new theme --->
	
	<cfquery datasource="#dsn#" name="AddCat">
		Insert into themes
			(theme, page_title, page_desc)
			values ('#form.theme#', '#form.page_title#', '#form.page_desc#')
	</cfquery>
</cfif>

<cflocation url="themes.cfm?x=x" addtoken="no">
