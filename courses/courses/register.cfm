<cfinclude template="templates/header.cfm">
<cfset feechange=createDate(2012, 3, 1)>
<script language="JavaScript" type="text/javascript">
function checkForm(fm) {
	var err=false;
	var msg="";
	if (fm.Kids != null && fm.Kids.value > (fm.Students.value-1) && fm.Students.value > 0) {
		msg+='You must register at least one adult for intergenerational classes, so the total students must be at least one more than the total number of children registered\n';
		err=true;
	}
	if (err) {
		alert(msg);
		return false;
	} else {
		return true;
	}
}
		

</script>
<cfif IsDefined("form.cid") and isDefined("form.startdt")>
	<!---- add item to cart array ----->
	<cfset dupflag = false>
	<cfloop index="idx" from=1 to=#ArrayLen(session.Cart)#>
		<cfif session.Cart[idx].cid eq form.cid and session.cart[idx].startdt eq form.startdt and 
			not session.Cart[idx].paybyproject>
			<cfset session.Cart[idx].Students = session.Cart[idx].students + 1>
			<script language="Javascript">
				alert("This class is already in your cart.  For your convenience," + 
					" we've updated the number of students registering for the course " +
					"rather than adding it a second time.");
			</script>
			<cfset dupflag=true>
		</cfif>
	</cfloop>
	
	<cfif not dupflag>
		<cfquery name="getCourse" datasource="#dsn#">
			Select c.*, course_full, cancelled, startdt from 
				courses c INNER JOIN course_dates cd ON c.cid=cd.cid 
				where c.cid=<cfqueryparam value="#form.cid#" cfsqltype="CF_SQL_INTEGER">
					and cd.startdt=#CreateODBCDate(form.startdt)#
		</cfquery>
		<cfif GetCourse.recordCount eq 0>
			<script language="Javascript">
				alert("Sorry, we were unable to find information on this course.  Please call to register.");
			</script>
		<cfelseif GetCourse.course_full eq true>
			<script language="Javascript">
				alert("Sorry, this course is full for this date.  Please call to be put on the waiting list, or select a different date.");
			</script>				
		<cfelseif GetCourse.cancelled eq true>
			<script language="Javascript">
				alert("Sorry, this course is no longer being offered on this date.  Please call for more informaiton, or select a different date.");
			</script>				
		<cfelse>
			<cfset err=false>
			<cfset Course=structnew()>
			<cfset course.cid=form.cid>
			<cfset course.name=GetCourse.name>
			<cfset course.students=1>
			<cfset course.startdt=GetCourse.startdt>
			<cfif startdt lt feechange>
				<cfif CreateDate(year(now()),month(startdt),day(startdt)) ge peak_start and
					CreateDate(year(now()),month(startdt),day(startdt)) le peak_end>
					<!--- peak tuition --->
					<!---cfif GetCourse.peak_tuition le 0>
						<script language="JavaScript">
							alert("Sorry, online registration is not available for this course.  Please call <cfoutput>#co_phone#</cfoutput> to register");
						</script>
						<cfset err=true>
					<cfelse--->
						<cfset course.tuition=GetCourse.peak_tuition>
					<!---/cfif--->
				<cfelse>
					<!---cfif GetCourse.offpeak_tuition le 0>
						<script language="JavaScript">
							alert("Sorry, online registration is not available for this course.  Please call #co_phone# to register");
						</script>
						<cfset err=true>
					<cfelse--->		
						<cfset course.tuition=GetCourse.offpeak_tuition>
					<!---/cfif--->
				</cfif>
			<cfelse>
				<cfif getCourse.reg_tuition GT 0>
					<cfif getCourse.early_tuition gt 0 and now() le dateAdd('ww', -6, dateadd('d', 1, startdt))>
						<cfset course.tuition = getCourse.early_tuition>
					<cfelse>
						<cfset course.tuition = getCourse.reg_tuition>
					</cfif>
				<cfelse>
					<cfset course.tuition=0>
				</cfif>
			</cfif>
			<cfif Getcourse.material_amt GT 0>
				<cfset course.materials=GetCourse.material_amt>			
			<cfelse>
				<cfset course.materials=0>
			</cfif>

			<cfif GetCourse.startdt LT dateAdd("ww",3,now()) or getCourse.payinfull eq true>
			<!--- less than 3 weeks away - full tuition+materials --->
				<cfset course.deposit=course.tuition+course.materials>
				<cfset course.kiddeposit=(course.tuition*.75)+course.materials>
			<cfelseif course.tuition lt 50>
			<!--- minimum deposit is $50, but if course tuition is less than that, full tuition --->
				<cfset course.deposit=course.tuition>
				<cfset course.kiddeposit=course.tuition*.75>
			<cfelseif course.tuition lt 150>
			<!--- minimum deposit is $50 or 1/3 of tuition - if tuition < 150 this will be 50 --->
				<cfset course.deposit=50>
				<cfset course.kiddeposit=50>
			<cfelse>
				<cfset course.deposit=ceiling(course.tuition/3)>
				<cfif (course.tuition*.75) lt 150>
					<cfset course.kiddeposit=50>
				<cfelse>
					<cfset course.kiddeposit=ceiling((course.tuition*.75)/3)>
				</cfif>
			</cfif>
			<cfset course.children=0>
			<cfif GetCourse.paybyproject eq true>
				<cfset course.paybyproject=true>
			<cfelse>
				<cfset course.paybyproject=false>
			</cfif>
			<cfif GetCourse.intergen GT "">
				<cfset course.intergen=true>
			<cfelse>
				<cfset course.intergen=false>
			</cfif>
			<cfif getCourse.comment>
				<cfset course.comment=true>
				<cfset course.commtitle=getCourse.commtitle>
			<cfelse>
				<cfset course.comment=false>
			</cfif>
			<cfif not err>
				<cfset ArrayAppend(session.Cart,course)>
			</cfif>
		</cfif>
	</cfif>
	
