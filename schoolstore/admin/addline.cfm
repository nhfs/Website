<cfinclude template="topper.cfm">
<BR><A HREF="#eart">View Existing Lines</A>

<cfquery name="GetsCats" datasource="#dsn#">
	Select * from subCats
</cfquery>

<H1 ALIGN=CENTER>Line Information</H1>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Line #URL.x# has been added!</B><P>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.y")>
	<cfoutput>
		<B>Line #URL.y# has been updated!</B><P>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.z")>
	<cfoutput>
		<B>Line has been deleted!</B><P>
	</cfoutput>
</cfif>

<cfset formact="line_add.cfm">
<cfset subtext="Add Line">
<cfset aname="">
<cfset aord="">
<cfif IsDefined("URL.LineID")>
	<cfquery datasource="#dsn#" name="GetLine">
		select * from _Lines
			WHERE LineID=#URL.LineID#
	</cfquery>
	<cfif GetLine.Recordcount gt 0>
		<cfset formact="line_update.cfm?LineID=" & URL.LineID>
		<cfset subtext="Update Line">
		<cfset aname=GetLine.LineName>
		<cfset aord=GetLine.LineOrder>
	</cfif>
</cfif>

<cfoutput>
<cfform action="#formact#" name="AddLine" id="AddLine" method="POST" enctype="multipart/form-data" scriptSrc="http://kite.boreal.org/CFIDE/scripts">
<TABLE WIDTH="100%">
<TR><TD>
	Sub-Category:
</TD>
<TD>
	<cfif #isdefined("URL.sCatID")#>
		<cfset sel="#URL.sCatID#">
	<cfelseif IsDefined("GetLine.sCatID")>
		<cfset sel="#GetLine.sCatID#">
	<cfelse>
		<cfset sel="">
	</cfif>
	
	<cfselect name="sCatID" required="yes" message="You must select a sub-category" query="GetsCats"
	  value="sCatID" display="sCatName" selected="#sel#"></cfselect>
</TD>
</TR>
<TR>
<TD>
	Line Name:
</TD>
<TD>
	<cfinput type="Text" name="LineName" size="60" required="yes" message="You must enter the Line's name" value="#aname#">
</TD>
</TR>
<TR>
<TD>
	Description:
</TD>
<TD>
	<textarea rows="5" name="LineDesc" cols="50" rows="5"><cfif isdefined("GetLine.LineDesc")>#GetLine.LineDesc#</cfif></textarea>
</TD>
</TR>
<tr>
<td valign="top">Apply sales tax to <br>items in this line?</td>
<td><input type="radio" name="taxable" value="true" <cfif (IsDefined("GetLine.taxable") and getLine.taxable) or not IsDefined("getLine.taxable")>checked</cfif>> Yes<br>
<input type="radio" name="taxable" value="false" <cfif (IsDefined("GetLine.taxable") and not getLine.taxable)>checked</cfif>> No
</td>
</tr>
<TR>
<TD>
	Placement: 
</TD>
<TD>
	(Lines will be displayed on the page in order by this number)<BR>
	<cfinput type="Text" name="LineOrder" size="60" required="no" validate="integer" message="Placement must be an integer" value="#aord#">
</TD>
</TR>
<TR>
<TD>
	Picture:
</TD>
<td>
	<input type="File" value="Get Picture" name="LinePicture" size="60">
	<cfif isdefined("GetLine.LinePicture") and GetLine.LinePicture GT "">
		</TR><TR><TD>Current Picture:</TD><TD Align="center"><IMG SRC="../#GetLine.LinePicture#">
		<BR><input type="Checkbox" value="Yes" name="DelPic"> Delete picture for this Line
	</cfif>
</td>
</TR>
<TR>
<TD COLSPAN=2 ALIGN=Center>
<input type="submit" value="#subtext#">
</TD>
</TR>
</TABLE>
</cfform>
</cfoutput>
<HR>
<A NAME="eart"></A>
<DIV ALIGN="CENTER"><B><FONT SIZE=+1>Existing Lines</FONT></B></DIV>
<cfquery name="Get_Lines" datasource="#dsn#">
	Select _Lines.LineID, LineName, LineDesc, LinePicture, LineOrder, taxable, sCatName, catname,
		Count(GroupID) as NbrGroups
		from (_Lines LEFT JOIN Groups on _Lines.LineID = Groups.LineID)
		INNER JOIN (subcats inner join Categories on subcats.catid=categories.catid) 
			on _Lines.sCatID = subcats.sCatID
		GROUP BY _Lines.LineID, LineName, LineDesc, LinePicture, LineOrder, scatname, CatName
		ORDER BY CatName, scatname, LineOrder, LineName
</cfquery>
<cfset scat="">
<cfset cat="">
<DIV ALIGN="Center">
<cfset catidx=1>
<cfoutput query="Get_Lines">
<cfif cat neq catname>
	<br/><a href="##c#catname#"><b>#catname#</b></a><br/>
	<cfset cat=catname>
	<cfset catidx=1>
</cfif>
<cfif scat NEQ sCatName>
	<A HREF="###sCatName#">#sCatName#</A>
	<cfif (catidx MOD 4) EQ 0>
			<BR>
	<cfelse>
		  /
	</cfif>
	<cfset scat=sCatName>
	<cfset catidx=catidx + 1>
</cfif>
</cfoutput>
</DIV>

<cfset cat="">
<cfset scat="">
<TABLE WIDTH="100%">
<cfoutput query="Get_Lines">
<cfif cat neq CatName>
	<TR>
		<TD COLSPAN="7" align=center class="tier1">
		<A Name="c#CatName#"></A>
		<B>#CatName#</B></TD>		
	</TR>
	<cfset cat=Catname>
</cfif>
<cfif scat neq sCatName>
	<TR>
		<TD COLSPAN="7" align=center class="tier2">
		<A Name="#sCatName#"></A>
		<B>#sCatName#</B></TD>		
	</TR>
	<TR>
	<TD class="tier3">
		Name
	</TD>
	<TD class="tier3">
		Desc
	</TD>
	<TD class="tier3">
		Taxable?
	</TD>
	<TD class="tier3">
		Picture
	</TD>
	<td class="tier3"></td>
	<TD class="tier3"></TD>
	<TD class="tier3"></TD>
	</TR>
	<cfset scat=sCatName>
</cfif>
<TR>
<TD>
	#LineName#
</TD>
<TD>
	#REReplace(LineDesc,"\n","<BR>","All")#
</TD>
<td><cfif taxable>yes<cfelse>no</cfif></td>
<TD>
	<cfif LinePicture GT "">
		Yes
	<cfelse>
		No
	</cfif>
</TD>
<td>
	<A HREF="additem.cfm?LineID=#LineID#">Add Line's Items</A>
</td>
<td>
	<A HREF="addLine.cfm?LineID=#LineID#">Edit Line Info</A>
</td>
<TD>
	<cfif NbrGroups GT 0>
		*-Items exist
	<cfelse>
		<A HREF="Line_delete.cfm?LineID=#LineID#">Delete Line</A>
	</cfif>
</TD>
</TR>
</cfoutput>
</TABLE>
* - Before you delete an line, you must delete all the items for that
line.  This prevents picture files for those items from being left
on the server.

<cfinclude template="bottom.cfm">