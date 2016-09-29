<cfinclude template="templates/header.cfm">
<cfprocessingdirective suppresswhitespace="Yes">
<cfif IsDefined("url.tid")>
	<cfquery name="GetTheme" datasource="#dsn#">
		select theme from themes where tid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.tid#">
	</cfquery>
</cfif>
<h2 class="caltitle">Courses by Date<cfif IsDefined("url.tid") and GetTheme.RecordCount GT 0> - <cfoutput>#getTheme.theme#</cfoutput></cfif></h2>
<cfoutput>
<cfif isDefined("url.tid") and getTheme.recordCount GT 0>
Note: <div class="intheme">&nbsp;#getTheme.theme# classes are highlighted with a &nbsp;white background. </div><br/>
</cfif>

<div class="arrows">
<cfif isDefined("url.mo") and isNumeric(url.mo)>
	<cfset thisdt=createdate(right(url.mo,len(url.mo)-2),left(url.mo, 2),1)>
<cfelse>
	<cfset thisdt=now()>
</cfif>
<cfset arrowmo=dateadd('m',-1,thisdt)>
<a href="#site_url#coursesbydate.cfm/mo/#dateformat(arrowmo,'mmyyyy')#<cfif isDefined("url.tid")>/tid/#url.tid#</cfif>"><- Previous Month</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<cfset arrowmo=dateadd('m',1,thisdt)>
<a href="#site_url#coursesbydate.cfm/mo/#dateformat(arrowmo,'mmyyyy')#<cfif isDefined("url.tid")>/tid/#url.tid#</cfif>">Next Month -></a>
</div><br/>
Please select a month: <select name="month" id="month" onchange="location.href='#site_url#coursesbydate.cfm?mo='+this.options[this.selectedIndex].value<cfif isDefined("url.tid")>+'&tid=#url.tid#'</cfif>;">

<option value="0">-- Please select --</option>
<option value="all">Entire Year</option>
<cfset idx=month(now())-2>
<cfset yr=numberFormat(year(now()),'09')>
<cfloop index="x" from="1" to="15">
	<cfif idx LT 1><cfset idx=idx+12><cfset yr=numberformat(yr-1,"09")></cfif>
	<cfif idx GT 12><cfset idx=1><cfset yr=numberformat(yr+1,"09")> </cfif>
	<option value="#numberformat(idx,'09')##yr#" <cfif (IsDefined("url.mo") and url.mo eq "#numberformat(idx,'09')##yr#") or (not IsDefined("url.mo") and month(now()) eq idx and year(now()) eq yr)>selected</cfif>>#MonthAsString(idx)#, #yr#</option>
	<cfset idx=idx+1>
</cfloop>
</cfoutput>
</select>
<div class="calendar" id="calendar">

<cfif not IsDefined("url.mo") or (not IsNumeric(url.mo) and url.mo neq "all")>
	<cfset url.mo=NumberFormat(month(now()),"09") & Year(now())>
</cfif>
<cfif url.mo neq "all">
	<cfset mo=left(url.mo,2)>
	<cfset yr=right(url.mo,4)>
	<cfset months=mo>
<cfelse>
	<cfset yr=year(now())>
	<cfset mo=1>
	<cfset months=mo+11>
</cfif>
<!--- formatting directives --->
<cfset pxperday=106>
<cfset pxperday_off=8>
<cfset digperday=11>
<cfset digpad=8>
<cfset evtdigperday=10>
<cfset boxheight=18>
<cfset box_pad=22>
<cfset boxmin_ht=100>

<cffunction name="SetupWeek">
	<cfargument name="startdt" required="true">
	<cfargument name="enddt" required="true">
	<cfargument name="urltid" required="true" default="0">