</cfif>
<h2>Course Registration List</h2>

<cfif ArrayLen(session.cart) gt 0>
<cfset tottu=0>
<cfset totma=0>
<cfset totde=0>
<cfoutput>
	<table class="cartlist">
	<tr>
	<th>Course</th><th>Total Students</th><th>## Under 18</th><th>Tuition</th><th>Materials</th><th>Minimum Deposit</th><th></th>
	</tr>
	
	<cfloop index="idx" from="1" to="#arraylen(session.cart)#">
		<cfform action="updcart.cfm" method="POST" scriptsrc="http://kite.boreal.org/cfide/scripts" onsubmit="return checkForm(_CF_this);">
		<input type="hidden" name="cartitem" value="#idx#">
		<tr>
		<td><a href="course.cfm?cid=#session.cart[idx].cid#">#session.cart[idx].name#</a>-#dateformat(session.cart[idx].startdt,'mm/dd/yy')#</td>
		<td align="right"><cfinput name="Students" type="text" size="5" validate="integer" value="#session.cart[idx].students#" message="Number of students must be an integer"></td>
		<td align="right">
			<cfif session.cart[idx].intergen><cfinput name="Kids" type="text" size="5" value="#session.cart[idx].children#" validate="integer" message="Please specify the number of children as an integer.">
			<cfelse>
			</cfif>
		</td>
		<td align="right"><cfif session.cart[idx].paybyproject>
				#Dollarformat(session.cart[idx].tuition)#
				<cfset tottu=tottu+session.cart[idx].tuition>
			<cfelse>
				<cfif session.cart[idx].children GT 0>
					#DollarFormat((session.cart[idx].tuition*(session.cart[idx].students-session.cart[idx].children))+((session.cart[idx].tuition*.75)*session.cart[idx].children))#
					<cfset tottu=tottu+((session.cart[idx].tuition*(session.cart[idx].students-session.cart[idx].children))+((session.cart[idx].tuition*.75)*session.cart[idx].children))>
				<cfelse>
					#DollarFormat(session.cart[idx].tuition*session.cart[idx].students)#
					<cfset tottu=tottu+(session.cart[idx].tuition*session.cart[idx].students)>
				</cfif>
			</cfif></td>
		<td align="right"><cfif session.cart[idx].paybyproject>
				#Dollarformat(session.cart[idx].materials)#
				<cfset totma=totma+session.cart[idx].materials>
			<cfelse>
				#DollarFormat(session.cart[idx].materials*session.cart[idx].students)#
				<cfset totma=totma+(session.cart[idx].materials*session.cart[idx].students)>
			</cfif></td>
		<td align="right"><cfif session.cart[idx].paybyproject>
				#Dollarformat(session.cart[idx].deposit)#
				<cfset totde=totde+session.cart[idx].deposit>
			<cfelse>
				<cfif session.cart[idx].children GT 0>
					#DollarFormat((session.cart[idx].deposit*(session.cart[idx].students-session.cart[idx].children))+(session.cart[idx].children*session.cart[idx].kiddeposit))#
					<cfset totde=totde+((session.cart[idx].deposit*(session.cart[idx].students-session.cart[idx].children))+(session.cart[idx].children*session.cart[idx].kiddeposit))>
				<cfelse>
					#DollarFormat(session.cart[idx].deposit*session.cart[idx].students)#
					<cfset totde=totde+(session.cart[idx].deposit*session.cart[idx].students)>
				</cfif>
			</cfif></td>
		<td><input type="submit" value="Update"></td>
		</tr>
		</cfform>
		<tr class="cartdiv"><td colspan="7"></td></tr>
	</cfloop>
	<tr class="carttot">
		<td colspan="3">Totals</td>
		<td align="right">#dollarformat(tottu)#</td>
		<td align="right">#dollarformat(totma)#</td>
		<td align="right">#dollarformat(totde)#</td>
		<td></td>
	</tr>
	</table>
	<p>To remove a course from the list, set the number of students to 0.</p>
	<p><a href="checkout.cfm"><b>Check Out</b></a></p>
</cfoutput>
<cfelse>
<p>Your course list is empty.  If you've registered for a course and it isn't showing up here, 
you may need to enable cookies in your browser.
</p>
<a href="coursesbydate.cfm">Back to Course Calendar</a>
</cfif>

<!---cfdump var="#session.cart#"--->
<cfinclude template="templates/footer.cfm">