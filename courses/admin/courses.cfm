<cfinclude template="header.cfm">
<link type="text/css" rel="stylesheet" href="jquery/css/ui-lightness/jquery-ui-1.8.4.custom.css" />
<script type="text/javascript" src="jquery/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.4.custom.min.js"></script>
<script language="JavaScript">
	function checkform() {
		fm=document.course;
		ok=true;
		msg="";
		
		if ((fm.iid1.selectedIndex > 0 && 
			(fm.iid1.selectedIndex == fm.iid2.selectedIndex || fm.iid1.selectedIndex == fm.iid3.selectedIndex)) ||
			(fm.iid2.selectedIndex > 0 && fm.iid2.selectedIndex == fm.iid3.selectedIndex)) {
			msg += "You've specified the same instructor more than once.  Please remove the duplicate instructor(s)\n";
			ok=false;
		}
		
		if (fm.tid1.selectedIndex == 0 && fm.tid2.selectedIndex == 0 && !fm.isevent.checked) {
			msg += "Please specify a theme for this course\n";
			ok=false;
		}
		
		if (fm.tid1.selectedIndex > 0 && fm.tid1.selectedIndex == fm.tid2.selectedIndex) {
			msg += "You've specified the same theme more than once.  Please remove the duplicate theme\n";
			ok=false;
		}
		
/*		if ((fm.peak_tuition.value == "" || fm.peak_tuition.value == 0) && 
			(fm.offpeak_tuition.value == "" || fm.offpeak_tuition.value == 0)  && !fm.isevent.checked) {
			msg += "Please specify a tuition amount for the course.\n";
			ok = false;
		}
*/				
		if (fm.length.value <= 0) {
			msg += "Please specify the number of days for this course\n";
			ok = false;
		}
		
		if ((fm.startdt1.value > "" || fm.startdt2.value > "" || fm.startdt3.value > "" || 
			fm.startdt4.value > "" || fm.startdt5.value > "" || fm.startdt6.value > "" || 
			fm.startdt7.value > "" || fm.startdt8.value > "" || fm.startdt9.value> "" || fm.startdt10.value > "")
			&& fm.daily.checked) {
			msg += "Please do not specify a course date if you've checked the 'Course available most dates' box";
			ok=false;
		}
		

		if ((fm.startdt1.value <= "" && fm.releasedt1.value > "") ||(fm.startdt2.value <= "" && fm.releasedt2.value > "") ||
			(fm.startdt3.value <= "" && fm.releasedt3.value > "") || (fm.startdt4.value <= "" && fm.releasedt4.value > "") ||
			(fm.startdt5.value <= "" && fm.releasedt5.value > "") || (fm.startdt6.value <= "" && fm.releasedt6.value > "") ||
			(fm.startdt7.value <= "" && fm.releasedt7.value > "") || (fm.startdt8.value <= "" && fm.releasedt8.value > "") ||
			(fm.startdt9.value <= "" && fm.releasedt9.value > "") || (fm.startdt10.value <= "" && fm.releasedt10.value > "")) {
			msg += "You have specified a course release date without a start date.  If you have a release date you must also have a course start date.";
			ok=false;
		}
			

		if (!ok) {
			alert(msg);
		} else {
			if (fm.hidden.checked) {
				ok=confirm('Are you sure you want to hide this course on the website?');
			}
		}
		return ok;
	}
</script>

<cfquery name="GetCourses" datasource="#dsn#">
	select theme, courses.cid, name, isevent, rank from courses LEFT JOIN 
		(Course_themes INNER JOIN themes on course_themes.tid=themes.tid)
		on courses.cid=course_themes.cid
		  order by
		theme, name
