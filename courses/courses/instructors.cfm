<cfinclude template="templates/header.cfm">
<h2>Instructors</h2>
<p>
This list includes the individuals who are currently scheduled to teach courses 
at North House. 
</p>
<cfquery name="GetInst" datasource="#dsn#">
	select * from instructors
		order by lname, fname
</cfquery>
<cfset alpha="">
<cfset photos=ArrayNew(1)>
<div class="col1">
<cfoutput query="GetInst">
<cfif not guest>
	<cfif left(lname,1) neq alpha><br><b>#left(lname,1)#</b><br/><cfset alpha=left(lname,1)></cfif>
	<a href="instructor.cfm?iid=#iid#"><span class="instname">#lname#, #fname#</span> </a><span class="insthome">#home#</span><br/>
	<cfif photo GT "">
		<cfset inst=StructNew()>
		<cfset inst.photo=photo>
		<cfset inst.name=fname & lname>
		<cfset inst.iid=iid>
		<cfset ArrayAppend(photos,inst)>
	</cfif>
</cfif>
</cfoutput>
<br><hr>
<h2>Guest/Past Instructors</h2>
<p>This list shows instructors who teach or have taught as guests at North House, but who are not currently scheduled
to teach courses.</p>
<cfoutput query="GetInst">
<cfif guest>
	<cfif left(lname,1) neq alpha><br><b>#left(lname,1)#</b><br/><cfset alpha=left(lname,1)></cfif>
	<a href="instructor.cfm?iid=#iid#"><span class="instname">#lname#, #fname#</span> </a><span class="insthome">#home#</span><br/>
	<cfif photo GT "">
		<cfset inst=StructNew()>
		<cfset inst.photo=photo>
		<cfset inst.name=fname & lname>
		<cfset inst.iid=iid>
		<cfset ArrayAppend(photos,inst)>
	</cfif>
</cfif>
</cfoutput>

</div>
<cfif ArrayLen(photos) GT 0>
<cfoutput>
	<div class="col2">

		<cfif ArrayLen(photos) le 5>
			<cfloop index="x" from="1" to="#arrayLen(photos)#">
				<a href="instructor.cfm?iid=#photos[x].iid#"><img src="#photos[x].photo#" alt="#photos[x].name#" border="0"></a><br/><br/>
			</cfloop>
		<cfelse>
		<!--- pull 5 random photos --->
			<cfset used="">
			<cfloop index="x" from="1" to="5">
				<cfset rnd=ceiling(rand()*ArrayLen(photos))>
				<cfloop condition="ListFind(used,rnd) GT 0">
					<cfset rnd=ceiling(rand()*ArrayLen(photos))>
				</cfloop>
				<a href="instructor.cfm?iid=#photos[rnd].iid#"><img src="#photos[rnd].photo#" alt="#photos[rnd].name#" border="0"></a><br/><br/>
				<cfset used=used & "," & rnd>
			</cfloop>		
		</cfif>
	
	</div>
</cfoutput>
</cfif>

<br clear="all"/><br/>
<cfinclude template="templates/footer.cfm">