<cfprocessingdirective suppresswhitespace="Yes">
<cfset feechange=createDate(2012,3,1)>
<cfif not IsDefined("url.cid")>
	<cflocation addtoken="No" url="coursesbydate.cfm">
</cfif>
<cfset html5='yes'>
<cfinclude template="templates/header.cfm">
<script type="text/javascript" src="<cfoutput>#site_url#</cfoutput>templates/jquery-1.8.2.min.js"></script>
<script language="JavaScript" type="text/javascript">
function checkForm(fm) {
	if (fm.startdt.selectedIndex == 0) {
		alert ("Please select a date");
		return false;
	} else {
		return true;
	}
}

function toggle(id) {
	node=returnObjById(id);
	if (node.style.display == 'none') {
		node.style.display='block';
	} else {
		node.style.display='none';
	}
}

function switchtab(id) {
	$('.tabarea').hide();
	$('.tabs a').each(function() {
		$(this).removeClass("active");
		nid=$(this).prop('rel');
		if (nid == id) {
			$(this).addClass("active");
			$("#"+id).show();
		}
	});
}
</script>

<cfquery name="getCourse" datasource="#dsn#">
	select * from courses where cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
</cfquery>
<cfquery name="GetInst" datasource="#dsn#">
	select fname, lname, i.iid, profile, photo from instructors i INNER JOIN
		course_inst ci on i.iid=ci.iid
		WHERE ci.cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
