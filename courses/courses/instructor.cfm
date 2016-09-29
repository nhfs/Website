<cfif not IsDefined("url.iid")><cflocation url="instructors.cfm" addtoken="no"></cfif>
<cfinclude template="templates/header.cfm">

<cfquery name="GetInst" datasource="#dsn#">
	select * from instructors
		where iid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.iid#">
</cfquery>
<cfif GetInst.RecordCount GT 0>
<div class="instructor">
<cfoutput query="GetInst">
	<h2 class="instname">#fname# #lname#</h2>
	<cfif photo GT "">
	<div class="instphoto">
	<img src="#site_url##photo#" border="0" alt="#fname# #lname#">
	</div></cfif>
	<span class="insthome">#home#</span><br/>
	#profile#
	<cfif url GT ""><p><a href="#url#" target="_new">#fname# #lname#'s Website</a></p></cfif>
	<cfquery name="GetCourses" datasource="#dsn#">
		select c.cid, c.name, c.tagline, max(if(startdt IS NULL or startdt<CURDATE(),0,1)) as ctype from (courses c 
			inner join course_inst ci on c.cid=ci.cid)
			left join course_dates cd on c.cid=cd.cid
			where ci.iid=#url.iid# and not hidden
			group by c.cid, c.name, c.tagline
			order by ctype desc, c.name
	</cfquery>
	<cfif GetCourses.RecordCOunt GT 0>
	<div class="indent">
	<cfset ct=-1>
	<cfloop query="GetCourses">
		<cfif ct neq ctype>
			<cfif ctype gt 0><h3>Current Courses Offered by #getInst.fname# #getInst.lname#</h3>
			<cfelse><br/><h3>Past Courses Offered by #getInst.fname# #getInst.lname#</h3>
			</cfif>
			<cfset ct=ctype>
		</cfif>
		<a href="#site_url#course.cfm/cid/#cid#">#name#</a><cfif tagline GT ""> - #tagline#</cfif><br/>
	</cfloop>
	</div>
	</cfif>

</cfoutput>
<cfelse>
	Sorry, no information was found for this instructor.
</cfif>
</div><br clear="all"/><br/>
<cfinclude template="templates/footer.cfm">