<cfinclude template="topper.cfm">
<BR><A HREF="#eart">View Existing Sub-Categories</A>

<cfquery name="GetCats" datasource="#dsn#">
	Select * from Categories
</cfquery>

<H1 ALIGN=CENTER>Sub-Category Information</H1>

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

<cfset formact="scat_add.cfm">
<cfset subtext="Add Sub-Category">
<cfset aname="">
<cfset aord="">
<cfif IsDefined("URL.scatID")>
	<cfquery datasource="#dsn#" name="GetScat">
		select * from subcats
			WHERE scatid=#URL.scatid#
	</cfquery>
	<cfif Getscat.Recordcount gt 0>
		<cfset formact="scat_update.cfm?scatID=" & URL.scatID>
		<cfset subtext="Update Sub-Category">
		<cfset aname=Getscat.scatName>
		<cfset aord=Getscat.scatOrder>
	</cfif>
<cfelse>
	<cfquery name="GetMaxOrd" datasource="#dsn#">
		select max(scatorder) as maxord from subcats
			<cfif IsDefined("url.catid")>
			where catid='#url.catid#'
			</cfif>
	</cfquery>
	<cfif IsNumeric(GetMaxOrd.maxord)>
		<cfset aord=GetMaxOrd.maxord+1>
	<cfelse>
		<cfset aord=1>
	</cfif>
</cfif>

<cfoutput>
<cfform action="#formact#" name="Addscat" id="Addscat" method="POST" scriptSrc="http://kite.boreal.org/CFIDE/scripts">
<TABLE WIDTH="100%">
<TR><TD>
	Category:
</TD>
<TD>
	<cfif #isdefined("URL.CatID")#>
		<cfset sel="#URL.CatID#">
	<cfelseif IsDefined("Getscat.CatID")>
		<cfset sel="#Getscat.CatID#">
	<cfelse>
		<cfset sel="">
	</cfif>
	
	<cfselect name="CatID" required="yes" message="You must select a category" query="GetCats"
	  value="CatID" display="CatName" selected="#sel#"></cfselect>
</TD>
</TR>
<TR>
<TD>
	Sub-Category Name:
</TD>
<TD>
	<cfinput type="Text" name="scatName" size="60" required="yes" message="Please enter the Sub-category's name" value="#aname#">
</TD>
</TR>
<TR>
<TD>
	Description:
</TD>
<TD>
	<textarea rows="5" name="scatdesc" cols="50" rows="5"><cfif isdefined("Getscat.scatDesc")>#Getscat.scatDesc#</cfif></textarea>
</TD>
</TR>
<TR>
<TD>
	Placement: 
</TD>
<TD>
	(Sub-categories will be displayed on the page in order by this number)<BR>
	<cfinput type="Text" name="scatOrder" size="60" required="no" validate="integer" message="Placement must be an integer" value="#aord#">
</TD>
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
<DIV ALIGN="CENTER"><B><FONT SIZE=+1>Existing Sub-Categories</FONT></B></DIV>
<cfquery name="Getscats" datasource="#dsn#">
	Select subcats.scatID, scatName, scatDesc, scatOrder, CatName,
		Count(lineID) as NbrLines
		from (subcats LEFT JOIN _lines on subcats.scatid=_Lines.scatid)
		INNER JOIN Categories on subcats.CatID = Categories.CatID
		GROUP BY subcats.scatID, scatName, scatDesc, scatOrder, CatName
		ORDER BY CatName, scatOrder, scatName
</cfquery>
<cfset cat="">

<DIV ALIGN="Center">
<cfset catidx=1>
<cfoutput query="GetScats">
<cfif cat NEQ CatName>
	<A HREF="###CatName#">#CatName#</A>
	<cfif (catidx MOD 4) EQ 0>
			<BR>
	<cfelse>
		  /
	</cfif>
	<cfset cat=CatName>
	<cfset catidx=catidx + 1>
</cfif>
</cfoutput>
</DIV>

<cfset cat="">
<TABLE WIDTH="100%">
<cfoutput query="GetScats">
<cfif cat neq CatName>
	<TR>
		<TD COLSPAN="5" align=center class="tier1">
		<A Name="#CatName#"></A>
		<B>#CatName#</B></TD>		
	</TR>
	<TR>
	<TD class="tier2">
		Name
	</TD>
	<TD class="tier2">
		Desc
	</TD>
	<td class="tier2"></td>
	<TD class="tier2"></TD>
	<TD class="tier2"></TD>
	</TR>
	<cfset cat=CatName>
</cfif>
<TR>
<TD>
	#scatName#
</TD>
<TD>
	#REReplace(scatDesc,"\n","<BR>","All")#
</TD>
<td>
	<A HREF="addline.cfm?scatID=#scatID#">Add Sub-category's Lines</A>
</td>
<td>
	<A HREF="addscat.cfm?scatID=#scatID#">Edit Sub-Category Info</A>
</td>
<TD>
	<cfif NbrLines GT 0>
		*-Lines exist
	<cfelse>
		<A HREF="scat_delete.cfm?scatID=#scatID#">Delete Sub-Category</A>
	</cfif>
</TD>
</TR>
</cfoutput>
</TABLE>
* - Before you delete an sub-category, you must delete all the lines and items for that
sub-category.  This prevents picture files for those items from being left
on the server.

<cfinclude template="bottom.cfm">