</cfquery>
<div class="sidebar">
	<a href="courses.cfm">New Course or Event</a><br/>		
	<cfset th="">
	<cfoutput query="GetCourses">
		<cfif rank neq 2>
			<cfif theme neq th>
				<cfif th neq ""></div><br/></cfif>
				<cfset th=theme>
				<a href="javascript:toggle('#theme#')"><b>#theme#</b></a>
				<div style="display: none;" id="#theme#">
			</cfif>
			<a href="courses.cfm?cid=#cid#"><cfif GetCourses.isEvent eq true><span class="event"></cfif>
			#left(name,23)#<cfif len(name)GT 23>...</cfif>
			<cfif GetCourses.isEvent eq true></span></cfif></a><br/>
		</cfif>
	</cfoutput>
	<cfif th neq ""></div><br/></cfif>
	<a href="javascript:toggle('all')"><b>All Courses</b></a>
	<div style="display: none;" id="all">
	<cfquery name="GetAll" dbtype="query">
		select * from GetCourses order by name
	</cfquery>
	<cfset c=0>
	<cfoutput query="GetAll">
		<cfif c neq cid>
		<a href="courses.cfm?cid=#cid#"><cfif isEvent eq true><span class="event"></cfif>
		#left(name,23)#<cfif len(name) GT 23>...</cfif><cfif isEvent eq true></span></cfif></a><br/>
		<cfset c=cid>
		</cfif>
	</cfoutput>
	</div>
	<br/><br/>
	<a href="clearnew.cfm" onclick="if (confirm('Are you sure you want to clear all the new courses?')){return true;} else {return false;}">Clear all new courses</a><br>
	<a href="clearhid.cfm" onclick="if (confirm('Are you sure you want to un-hide all the courses?')){return true;} else {return false;}">Un-hide all hidden courses</a><br>
	<a href="../courses/coursesbydate.cfm" target="_new">Review all classes by date</a>
</div>
<div class="form">
<h2>Update Course Information</h2>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Course information has been updated!</B><P>
		</P>
	</cfoutput>
</cfif>
<cfif IsDefined("url.c")>
	<b>New courses have been cleared!</b><p></p>
</cfif>

<cfquery name="GetInsts" datasource="#dsn#">
	select iid, concat(lname, ', ', fname) as name from instructors order by
		lname
</cfquery>
<cfquery name="GetThemes" datasource="#dsn#">
	select tid, theme from themes order by
		theme
</cfquery>
<cfquery name="GetEvents" datasource="#dsn#">
	select cid, name from courses where isevent
		order by name
</cfquery>
<cfoutput>
<cfform action="course_update.cfm" method="POST" name="course" enctype="multipart/form-data" scriptsrc="http://kite.boreal.org/cfide/scripts" onSubmit="return checkform();">
<cfif IsDefined("url.cid")>
	<cfquery name="GetCourse" datasource="#dsn#">
		select * from courses where
			cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
	</cfquery>
	<cfif GetCourse.recordcount GT 0>
		<cfquery name="GetCInsts" datasource="#dsn#">
			select iid from course_inst
				where cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
		</cfquery>
		<cfquery name="GetCDates" datasource="#dsn#">
			select * from course_dates where cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
				order by startdt
		</cfquery>
		<cfquery name="GetCThemes" datasource="#dsn#">
			select tid from course_themes where cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
				order by rank
		</cfquery>
		<cfquery name="GetCPhotos" datasource="#dsn#">
			select * from course_photos where cid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.cid#">
				order by porder
		</cfquery>
		<cfoutput>
		<input type="hidden" name="cid" value="#url.cid#">
		</cfoutput>
	</cfif>
</cfif>
<input type="submit" value="Save Course Information">
<cfif IsDefined("getCourse.name")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the information for #getCourse.name#?')) { location.href='course_update.cfm?cid=#url.cid#&del=yes'; }" value="Delete Course">
</cfif>
<br/><br/>

<label for="name">Course title:</label>
<cfif IsDefined("GetCourse.name")><cfset val=getCourse.name><cfelse><cfset val=""></cfif>
<cfinput type="text" name="name" id="name" size="50" value="#val#" maxlength="100" required="yes" message="Please enter the course name">
<br/><br/>
Instructor(s):<br/>
<div class="indent">
<cfset iidnum=1>
<cfif IsDefined("getCInsts.recordCount")>
	<cfloop query="getCInsts">
		<cfset val=GetCInsts.iid>
		<label for="iid#iidnum#">Instructor #iidnum#:</label>
		<cfselect name="iid#iidnum#" id="iid#iidnum#" query="GetInsts" display="name" value="iid" selected="#val#" queryPosition="below">
			<option value="0">--Please select--</option>
		</cfselect><br/>
		<cfset iidnum=iidnum+1>				
	</cfloop>
</cfif>
<cfloop index="idx" from="#iidnum#" to=3>
	<label for="iid#idx#">Instructor #idx#:</label>
	<cfselect name="iid#idx#" id="iid#idx#" query="GetInsts" display="name" value="iid" queryPosition="below">
		<option value="0">--Please select--</option>
	</cfselect><br/>
