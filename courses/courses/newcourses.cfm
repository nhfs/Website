<cfinclude template="templates/header.cfm">
<cfset thumbwid="100">
<cfquery name="GetCourses" datasource="#dsn#">
	select c.cid, c.name, c.tagline, c.length, t.theme, t.tid, c.intergen,
	max(if(startdt IS NULL OR startdt<CURDATE(),0,1)) as ctype 
	from (courses c inner join (course_themes ct inner join themes t on ct.tid=t.tid)
	ON c.cid=ct.cid)
	INNER JOIN course_dates cd on c.cid = cd.cid
		where new and not hidden
		group by c.cid,c.name, c.tagline, c.length, c.intergen
		having max(if(startdt IS NULL OR startdt<CURDATE(),0,1)) = 1
		order by t.theme, c.name
</cfquery>

<cfif GetCourses.RecordCount GT 0>
<h2 class="themedesc">New Courses</h2>
<p class="themedesc">Look what's new! Check out the following new course dates, which were recently added to the calendar. 
They're organized by theme--beginning with Basketry and ending with Woodworking, so just keep scrolling!
</p>

<div class="theme" id="themelist">
<cfset ctid=0>
<cfoutput query="getCourses">
	<cfif ctid neq tid>
		<cfif ctid GT 0></div></cfif>
		<div class="themearea">
			<h3 class="themettl"><a href="coursesbytheme.cfm/tid/#tid#">#theme#</a></h3>
		<cfset ctid=tid>
	</cfif>

	<cfquery name="GetPhoto" datasource="#dsn#">
		select photo from course_photos where cid=#cid#
			order by porder
			limit 1
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
		<span class="cname"><a href="#site_url#course.cfm/cid/#cid#">#name#</a></span> (#len# <cfif length le 1>day<cfelse>days</cfif>)
		<cfif GetInst.RecordCount GT 0><br/></cfif>
			<cfloop query="GetInst"><span class="iname"><a href="#site_url#instructor.cfm/iid/#iid#">#fname# #lname#</a></span><cfif currentRow neq recordcount>, </cfif></cfloop>
		<cfquery name="getDates" datasource="#dsn#">
			select startdt from course_dates where 
				startdt >= CURDATE() and
					cid=#cid#
		</cfquery>	
		<br>
		Date(s): <cfloop query="getDates">#dateformat(startdt, 'mm/dd/yyyy')#<cfif currentrow lt recordcount>, </cfif></cfloop>		
		<br/>#tagline#
		
	</div>
	<br clear="all"/><br/>
</cfoutput>
<cfif ctid GT 0></div></cfif>
<cfoutput>
<br/><br/>
<a href="#site_url#coursesbydate.cfm">Calendar</a>
</cfoutput>
</div>
<br clear="all"/>

<cfelse>
	Sorry, we don't have any new courses to show you at this time.
</cfif>


<cfinclude template="templates/footer.cfm">