</cfquery>
<cfquery name="GetDates" datasource="#dsn#">
	select startdt, new, course_full, nearly_full, length, eventid, releasedt, sum(if(course_full,1,0)) as nbrfull from course_dates inner join
		courses on course_dates.cid = courses.cid
		WHERE course_dates.cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#"> and not cancelled
		<!---
			and (releasedt is null or releasedt < #createODBCDate(now())# or 
			  (releasedt = #createODBCDate(now())# and CURTIME() > #createODBCTime(releasetime)#))--->
		GROUP BY startdt, course_full
		ORDER BY startdt
</cfquery>
<cfquery name="GetThemes" datasource="#dsn#">
	select theme, t.tid from course_themes ct INNER JOIN themes t
		ON ct.tid=t.tid
		WHERE ct.cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
		ORDER BY theme
</cfquery>
<cfoutput query="getCourse">
<div class="coursepage">
<div class="tabs">
	<a href="##" onclick="switchtab('over'); return false" rel="over"><div class="tab">Overview</div></a>
	<a href="##" onclick="switchtab('tools'); return false" rel="tools"><div class="tab">Tools</div></a>
	<a href="##" onclick="switchtab('inst'); return false" rel="inst"><div class="tab">Instructor</div></a>
</div>
<h2>#name#</h2>
<div class="themes"><cfloop query="GetThemes"><a href="#site_url#coursesbytheme.cfm/tid/#tid#">#theme#</a><cfif CurrentRow neq RecordCount>, </cfif></cfloop></div>
<div class="desc">

<div id="over" class="tabarea">
	<div class="iname">
		<cfloop query="GetInst">
			<a href="#site_url#instructor.cfm/iid/#iid#">#fname# #lname#</a><cfif CurrentRow neq RecordCount>, </cfif>
		</cfloop>
	</div>
	#descrip#
	<h3>Dates</h3>
	<cfif GetDates.RecordCount GT 0>
		<cfset today=createDate(year(now()), month(now()), day(now()))>
		<cfset events=arrayNew(1)>
		<cfset dates=arrayNew(1)>
		<cfloop query="GetDates">
			<!--- figure end date --->
			<cfset edt=dateadd('d',ceiling(length)-1,startdt)>
			<cfif new eq true> <b class="err">New!</b></cfif>
			#dateFormat(startdt, 'ddd, mmm dd, yyyy')#<cfif edt neq startdt> - #DateFormat(edt, 'ddd, mmm dd, yyyy')#</cfif><cfif nearly_full eq true> <span class="nearly">Nearly Full</span></cfif>
			<cfif course_full eq true> <b class="err">Course Full - Please call for wait list</b></cfif>
			<cfset released=true>
			<cfif releasedt GT 0 and (datecompare(releasedt, today) GT 0 or 
				(datecompare(releasedt, today) eq 0 and hour(releasetime) GT hour(now())) or
				(datecompare(releasedt, today) eq 0 and hour(releasetime) eq hour(now()) and minute(releasetime) gt minute(now())))>
				<div class="indent"><i>(Available for registration on #dateformat(releasedt, 'mm/dd/yyyy')# after #timeformat(releasetime, 'hh:mmtt')#)</i></div>
				<cfset released=false>
			<cfelseif getCourse.early_tuition GT 0 and startdt ge feechange>
				<div class="indent">
				<i>(Early bird tuition deadline: #dateformat(dateAdd('ww', -6, startdt), 'mm/dd/yy')#)</i>
				</div>
			</cfif>
			<cfif eventid GT 0>
				<cfif not ArrayisEmpty(events)>
					<cfset found=false>
					<cfloop index="x" from="1" to="#arrayLen(events)#">
						<cfif events[x] eq eventid>
							<cfloop index="y" from="1" to="#x#">*</cfloop>
							<cfset dates[x]=dates[x] & "," & startdt>
							<cfset found=true>
						</cfif>
					</cfloop>
					<cfif not found>
						<cfset arrayAppend(events, eventid)>
						<cfset arrayAppend(dates, startdt)>
						<cfloop index="y" from="1" to="#arraylen(events)#">*</cfloop>
					</cfif>
				<cfelse>
					<cfset arrayAppend(events,eventid)>*
					<cfset arrayAppend(dates, startdt)>
				</cfif>
			</cfif>
			<cfif released and not (GetCourse.early_tuition GT 0 and startdt ge feechange)>
				<br/>
			</cfif>
		</cfloop>
	<cfelseif daily eq true>
		This experience available most dates.  Please call for details and reservations.
	<cfelse>
		No classes currently scheduled
	</cfif>
	
		<br/>
	<h3>Course Details</h3>
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

	<div class="col1">Length in days:</div><div class="col2">#len#<cfif length_desc gt "">-#length_desc#</cfif></div>
	<br/>
	<cfif hours GT ""><div class="col1">Hours: </div><div class="col2">#hours#</div><br/></cfif>
	<div class="col1">Tuition:</div>
	<div class="col2">
		<cfif now() lt feechange>
			<cfset tuit=0>
			<cfif peak_tuition GT 0 or offpeak_tuition GT 0>
				<cfif peak_tuition GT 0>
					#DollarFormat(peak_tuition)#<cfif paybyproject eq true> per project<cfelse> per student</cfif>
					<cfif offpeak_tuition GT 0> (May 1-Oct 31)</cfif>
					<cfif peak_desc GT ""> #peak_desc#</cfif><br/>
					<cfset tuit=peak_tuition>
				</cfif>
				<cfif offpeak_tuition GT 0>#DollarFormat(offpeak_tuition)#<cfif paybyproject eq true> per project<cfelse> per student</cfif>
					(Nov 1-Apr 30)
					<cfif offpeak_desc GT ""> #offpeak_desc#</cfif><br/>
					<cfset tuit=offpeak_tuition>
				</cfif>
			<cfelse>
				None
			</cfif>
		</cfif>
		<cfif reg_tuition gt 0 and now() ge feechange>
			<cfif (now() lt feechange and reg_tuition neq tuit) or now() ge feechange>
				#DollarFormat(reg_tuition)#<cfif paybyproject eq true> per project<cfelse> per student</cfif>
				<cfif now() lt feechange and not (reg_tuition eq peak_tuition and offpeak_tuition eq 0) and 
					not (reg_tuition eq offpeak_tuition and offpeak_tuition GT 0)> (after #dateFormat(feechange, 'mmm d')#)<cfelse> (Regular rate)</cfif>
				<cfif reg_desc GT ""> #reg_desc#</cfif><br/>
			</cfif>	
			<cfif early_tuition GT 0>
				#DollarFormat(early_tuition)#<cfif paybyproject eq true> per project<cfelse> per student</cfif>
				<a href="##" onclick="toggle('ebrate'); return false;">(Early-bird rate)</a>
				<cfif early_desc GT ""> #early_desc#</cfif>			
				<div class="ctext" id="ebrate" style="display:none">
				The Early Bird Rate is available for students who sign up more than 6 weeks before the course begins.
				</div>
				<br/>
			</cfif>
			
		<cfelseif now() ge feechange>
			None
		</cfif>
	</div><br/>

	<cfif material_amt GT 0 or materials GT "">
		<div class="col1">Materials:</div>
		<div class="col2"><cfif material_amt GT 0>#dollarformat(material_amt)#</cfif><cfif materials gt ""> #materials#</cfif></div><br/>
	</cfif>
	<cfif levels GT "">
		<div class="col1">Levels:</div>
		<div class="col2">#levels#</div><br/>		
	</cfif>
	<cfif intergen GT "">
		<div class="col1">Age with adult:</div>
		<div class="col2">#intergen#<a href="#site_url#../howtoregister/policies.htm##agepolicy" class="cdata">&nbsp;&nbsp;&nbsp;(Review age guidelines/policy)</a><br/></div>
	</cfif>
	<br clear="all"/>
	<cfif isDefined("events") and not arrayisEmpty(events)>
		<cfloop index="x" from="1" to="#arrayLen(events)#">
			<cfquery name="GetEvent" datasource="#dsn#">
				select * from courses where cid=#events[x]#
			</cfquery>
			<cfif GetEvent.RecordCount GT 0>
				<br/>
				<h3><cfloop index="y" from="1" to="#x#">*</cfloop>
					The
					<cfif find(",", dates[x])>
						<cfloop index="idx" from="1" to="#listLen(dates[x])#">
							<cfif idx gt 1 and idx lt listLen(dates[x])>,</cfif>
							<cfif idx eq listLen(dates[x])>and</cfif>
							#dateformat(listGetAt(dates[x], idx), 'mmm dd, yyyy')#
						</cfloop>
						courses are
					<cfelse>
						#dateformat(dates[x], 'mmm dd, yyyy')# course is
					</cfif>
					part of the <cfif getEvent.url gt ""><a href="#GetEvent.url#"></cfif>
					#getevent.name#<cfif GetEvent.url gt ""></a></cfif> event</h3>
			</cfif>
		</cfloop>
	</cfif>
	<cfif GetDates.RecordCount GT 0 and GetDates.nbrfull lt GetDates.RecordCount>
	<br/>
	<form action="#site_url#register.cfm" method="POST" onsubmit="return checkForm(this)">
	<input type="hidden" name="cid" value="#url.cid#">
	<div class="col1a">
			<select name="startdt">
			<option value="0">--Select a Start Date--</option>
			<cfloop query="GetDates">
				<cfif course_full neq true and startdt ge now() and (releasedt eq "" or
					datecompare(releasedt, today) lt 0 or 
					(datecompare(releasedt, today) eq 0 and hour(releasetime) LT hour(now())) or
					(datecompare(releasedt, today) eq 0 and hour(releasetime) eq hour(now()) and minute(releasetime) Lt minute(now())))>
				<option value="#startdt#">#DateFormat(startdt,'mm/dd/yyyy')#</option>
				</cfif>
			</cfloop>
			
			
			</select>
	</div>
	<div class="col2a"><input type="submit" value="Register Now"></div>
	</form>

	<br/>
	</cfif>

<cfquery name="GetData" datasource="#dsn#">
	select * from course_extra
</cfquery>

<div class="cdata">
Making it happen:
<ul>
<cfloop query="GetData">
<li><a href="##" onclick="toggle('d#eid#'); return false;">#etitle#</a>
<div class="ctext" id="d#eid#" style="display:none">#etext#
</div></li>
</cfloop>
</ul>
</div>
</div> <!-- tabarea -->

<div id="tools" class="tabarea">
<cfif getCourse.tools gt "">
	<h2>You will need the following tools for this class:</h2>
	#getCourse.tools#
<cfelse>
	<p>
	All required tools/materials are provided by your instructor and North House and will be available once the course 
	begins. Once registered, students will receive a confirmation packet in the mail with optional tool recommendations and 
	suggested reading.
</p>
</cfif>
</div> <!-- tabarea -->

<div class="tabarea" id="inst">
<cfloop query="getInst">
<cfif photo GT "">
	<img src="#site_url##getINst.photo#" alt="#getInst.fname# #getINst.lname#" class="imgleft">
</cfif>
	<a href="#site_url#instructor.cfm/iid/#iid#">#fname# #lname#</a><br><br>
	#profile#
	<br><br>
	<a href="#site_url#instructor.cfm/iid/#iid#">More about #fname# #lname#</a>
	<cfif currentrow neq recordcount><hr></cfif>
	
</cfloop>
</div>

</div>  <!--- desc --->

<div class="photos">
<cfif pdf GT "">
	<a href="#site_url##pdf#" target="_new">Click here for<br/><cfif pdftitle GT "">#pdftitle#<cfelse>more info</cfif></a><br/><hr/>
</cfif>
<cfquery name="getPhotos" datasource="#dsn#">
	select * from course_photos
		where cid = <cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
		order by porder
</cfquery>
<cfloop query="getPhotos">
	<img src="#site_url##photo#" border="0" alt="#GetCourse.name#"><br/><br/>
</cfloop>
</div>
</div>  <!--- coursepage --->
</cfoutput>
<br clear="all"/><br/>

<cfinclude template="templates/footer.cfm">
</cfprocessingdirective>
<script type="text/javascript">
	switchtab('over');
	$('.tabs a').hover(
		function() {
			$(this).addClass("hover");
		},
		function() {
			$(this).removeClass("hover");
		}
	);
					
</script>