</cfloop>
</div>
<br/>

<label for="tagline">Tagline:</label>
<cfif IsDefined("GetCourse.tagline")><cfset val=getCourse.tagline><cfelse><cfset val=""></cfif>
<input type="text" name="tagline" id="tagline" size="50" value="#val#" maxlength="150">
<br/><br/>
<label for="descrip">Description:</label>
<cfif IsDefined("GetCourse.descrip") and getCourse.descrip GT "">
	<cfset val=getCourse.descrip>
<cfelse>
	<cfset val="">
</cfif>
<cfmodule template="../../schoolstore/admin/fckeditor/fckeditor.cfm"
   	basePath="../../schoolstore/admin/fckeditor/"
	instanceName="descrip"
	width="350"
	height="350"
	value="#val#"
	config="#conf#"
	toolbarset="nhouse"
>
<br/><br/>

Theme(s):<br/>
<div class="indent">
<cfset tidnum=1>
<cfif IsDefined("getCThemes.recordCount")>
	<cfloop query="getCThemes">
		<cfset val=GetCThemes.tid>
		<label for="tid#tidnum#">Theme #tidnum#:</label>
		<cfselect name="tid#tidnum#" id="tid#tidnum#" query="GetThemes" display="theme" value="tid" selected="#val#" queryPosition="below">
			<option value="0">--Please select--</option>
		</cfselect><br/>
		<cfset tidnum=tidnum+1>				
	</cfloop>
</cfif>
<cfloop index="idx" from="#tidnum#" to=2>
	<label for="tid#idx#">Theme #idx#:</label>
	<cfselect name="tid#idx#" id="tid#idx#" query="GetThemes" display="theme" value="tid" queryPosition="below">
		<option value="0">--Please select--</option>
	</cfselect><br/>
</cfloop>
</div>
<br/>

<cfif now() lt createDate(2012, 03, 01)>
<label for="peak_tuition">Tuition (peak):</label>
<cfif IsDefined("getCourse.peak_tuition")><cfset val=getCourse.peak_tuition><cfelse><cfset val=0></cfif>
<cfinput type="text" name="peak_tuition" id="peak_tuition" size="20" value="#numberFormat(val, '9.00')#" validate="float" message="Please enter the peak tuition as a number">
<br/>
<label for="peak_desc">Peak tuition description:</label>
<cfif IsDefined("getCourse.peak_desc")><cfset val=getCourse.peak_desc><cfelse><cfset val=""></cfif>
<input type="text" name="peak_desc" id="peak_desc" value="#val#" size="50"><br/><br/>
<label for="offpeak_tuition">Tuition (off-peak):</label>
<cfif IsDefined("getCourse.offpeak_tuition")><cfset val=getCourse.offpeak_tuition><cfelse><cfset val=0></cfif>
<cfinput type="text" name="offpeak_tuition" id="offpeak_tuition" size="20" value="#numberFormat(val, '9.00')#" validate="float" message="Please enter the off-peak tuition as a number">
<br/>
<label for="peak_desc">Off-peak tuition description:</label>
<cfif IsDefined("getCourse.offpeak_desc")><cfset val=getCourse.offpeak_desc><cfelse><cfset val=""></cfif>
<input type="text" name="offpeak_desc" id="offpeak_desc" value="#val#" size="50">
<br><br>
</cfif>

<label for="early_tuition">Tuition (early-bird):</label>
<cfif IsDefined("getCourse.early_tuition")><cfset val=getCourse.early_tuition><cfelse><cfset val=0></cfif>
<cfinput type="text" name="early_tuition" id="early_tuition" size="20" value="#numberFormat(val, '9.00')#" validate="float" message="Please enter the early-bird tuition as a number">
<br>
<label for="early_desc">Early-bird tuition description:</label>
<cfif IsDefined("getCourse.early_desc")><cfset val=getCourse.early_desc><cfelse><cfset val=""></cfif>
<input type="text" name="early_desc" id="early_desc" value="#val#" size="50"><br/><br/>

