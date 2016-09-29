<cfif not IsDefined("url.tid") or not IsNumeric(url.tid)>
	Please specify the theme you're interested in viewing.
	<cfabort>
</cfif>
<cfquery name="GetTheme" datasource="#dsn#">
	select * from themes where tid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.tid#">
</cfquery>
<cfif getTheme.recordCount GT "">
	<cfset meta_desc=Replace(getTheme.page_desc, "#chr(10)#", " ", "ALL")>
	<cfset meta_desc=Replace(meta_desc, "#chr(13)#", " ", "ALL")>
	<cfset title=getTheme.page_title>
</cfif>
<cfinclude template="templates/header.cfm">
<cfset thumbwid="100">
<cfquery name="GetCourses" datasource="#dsn#">
	select c.cid, c.name, c.tagline, c.length, t.theme, max(if(startdt IS NULL OR startdt<CURDATE(),0,1)) as ctype from (courses c inner join 
		(course_themes ct inner join themes t on ct.tid=t.tid)
		on c.cid = ct.cid) LEFT JOIN course_dates cd on c.cid = cd.cid
		<cfif url.tid neq "all">
			where ct.tid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.tid#"> and not hidden
		<cfelse>
			where not hidden
		</cfif>
		group by c.cid,c.name, c.tagline, c.length, t.theme
		order by t.theme, ctype desc, c.name
</cfquery>

<cfif GetCourses.RecordCount GT 0>
<h2 class="themedesc"><cfoutput>#GetCourses.theme#</cfoutput></h2>
<p class="themedesc"><cfoutput>#rereplace(getTheme.page_desc, "\n[\s]*", "<br/>","All")#</cfoutput></p>

<div class="theme" id="themelist">
<cfset ct=-1>
<cfoutput query="getCourses">
	<cfif ct neq ctype>
		<h4><cfif ctype gt 0>Currently Scheduled<cfelse><br/><br/>Not Currently Scheduled / Past Offerings</cfif></h4>
		<cfset ct = ctype>
	</cfif>
	
	<cfquery name="GetPhoto" datasource="#dsn#">
		select photo from course_photos where cid=#cid#
			order by porder
			limit 1
	</cfquery>
	<cfquery name="getNew" datasource="#dsn#">
		select count(startdt) as dates, sum(new) as news
			from course_dates where cid=#cid#
	</cfquery>
	<div class="thumb">
		<cfif GetPhoto.RecordCOunt GT 0>
			<cfset fn=fileloc& GetPhoto.photo>
			<cfif fileExists(fn)>
			<cfset imageCFC = createObject("component","image")>
			<!--- first, get current dimensions --->
			<cfset imgInfo = imageCFC.getImageInfo("", "#fn#")>
			<cfif imgInfo.width lt thumbwid>
				<cfset wid=imgInfo.width>
				<cfset ht=imgInfo.height>
			<cfelse>
				<cfset wid=thumbwid>
				<cfset ht=imgInfo.height*(thumbwid/imgInfo.width)>			
			</cfif>
			<img src="#site_url##GetPhoto.photo#" alt="#name#" border="0" width="#wid#" height="#ht#">
			<cfelse>&nbsp;
			</cfif>
		<cfelse>&nbsp;
		</cfif>
	</div>
	<cfquery name="GetInst" datasource="#dsn#">
		select fname, lname, i.iid from instructors i inner join course_inst ci on i.iid=ci.iid
			where ci.cid=#cid# order by lname
	</cfquery>
	<div class="desc">
		<cfset len=int(length)>
		<cfif len eq 0><cfset len=""><cfelse><cfset len=len & " "></cfif>
		<cfset dec=(length*100) mod 100>
		<cfif dec GT 0>
			<cfswitch expression="#dec#">
			<cfcase value=25>
				<cfset len=len & "1/4">
			</cfcase>
			<cfcase value=50>
				<cfset len=len & "1/2">
			</cfcase>
			<cfcase value=75>
				<cfset len=len & "3/4">
			</cfcase>
			<cfdefaultcase>
				<cfset len=length>
			</cfdefaultcase>
			</cfswitch>
		
		</cfif>
		<cfif getNew.dates gt 0 and getNew.dates eq getNew.news><span class="err">New! </span></cfif>
		<span class="cname"><a href="#site_url#course.cfm/cid/#cid#">#name#</a></span> (#len# <cfif length le 1>day<cfelse>days</cfif>)<cfif GetInst.RecordCount GT 0><br/></cfif>
			<cfloop query="GetInst"><span class="iname"><a href="#site_url#instructor.cfm/iid/#iid#">#fname# #lname#</a></span><cfif currentRow neq recordcount>, </cfif></cfloop>
		<br/>#tagline#
		
	</div>
	<br clear="all"/>
</cfoutput>
<cfoutput>
<br/><br/>
<a href="#site_url#coursesbydate.cfm/tid/#url.tid#">#GetCourses.Theme# Calendar</a>
</cfoutput>
</div>
<br clear="all"/>

<cfelse>
	Sorry, there are currently no courses being offered for this theme.
</cfif>


<cfinclude template="templates/footer.cfm">