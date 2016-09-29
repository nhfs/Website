<cfset masthead="raku">
<cfinclude template="Templates/topper.cfm">
	
<cfif not IsDefined("URL.LineID")>
	<cflocation url="index.cfm">
</cfif>

<cfquery datasource="#dsn#" name="GetLine">
	Select * from _lines
		WHERE LineID = <cfqueryparam value="#URL.LineID#" cfsqltype="CF_SQL_INTEGER">
</cfquery>
<BR>

<Table width="90%" align="CENTER" cellpadding="3" cellspacing="3">
<TR>
<TD WIDTH="25%"></TD>
<TD WIDTH="50%" ALIGN="CENTER">
<cfoutput query="GetLine">
	<H2>#GetLine.LineName#</H2>
</TD><TD WIDTH="25%" ALIGN=CENTER VALIGN="TOP">
<cfinclude template="Templates/cartcnt.cfm">
</TD></tR>
<TR>
<TD ALIGN=CENTER colspan=3>
	<cfif LinePicture GT "">
		<P><IMG SRC="#LinePicture#" alt="#LineName#" BORDER="0"></P>
	</cfif>
	
	<P>#REReplace(LineDesc,"\n[\s]*","<P>","All")#</P>
	</TD>
	</TR><TR>

<TD ALIGN=CENTER COLSPAN=3>
	<TABLE><TR><!---TD>
	<A HREF="catalog.cfm?CatID=#CatID####LineID#"><IMG SRC="Images/shopbutton.jpg" alt="Shop" border=0></A></TD--->
	<TD VALIGN="middle">
	<A HREF="catalog.cfm?CatID=#CatID####LineID#"><B><FONT SIZE="+1"><B>Shop</B> for #LineName#</FONT></b></A>
	</TD></TR></TABLE>
</cfoutput>
</TABLE>
<cfinclude template="Templates/bottom.cfm">