<label for="reg_tuition">Tuition (regular):</label>
<cfif IsDefined("getCourse.reg_tuition")><cfset val=getCourse.reg_tuition><cfelse><cfset val=0></cfif>
<cfinput type="text" name="reg_tuition" id="reg_tuition" size="20" value="#numberFormat(val, '9.00')#" validate="float" message="Please enter the regular tuition as a number">
<label for="reg_desc">Regular tuition description:</label>
<cfif IsDefined("getCourse.reg_desc")><cfset val=getCourse.reg_desc><cfelse><cfset val=""></cfif>
<input type="text" name="reg_desc" id="reg_desc" value="#val#" size="50">

<br/><br/>
<input type="radio" class="checkbox" name="paybyproject" id="pbpyes" value="true" <cfif Isdefined("GetCourse.paybyproject") and GetCourse.paybyproject eq true>checked</cfif>>
<label for="phpyes" class="checkbox">Tuition is per project</label><br clear="all"/>
<input type="radio" class="checkbox" name="paybyproject" id="pbpno" value="false" <cfif (Isdefined("GetCourse.paybyproject") and GetCourse.paybyproject eq false) or not IsDefined("GetCourse.paybyproject")>checked</cfif>>
<label for="phpno" class="checkbox">Tuition is per student</label><br/>
<br/><br/>

<label for="material_amt">Materials Fee:</label>
<cfif IsDefined("getCourse.material_amt")><cfset val=numberformat(getCourse.material_amt, '9.00')><cfelse><cfset val=0></cfif>
<cfinput type="text" name="material_amt" id="material_amt" value="#val#"  size="20" validate="float" message="Please enter the materials amount as a number">
<br/>

<label for="materials">Materials Desc:</label>
<cfif IsDefined("getCourse.materials")><cfset val=getCourse.materials><cfelse><cfset val=""></cfif>
<input type="text" name="materials" id="materials" value="#val#"  size="50" maxlength="100">
<br/><br/>
<label for="tools">Materials/tools needed:</label>
<cfif IsDefined("GetCourse.tools") and getCourse.tools GT "">
	<cfset val=getCourse.tools>
<cfelse>
	<cfset val="">
</cfif>
<cfmodule template="../../schoolstore/admin/fckeditor/fckeditor.cfm"
   	basePath="../../schoolstore/admin/fckeditor/"
	instanceName="tools"
	width="350"
	height="350"
	value="#val#"
	config="#conf#"
	toolbarset="nhouse"
>
<br/><br/>



<label for="length">Number of days:</label>
<cfif IsDefined("getCourse.length")><cfset val=getCourse.length><cfelse><cfset val=0></cfif>
<cfinput type="text" name="length" id="length" size="20" value="#decimalFormat(val)#" validate="float"  required="yes" message="Please enter the number of days as a number">
<br/>
<label for="peak_desc">Length description:</label>
<cfif IsDefined("getCourse.length_desc")><cfset val=getCourse.length_desc><cfelse><cfset val=""></cfif>
<input type="text" name="length_desc" id="length_desc" value="#val#"  size="50">
<br/><br/>
<br/><br/>

<label for="hours">Hours:</label>
<cfif IsDefined("getCourse.hours")><cfset val=getCourse.hours><cfelse><cfset val=""></cfif>
<input type="text" name="hours" id="hours" value="#val#" size="50" maxlength="150">
<br/><br/>

<label for="levels">Levels:</label>
<cfif IsDefined("getCourse.levels")><cfset val=getCourse.levels><cfelse><cfset val=""></cfif>
<input type="text" name="levels" id="levels" value="#val#" size="50" maxlength="150">
<br/><br/>

<label for="intergen">Inter-generational ages:</label>
<cfif IsDefined("getCourse.intergen")><cfset val=getCourse.intergen><cfelse><cfset val=""></cfif>
<input type="text" name="intergen" id="intergen" value="#val#" size="50" maxlength="150"><br/>
<label></label><b><i>Leave blank if n/a</i></b>
<br/><br/>

Course photos:<br/>
<cfset pnum=1>
<div class="indent">
<cfif IsDefined("getCPhotos.recordCount")>
	<cfloop query="getCPhotos">
		<label for="photo#pnum#">Photo #pnum#:</label>
		<input type="file" value="Get Photo" size="35" name="photo#pnum#" id="photo#pnum#">
		<br/><br/>
		<div class="col1">Current Photo:</div>
		<div class="col2"><IMG src="../courses/#GetCPhotos.photo#"></div>
		<br clear="all"/>
		<input type="checkbox" value="Yes" name="delpic#pnum#" id="delpic#pnum#" class="checkbox">
		<label for="delpic#pnum#" class="checkbox">Delete Photo</label><br/><br clear="all"/>
		<cfset pnum=pnum+1>
	</cfloop>
