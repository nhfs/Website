<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<cfinclude template="topper.cfm">
<BR><A HREF="#ecat">View Existing Categories</A>

<H1 ALIGN=CENTER>Maintain Product Categories</H1>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>#URL.x# has been added!</B><P>
		</P>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.y")>
	<cfoutput>
		<B>#URL.y# has been updated!</B><P>
		</P>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.z")>
	<cfoutput>
		<B>Category has been deleted!</B><P>
		</P>
	</cfoutput>
</cfif>

<cfset formact="category_add.cfm">
<cfset subtext="Add Category">
<cfif IsDefined("URL.CatID")>
	<cfquery datasource="#dsn#" name="GetCat">
		select * from Categories 
			WHERE CatID='#URL.CatID#'
	</cfquery>
	<cfif GetCat.Recordcount gt 0>
		<cfset formact="category_update.cfm?CatID=" & URL.CatID>
		<cfset subtext="Update Category">
	</cfif>
</cfif>

<cfoutput>
<cfform action="#formact#" name="AddCat" id="AddCat" method="POST" enctype="multipart/form-data" scriptSrc="http://kite.boreal.org/CFIDE/scripts">
<TABLE WIDTH="100%" BORDER=0>
<TR><TD>
	Category Name:
</TD>
<TD>
	<input name="CatName" type="text" size="60" <cfif isdefined("GetCat.CatName")>value="#GetCat.CatName#"</cfif>>
</TD>
</TR>
<TR><TD VALIGN="bottom">
	Unique Category ID:
</TD>
<TD>
	(This code will be used in the catalog's URL address to show the page for the right category.)<BR>
	<cfif isdefined("GetCat.CatID")>
		<cfset catkey=GetCat.CatID>
	<cfelse><cfset catkey="">
	</cfif>
	<cfinput name="CatID" type="text" size="60" value="#catkey#" required="Yes" maxlength="5" message="Category ID is required, and must be a code of 5 or fewer characters">
</TD>
</TR>

<TR><TD VALIGN="middle">
	Category Description:
</TD>
<TD>
	(This text will be displayed at the top of the page for this category.)<BR>
	<textarea name="CatDesc" cols="45" rows="5"><cfif Isdefined("GetCat.CatDesc")>#GetCat.CatDesc#</cfif></textarea>
</TD>
</TR>

<TR><TD VALIGN="middle">
	Category Special Message:
</TD>
<TD>
	(Use this to specify any special message or promotion that should be displayed under items from this category.)<BR>
	<textarea name="CatMessage" cols="45" rows="5"><cfif Isdefined("GetCat.CatMessage")>#GetCat.CatMessage#</cfif></textarea>
</TD>
</TR>

<TR><TD>
	Title Picture:
</TD>
<TD>
	<input type="File" value="Get Picture" name="CatPicture" size="60">
	<cfif isdefined("GetCat.CatPicture") and GetCat.CatPicture GT "">
		</TR><TR><TD>Current Picture:</TD><TD Align="center"><IMG SRC="../#GetCat.CatPicture#">
		<BR><input type="Checkbox" value="Yes" name="DelPic"> Delete picture for this category
	</cfif>
</TD>
</TR>

<TR><TD COLSPAN=2 align=center>
<INPUT TYPE="Submit" value="#subtext#">
</TD></TR>
</TABLE>
</cfform>
</cfoutput>
<HR>
<DIV ALIGN="CENTER"><B><FONT SIZE=+1>Existing Categories</FONT></B></DIV>
<cfquery name="AllCats" datasource="#dsn#">
	select Categories.CatID, CatName, CatPicture, CatMessage, CatDesc, Count(scatID) as Nbr_Lines
		from Categories LEFT JOIN subcats ON subcats.CatID=Categories.CatID
		GROUP BY Categories.CatID, CatName, CatPicture, CatMessage, CatDesc
		ORDER BY CatName
</cfquery>
<a name="ecat"></a>
<TABLE WIDTH="100%">
<TR>
<TD class="tier1">Category ID</TD>
<TD class="tier1">
	Category
</TD>
<TD class="tier1">
	Description
</TD>
<TD class="tier1">
	Message
</TD>
<TD class="tier1">
	Picture?
</TD>
<TD class="tier1">
</TD>
<TD class="tier1">
</TD>
<TD class="tier1">
</TD>
</TR>
<cfoutput query="AllCats">
<TR>
<TD>
	#CatID#
</TD>
<TD>
	#CatName#
</TD>
<TD>
	#REReplace(CatDesc,"\n","<BR>","All")#
</TD>
<TD>
	#CatMessage#
</TD>
<TD>
	<cfif AllCats.CatPicture GT "">Yes<cfelse>No</cfif>
</TD>
<td>
	<A HREF="addline.cfm?CatID=#CatID#">Add Category's Lines</A>
</td>
<TD>
	<A HREF="addcategory.cfm?CatID=#CatID#">Edit Category</A>
</TD>
<TD>
	<cfif Nbr_Lines GT 0>
		* - Lines exist
	<cfelse>
		<A HREF="category_delete.cfm?CatID=#CatID#">Delete Category</A>
	</cfif>
</TD>
</TR>
</cfoutput>
</TABLE>
* - Before you delete a category, you must first delete all the lines
under that category.  This prevents pictures for the lines and their
items from being left on the server.
<cfinclude template="bottom.cfm">