<!--- need to figure out how many classes will appear on one day so we know how tall to make the row --->
	<cfquery name="GetWeek" dbtype="query">
		select length, cday, name, course_full, new, isevent, url, cid <cfif urltid GT 0>, tid</cfif> from GetCourses
			where ((cday+length) > #startdt# and (cday+length) <= #enddt#) or
				(cday between #startdt# and #enddt#) or 
				(cday < #startdt# and cday+length > #enddt#)
			order by cday, cid<cfif urltid GT 0>, tid</cfif>
	</cfquery>
	<cfset dy=-30>
	<cfset ctr=0>
	<cfset maxcourses=1>
	<cfset cdates=ArrayNew(1)>
	<!---cfloop index="x" from=1 to=7>
		<cfset cdates[x]=ArrayNew(1)>
	</cfloop--->

	<cfloop query="GetWeek">
		<cfif cday GT dy>
			<cfif dy eq -30 or cday gt startdt>
				<cfset ctr=ctr+1>
				<cfset cdates[ctr]=StructNew()>
				<cfif cday lt startdt>
					<cfset cdates[ctr].day=startdt>
				<cfelse>
					<cfset cdates[ctr].day=cday>
				</cfif>
				<cfset cdates[ctr].courses=StructNew()>
				<cfif ctr GT 1 and isStruct(cdates[ctr-1])>
					<cfset keys=StructKeyList(cdates[ctr-1].courses)>
					<cfloop index="i" list="#keys#">
						<cfif cdates[ctr-1].courses["#i#"].end gt cday>
							<cfset cdates[ctr].courses["#i#"]=cdates[ctr-1].courses["#i#"]>
						</cfif>
					</cfloop>
				</cfif>
			</cfif>
			<cfset dy=cday>
			<cfset currcid=0>
		</cfif>
		<cfif cday lt startdt and (cday+length) gt enddt>
			<cfset leng=7>
			<cfset nam="..." & name>
		<cfelseif cday lt startdt>
			<cfset leng=length-(startdt-cday)>
			<cfset nam="..." & name>
		<cfelseif cday+length-1 gt enddt>
			<cfset leng=(enddt+1)-cday>
			<cfset nam=name>
		<cfelse>
			<cfset leng=length>
			<cfset nam=name>
		</cfif>
		<cfset leng=ceiling(leng)>
		<cfif course_full eq true><cfset nam="<b>FULL:</b> " & nam>
		<cfelseif new eq true><cfset nam="<b>NEW:</b> " & nam></cfif>
		<cfset found=false>
		<cfset y=1>
		<cfif cid neq currcid>
			<cfloop condition="found eq false">
				<cfif not (StructKeyExists(cdates[ctr].courses,y) and cdates[ctr].courses["#y#"].end gt dy)>
					<cfset cdates[ctr].courses["#y#"]=StructNew()>
					<cfset cdates[ctr].courses["#y#"].day=cdates[ctr].day>				
					<cfset cdates[ctr].courses["#y#"].end=cday+length>
					<cfset cdates[ctr].courses["#y#"].cid=cid>
					<cfif IsEvent eq true>
						<cfset cdates[ctr].courses["#y#"].url=url>
					<cfelse>
						<cfset cdates[ctr].courses["#y#"].url="#site_url#course.cfm/cid/" & cid>
					</cfif>
					<cfset cdates[ctr].courses["#y#"].name=nam>
					<cfif isevent eq true>
						<cfset cdates[ctr].courses["#y#"].event=true>
					<cfelse>
						<cfset cdates[ctr].courses["#y#"].event=false>
					</cfif>
					<cfset cdates[ctr].courses[y].top=boxheight*(y-1)>
					<cfset cdates[ctr].courses["#y#"].len=(leng*pxperday)-pxperday_off>
					<cfif isevent eq true>
						<cfset cdates[ctr].courses["#y#"].digits=leng*evtdigperday>	
					<cfelse>
						<cfset cdates[ctr].courses["#y#"].digits=leng*digperday>
					</cfif>
					<cfif course_full>
						<cfset cdates[ctr].courses["#y#"].digits=cdates[ctr].courses["#y#"].digits+7>
					<cfelseif new>
						<cfset cdates[ctr].courses["#y#"].digits=cdates[ctr].courses["#y#"].digits+6>
					</cfif>
					<cfif leng GT 2 and isevent eq false>
						<cfset cdates[ctr].courses["#y#"].digits=cdates[ctr].courses["#y#"].digits+((leng-2)*digpad)>
					</cfif>
					<cfif urltid GT 0 and tid eq 0>
						<cfset cdates[ctr].courses["#y#"].intheme=true>
					<cfelse>
						<cfset cdates[ctr].courses["#y#"].intheme=false>
					</cfif>
					<cfset found=true>
				</cfif>
				<cfset y=y+1>
			</cfloop>
		</cfif>

		<cfif StructCount(cdates[ctr].courses) GT maxcourses>
			<cfset maxcourses=StructCount(cdates[ctr].courses)>
		</cfif>
		
		<cfset currcid=cid>
	</cfloop>
	<cfif (maxcourses*boxheight)+box_pad gt boxmin_ht>
		<cfset rowht=(maxcourses*boxheight)+box_pad>
	<cfelse>
		<cfset rowht=boxmin_ht>
	</cfif>
	<!---cfdump var="#cdates#"--->
</cffunction>

<cfoutput>
<cfif isDefined("url.tid")><cfset urltid=url.tid><cfelse><cfset urltid=0></cfif>
<cfloop index="idx" from="#mo#" to="#months#">
	<cfset firstOfTheMonth = createDate(yr, idx, 1)>
	<cfquery name="GetCourses" datasource="#dsn#">
		select distinct c.name, c.length, c.isevent, c.url, cd.course_full, cd.new,
		if(month(cd.startdt)< #idx#,day(cd.startdt)-#daysinmonth(dateadd('d',-1,firstOfTheMonth))#,day(cd.startdt)) as cday, 
		month(startdt) as cmo, <cfif isDefined("url.tid")>if(ct.tid=#url.tid#,0,1) as tid, </cfif> cd.* from (courses c inner join course_dates cd 
			on c.cid = cd.cid)
			<cfif IsDefined("url.tid")>
			INNER JOIN course_themes ct on c.cid=ct.cid
			</cfif>
			where (month(cd.startdt) = #idx# or
				month(date_add(cd.startdt, INTERVAL ceiling(length)-1 day)) = #idx#)
				and year(startdt)=#yr#
				and not cancelled
				and not hidden
				<!---cfif getAuthUser() EQ "">
				and (cd.releasedt is null or cd.releasedt < #createODBCDate(now())# or 
					(cd.releasedt = #createODBCDate(now())# and CURTIME() > #createODBCTime(releasetime)#))
				</cfif--->					
				<!---cfif isDefined("url.tid")>and ct.tid=#url.tid#</cfif--->
	</cfquery>

	<div class="month">#monthAsString(idx)#, #yr#</div>
	<ul class="days">
		<li>Mon</li>
		<li>Tue</li>
		<li>Wed</li>
		<li>Thu</li>
		<li>Fri</li>
		<li class="weekend">Sat</li>
		<li class="weekend">Sun</li>
	</ul>
	<cfset lines=0>
	<!--- get day of first of month --->
	<cfset dow = dayofWeek(firstOfTheMonth)>
	<cfif dow eq 1>
		<cfset dow=7>
	<cfelse>
		<cfset dow=dow-1>  <!--- their week starts on Monday --->
	</cfif>

	<cfset foo=SetupWeek(2-dow,8-dow,urltid)>
	<!--- pad days before month begins --->
	<cfset pad = dow - 1>
	<cfset lastmo=dateadd('d',-(pad),firstOfTheMonth)>
	<ul>
	<cfset ctr=1>
	<cfif pad gt 0>
		<cfset dy=2-dow>
		<cfloop index="x" from="1" to="#pad#">
			<li class="pad" style="height: #rowht#px">&nbsp;
			<cfif ArrayLen(cdates) ge ctr and cdates[ctr].day eq dy>
				<cfset keys=StructKeyList(cdates[ctr].courses)>
				<cfloop index="y" list="#keys#">
					<cfif cdates[ctr].courses["#y#"].day eq dy>
						<div class="calcont" style="top: #cdates[ctr].courses["#y#"].top#px;"><div class="course <cfif cdates[ctr].courses["#y#"].event>event</cfif><cfif cdates[ctr].courses["#y#"].intheme> intheme</cfif>" style="width: #cdates[ctr].courses["#y#"].len#px;">
						<cfif cdates[ctr].courses["#y#"].url GT "">
							<a href="#cdates[ctr].courses["#y#"].url#" title="<cfif left(cdates[ctr].courses["#y#"].name,12) eq '<b>FULL:</b>'>#right(cdates[ctr].courses["#y#"].name,len(cdates[ctr].courses["#y#"].name)-12)#<cfelseif left(cdates[ctr].courses["#y#"].name,11) eq '<b>NEW:</b>'>#right(cdates[ctr].courses["#y#"].name,len(cdates[ctr].courses["#y#"].name)-11)#<cfelse>#cdates[ctr].courses["#y#"].name#</cfif>">
						</cfif>#left(cdates[ctr].courses["#y#"].name,(cdates[ctr].courses["#y#"].digits))#<cfif cdates[ctr].courses["#y#"].digits lt len(cdates[ctr].courses["#y#"].name)>...</cfif><cfif cdates[ctr].courses["#y#"].url GT ""></a></cfif></div></div>
					</cfif>
				</cfloop>
				<cfset ctr=ctr+1>
		   </cfif>

		   </li>
		   <cfset dy=dy+1>
   		</cfloop>
	</cfif>

	<!--- output days --->
	<cfset days = daysInMonth(firstOfTheMonth)>

	<cfset counter = pad + 1>


	<cfloop index="x" from="1" to="#days#">
	   <li style="height: #rowht#px"<cfif counter gt 5> class="weekend"</cfif>>#x#
			<cfif ArrayLen(cdates) ge ctr and cdates[ctr].day eq x>
				<cfset keys=StructKeyList(cdates[ctr].courses)>
				<cfloop index="y" list="#keys#">
					<cfif cdates[ctr].courses["#y#"].day eq x>
						<div class="calcont" style="top: #cdates[ctr].courses["#y#"].top#px;"><div class="course <cfif cdates[ctr].courses["#y#"].event>event</cfif><cfif cdates[ctr].courses["#y#"].intheme> intheme</cfif>" style="width: #cdates[ctr].courses["#y#"].len#px;">
						<cfif cdates[ctr].courses["#y#"].url GT "">
							<a href="#cdates[ctr].courses["#y#"].url#" title="<cfif left(cdates[ctr].courses["#y#"].name,12) eq '<b>FULL:</b>'>#right(cdates[ctr].courses["#y#"].name,len(cdates[ctr].courses["#y#"].name)-12)#<cfelseif left(cdates[ctr].courses["#y#"].name,11) eq '<b>NEW:</b>'>#right(cdates[ctr].courses["#y#"].name,len(cdates[ctr].courses["#y#"].name)-11)#<cfelse>#cdates[ctr].courses["#y#"].name#</cfif>">
						</cfif>#left(cdates[ctr].courses["#y#"].name,(cdates[ctr].courses["#y#"].digits))#<cfif cdates[ctr].courses["#y#"].digits lt len(cdates[ctr].courses["#y#"].name)>...</cfif><cfif cdates[ctr].courses["#y#"].url GT ""></a></cfif></div></div>
					</cfif>
				</cfloop>
				<cfset ctr=ctr+1>
		   </cfif>

	   </li>
	   <cfset counter = counter + 1>
	   <cfif counter is 8>
    	  </ul>
	      <cfif x lt days>
    	     <cfset counter = 1>
			 <cfset foo=SetupWeek(x+1,x+7, urltid)>
			 <cfset lines=lines+1>
			 <cfset ctr=1>
        	 <ul>
	      </cfif>
	   </cfif>
	</cfloop>

	<!--- pad days after end of month --->
	<cfif counter is not 8>
		<cfset endPad = 8 - counter>
		<cfloop index="x" from="1" to="#endpad#">
		   <li class="pad" style="height: #rowht#px">&nbsp;</li>
	   	</cfloop>
	</cfif>
	</ul>
<br clear="all"/><br/>
</cfloop>
</cfoutput>

</div>
<br clear="all"/>
</cfprocessingdirective>
<cfinclude template="templates/footer.cfm">