</cfif>
<cfloop index="idx" from="#pnum#" to=3>
	<label for="photo#idx#">Photo #idx#:</label>
	<input type="file" value="Get Photo" size="35" name="photo#idx#" id="photo#idx#">
	<br clear="all"/>	
</cfloop>

<br/>
</div>

<label for="pdf">Associated PDF file:</label>
<input type="file" value="Get PDF" size="35" name="pdf" id="pdf">
<cfif IsDefined("GetCourse.pdf") and GetCourse.pdf GT "">
	<br/><br/>
	<div class="col1">Current File:</div>
	<div class="col2"><a href="../courses/#GetCourse.pdf#">#GetCourse.pdf#</a></div>
	<br clear="all"/>
	<input type="checkbox" value="Yes" name="delpdf" id="delpdf" class="checkbox">
	<label for="delpdf" class="checkbox">Delete File</label>
</cfif>
<br/>
<label for="pdftitle">PDF label:</label>
<input type="text" name="pdftitle" id="pdftitle" size="50"<cfif isDefined("GetCourse.pdftitle") and getCourse.pdftitle gt ""> value="#GetCourse.pdftitle#"</cfif>>
<br/>
<br/>

<label for="comments">Include a comments field in registration?</label>
<input type="checkbox" name="comments" id="comments"<cfif isdefined("getCourse.comment") and getCourse.comment eq true> checked</cfif>>
<br/>
<label for="commmtitle">Comments label:</label>
<input type="text" name="commtitle" id="commtitle" size="50"<cfif isDefined("GetCourse.commtitle") and getCourse.commtitle gt ""> value="#GetCourse.commtitle#"</cfif>>
<br/>
<label for="payinfull">Require full payment at registration?</label>
<input type="checkbox" name="payinfull" id="payinfull"<cfif isDefined("getCourse.payinfull") and getCourse.payinfull eq true> checked</cfif>>
<br/>
<br/>



<label for="daily">Course available most dates?</label>
<input type="checkbox" name="daily" id="daily"<cfif isdefined("getCourse.daily") and getCourse.daily eq true> checked</cfif>>
<br/>
<br/>
<label for="hidden">Hide course on website?</label>
<input type="checkbox" name="hidden" id="hidden"<cfif isdefined("getCourse.hidden") and getCourse.hidden eq true> checked</cfif>>
<br/>
<cfif isdefined("getCourse.hidden") and getCourse.hidden>
	(The URL for this course is <a href="#courseurl#course.cfm/cid/#getCourse.cid#">#courseurl#course.cfm/cid/#getCourse.cid#</a>)
	<br/>
</cfif>
<br/>

Start dates:<br/>
<div class="indent">
<cfset dtnum=1>
<cfif IsDefined("getCDates.recordCount")>
	<cfloop query="getCDates">
		<cfset val=dateformat(GetCDates.startdt,'mm/dd/yyyy')>
		<label for="startdt#dtnum#">Date #dtnum#:</label>
		<cfinput name="startdt#dtnum#" id="startdt#dtnum#" size="20" value="#val#" validate="date" message="Please enter the start date in the format mm/dd/yyyy" onchange="this.form.nearly_full#dtnum#.checked=false; this.form.releasedt#dtnum#.value=''; this.form.course_full#dtnum#.checked=false; this.form.cancelled#dtnum#.checked=false; this.form.eventid#dtnum#.selectedIndex=0;"><br clear="all"/>
		<cfif GetCdates.releasedt GT "" and getCdates.releasedt gt now()>
			<cfset val=dateformat(GetCDates.releasedt,'mm/dd/yyyy')>
		<cfelse>
			<cfset val="">
		</cfif>
		<label for="releasedt#dtnum#">Release Date #dtnum#:</label>
		<cfinput name="releasedt#dtnum#" id="releasedt#dtnum#" size="20" value="#val#" validate="date" message="Please enter the release date in the format mm/dd/yyyy"><br clear="all"/>
		<input type="checkbox" class="checkbox" name="new#dtnum#" id="new#dtnum#"<cfif GetCDates.new eq true>checked</cfif>>
		<label for="new#dtnum#" class="checkbox">New</label><br clear="all"/>
		<input type="checkbox" class="checkbox" name="nearly_full#dtnum#" id="nearly_full#dtnum#"<cfif GetCDates.nearly_full eq true>checked</cfif>>
		<label for="nearly_full#dtnum#" class="checkbox">Nearly full</label><br clear="all"/>
		<input type="checkbox" class="checkbox" name="course_full#dtnum#" id="course_full#dtnum#"<cfif GetCDates.course_full eq true>checked</cfif>>
		<label for="course_full#dtnum#" class="checkbox">Course full</label><br clear="all"/>
		<input type="checkbox" class="checkbox" name="cancelled#dtnum#" id="cancelled#dtnum#"<cfif GetCDates.cancelled eq true>checked</cfif>>
		<label for="cancelled#dtnum#" class="checkbox">Cancelled</label><br/>
		<script type="text/javascript">
			$(function() {
				$("##startdt#dtnum#, ##releasedt#dtnum#").datepicker({
					minDate: 0,
					changeMonth: true,
					changeYear: true
				});
			});
		</script>

		
<cfif eventid GT 0>
	<cfset val=eventid><cfelse><cfset val=0>
</cfif>
<label for="eventid#dtnum#">Date #dtnum# is part of event:</label>
<cfselect name="eventid#dtnum#" id="eventid#dtnum#" query="GetEvents" display="name" value="cid" selected="#val#" queryPosition="below">
	<option value="0">--Please select--</option>
</cfselect>
<br/>
<br/><hr/>

		
		
		
		<cfset dtnum=dtnum+1>			
	</cfloop>
</cfif>
<cfloop index="idx" from="#dtnum#" to=10>
	<label for="startdt#idx#">Date #idx#:</label>
	<cfinput name="startdt#idx#" id="startdt#idx#" size="20" validate="date" message="Please enter the start date in the format mm/dd/yyyy"><br clear="all"/>
	<label for="releasedt#idx#">Release Date #idx#:</label>
	<cfinput name="releasedt#idx#" id="releasedt#idx#" size="20" validate="date" message="Please enter the release date in the format mm/dd/yyyy"><br clear="all"/>
	<input type="checkbox" class="checkbox" name="new#idx#" id="new#idx#" checked>
	<label for="new#idx#" class="checkbox">New</label><br clear="all"/>
	<input type="checkbox" class="checkbox" name="nearly_full#idx#" id="nearly_full#idx#">
	<label for="nearly_full#idx#" class="checkbox">Nearly full</label><br clear="all"/>
	<input type="checkbox" class="checkbox" name="course_full#idx#" id="course_full#idx#">
	<label for="course_full#idx#" class="checkbox">Course full</label><br clear="all"/>
	<input type="checkbox" class="checkbox" name="cancelled#idx#" id="cancelled#idx#">
	<label for="cancelled#idx#" class="checkbox">Cancelled</label><br/>
	<script type="text/javascript">
		$(function() {
			$("##startdt#idx#, ##releasedt#idx#").datepicker({
				minDate: 0,
				changeMonth: true,
				changeYear: true
			});
		});
	</script>


<label for="eventid#idx#">Date #idx# is part of event:</label>
<cfselect name="eventid#idx#" id="eventid#idx#" query="GetEvents" display="name" value="cid" queryPosition="below">
	<option value="0">--Please select--</option>
</cfselect>
	<br/><br/><hr/>


</cfloop>
</div>

<input type="Checkbox" class="checkbox" name="isevent" id="isevent" <cfif IsDefined("GetCourse.isevent") and getCourse.isEvent eq true>checked</cfif>>
<label for="isevent" class="checkbox">Course is an event</label><br clear="all"/>
<label for="url">Link to event page:</label>
<cfif IsDefined("GetCourse.url") and getCourse.url GT "">
	<cfset val=GetCourse.url><cfelse><cfset val="">
</cfif>
<input type="text" name="url" id="url" size="50" value="#val#"><br clear="all"/>
<br/>
<input type="submit" value="Save Course Information">
<cfif IsDefined("getCourse.name")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the information for #getCourse.name#?')) { location.href='course_update.cfm?cid=#url.cid#&del=yes'; }" value="Delete Course">
</cfif>
</cfform>
</cfoutput>
</div>
<br clear="all"/><br/>



<cfinclude template="footer.cfm">