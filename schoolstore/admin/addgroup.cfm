<cfinclude template="topper.cfm">
<BR><A HREF="#eitem">View existing items</A>

<cfquery datasource="#dsn#" name="Get_Lines">
	Select LineID, LineName from _Lines
	ORDER BY LineName
</cfquery>
<H1>Grouped Items</H1>


<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Group/Item #URL.x# added!</B>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.y")>
	<cfoutput>
		<B>Group/Item #URL.y# updated!</B>
	</cfoutput>
</cfif>

<cfif IsDefined("URL.z")>
	<cfoutput>
		<B>Item has been deleted!</B>
	</cfoutput>
</cfif>

<cfset formact="group_add.cfm">
<cfset subtext="Add Group & Item">
<cfset iname="">
<cfset gname="">
<cfset gord=0>
<cfset iord=0>
<cfset iprice="">
<cfset sprice="0.00">
<cfif IsDefined("URL.GroupID")>
	<cfquery datasource="#dsn#" name="GetGroup">
		Select * from Groups
			Where GroupID = #URL.GroupID#
	</cfquery>
	<cfif GetGroup.RecordCount GT 0>
		<cfset gord=GetGroup.GroupOrder>
		<cfset gname=GetGroup.GroupName>
		<cfset subtext="Add Item to Group">
	</cfif>
	<cfquery name="GetMaxIORD" datasource="#dsn#">
		select Max(ItemOrder) as maxOrd from Items where
			GroupID = #URL.GroupID#
	</cfquery>
	<cfif GetMaxIORD.MaxOrd GT "">
		<cfset iord=GetMaxIORD.MaxOrd + 1>
	</cfif>
</cfif>
<cfif IsDefined("URL.ItemID")>
	<cfquery datasource="#dsn#" name="GetItem">
		select * FROM Items
			WHERE ItemID=#URL.ItemID#
	</cfquery>
	<cfif GetItem.Recordcount gt 0>
		<cfquery datasource="#dsn#" name="GetGroup">
			Select * from Groups
				Where GroupID = #GetItem.GroupID#
		</cfquery>
		<cfif GetGroup.RecordCount GT 0>
			<cfset gord=GetGroup.GroupOrder>
			<cfset gname=GetGroup.GroupName>
		</cfif>
		<cfset formact="item_update.cfm?ItemID=" & URL.ItemID>
		<cfset subtext="Update Group & Item">
		<cfset iname=GetItem.ItemName>
		<cfset iord=GetItem.ItemOrder>
		<cfset iprice=Replace(DecimalFormat(GetItem.Price),",","","All")>
		<cfset sprice=Replace(DecimalFormat(GetItem.salePrice),",","","All")>		
	</cfif>
</cfif>
<cfif not Isdefined("URL.GroupID") and not ISDefined("URL.ItemID")>
	<cfquery name="GetMaxGOID" datasource="#dsn#">
		select Max(GroupOrder) as MaxID from Groups
	</cfquery>
	<cfif GetMaxGOID.MaxID GT "">
		<cfset gord=GetMaxGOID.MaxID + 1>
	</cfif>
</cfif>

<cfoutput>
<cfform action="#formact#" name="AddGrItems" id="AddGrItems" method="POST" enctype="multipart/form-data" scriptSrc="http://kite.boreal.org/CFIDE/scripts">
<cfif IsDefined("URL.GroupID")><input type="hidden" name="GroupID" value="#URL.GroupID#"></cfif>
<table width="100%">
<TR>
<td colspan=2 bgcolor="##DDDDDD" align="Center"><font color="##000000">
	<B>Group Information - this information will be shared by all items in the group.</B>
</font></td>
</tr>
<TD>Line:</TD>
<TD>
<cfif #isdefined("URL.LineID")#>
	<cfset sel="#URL.LineID#">
<cfelseif isdefined("GetGroup.LineID")>
	<cfset sel="#GetGroup.LineID#">
<cfelse>
	<cfset sel="">
</cfif>
<cfselect name="LineID" required="yes" message="You must select an Line" query="Get_Lines"
  value="LineID" display="LineName" selected="#sel#"></cfselect>
</TD></TR>
<TR>
<TD>Group Name:</TD>
<TD>
(This will be displayed above all the items in the group.)<BR>
<cfinput type="text" name="GroupName" size="60" maxlength="150" required="yes" value="#gname#">
</TD>
</TR>
<TR>
<TD>
Group Description:
</TD>
<TD>
(This will be displayed above all the items in the group.)<BR>
<cfset conf=structNew()>
<cfset conf.CustomConfigurationsPath="/schoolstore/admin/editorconf.js">
<cfif IsDefined("GetGroup.GroupDesc")>
	<cfset val=GetGroup.GroupDesc>
<cfelse>
	<cfset val="">
</cfif>

<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="groupdesc"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</TR>
<!----<cfquery name="GetCommunity" datasource="#dsn#">
	Select * From Communities
	order by CommCode
</cfquery>
<tr>
<td>
Community:
</TD>
<TD>
<cfif isdefined("GetGroup.CommCode")>
	<cfset sel=GetGroup.CommCode>
<cfelse>
	<cfset sel="00">
</cfif>
<cfselect name="CommCode" required="no" query="GetCommunity"
  value="CommCode" display="CommName" selected="#sel#"></cfselect>
</TD>
</tr> ----->

<TR>
<TD VALIGN="Bottom">
	Placement: 
</TD>
<TD>
	(Groups will be displayed under the Line in order by this number)<BR>
	<cfinput type="Text" name="GroupOrder" size="60" required="no" validate="integer" message="Group placement must be an integer" value="#gord#">
</TD>
</TR>
<tr>
<td>Related Course:</td>
<td>
<cfquery name="GetCourses" datasource="#dsn#">
	select cid, name from courses order by name
</cfquery>
<cfif isDefined("GetGroup.cid") and getGroup.cid GT 0>
	<cfset val=getgroup.cid>
<cfelse>
	<cfset val=0>
</cfif>
<cfselect query="GetCourses" name="cid" id="cid" display="name" value="cid" selected="#val#" queryPosition="below">
<option value="0">--Select--</option>
</cfselect>
</td>
</tr>

<TR>
<TD>
Main Picture:
</TD>
<TD>
<input type="File" value="Get Picture" name="Picture">
<!---cfinput type="text" name="Pic"--->
<cfif IsDefined("GetGroup.Picture") and GetGroup.Picture GT "">
	</td></TR>
	<TR>
	<td>Current Picture:</td>
	<td ALIGN="CENTER"><IMG src="../#GetGroup.Picture#"><BR>
	<input type="checkbox" value="Yes" name="Delpic"> Delete Picture</td>
	</TR>
</cfif>

</TD>
</TR>
<TR>
<TD>
Large Picture:
</TD>
<TD>
<input type="File" value="Get Picture" name="LgPicture">
<!---cfinput type="text" name="Pic"--->
<cfif IsDefined("GetGroup.LgPicture") and GetGroup.LgPicture GT "">
	</td></TR>
	<TR>
	<td>Current Picture:</td>
	<td ALIGN="CENTER"><IMG src="../#GetGroup.LgPicture#"><BR>
	<input type="checkbox" value="Yes" name="DelLpic"> Delete Picture</td>
	</TR>
</cfif>

</TD>
</TR>

<!---TR>
<TD>
Picture Placement:
</TD>
<TD>
<input type="radio" name="Picplace" value="1" <cfif (IsDefined("GetGroup.PicturePlace") and GetGroup.PicturePlace LT 2) OR Not IsDefined("GetGroup.PicturePlace")>checked</cfif>>Left<BR>
<input type="radio" name="Picplace" value="2" <cfif IsDefined("GetGroup.PicturePlace") and GetGroup.PicturePlace EQ 2>checked</cfif>>Right<BR>
<input type="radio" name="Picplace" value="3" <cfif IsDefined("GetGroup.PicturePlace") and GetGroup.PicturePlace EQ 3>checked</cfif>>Top<BR>
<input type="radio" name="Picplace" value="4" <cfif IsDefined("GetGroup.PicturePlace") and GetGroup.PicturePlace EQ 4>checked</cfif>>Bottom
<P></P>
</TD>
</TR--->

<TR>
<td colspan=2 bgcolor="##DDDDDD" align="Center"><font color="##000000">
	<B>Item Information - this information is specific to the item in the group.</B>
</font></td>
</tr>

<TR>
<TD>
Item Name:
</TD>
<TD>
<cfinput type="text" name="ItemName" required="Yes" size="60" value="#iname#">
</TD>
</TR>
<TR>
<TD>
Item Code (if applicable):
</TD>
<TD>
<input type="text" name="ItemCode" size="60" <cfif IsDefined("GetItem.ItemCode")>value="#GetItem.ItemCode#"</cfif>>
</TD>
</TR>
<TR>
<TD>
Description/Dimensions:
</TD>
<TD>
<cfset conf=structNew()>
<cfset conf.CustomConfigurationsPath="/schoolstore/admin/editorconf.js">
<cfif IsDefined("GetItem.ItemDesc")>
	<cfset val=GetItem.ItemDesc>
<cfelse>
	<cfset val="">
</cfif>

<cfmodule
	template="fckeditor/fckeditor.cfm"
	basePath="fckeditor/"
	instanceName="itemdesc"
	value='#val#'
	width="625"
	height="300"
	config="#conf#"
	toolbarset="nhouse"
>

</TD>
</TR>
<tr>
<td>Weight:</td>
<cfif isDefined("GetITem.weight")><cfset val=GetItem.weight><cfelse><cfset val=""></cfif>
<td><cfinput type="text" name="weight" size="60" value="#val#" validate="float" message="Please enter the item weight as a number with up to 2 decimal places"></td>
</tr>

<TR>
<TD>
Price:
</TD>
<TD>
<cfinput type="text" name="Price" required="Yes" validate="float" message="Price is required, and must be a number" value="#iprice#" size="60">
</TD>
</TR>
<TR>
<TD>
Sale Price:
</TD>
<TD>
<cfinput type="text" name="saleprice" required="Yes" validate="float" message="Sale Price must be a number" value="#sprice#" size="60"><br/>
(Set to 0 if not on sale)
</TD>
</TR>
<tr>
<td>Out of stock?</td>
<td><input type="checkbox" name="outofstock" value="yes" <cfif IsDefined("GetItem.outofstock") and getItem.outofstock>checked</cfif>></td>
</tr>
<tr>
<td>Best seller:</td>
<td><input type="checkbox" name="bestseller" value="yes" <cfif IsDefined("GetItem.bestseller") and getItem.bestseller eq true>checked</cfif>></td>
<tr>
</tr>
<td>New item:</td>
<td><input type="checkbox" name="newitem" value="yes" <cfif IsDefined("GetItem.newitem") and getItem.newitem eq true>checked</cfif>></td>
<tr>
</tr>
<td>Staff pick:</td>
<td><input type="checkbox" name="recommended" value="yes" <cfif IsDefined("GetItem.recommended") and getItem.recommended eq true>checked</cfif>></td>
</tr>
<tr>
<td>Instructor pick:</td>
<td><input type="checkbox" name="instructor" value="yes" <cfif IsDefined("GetItem.instructor") and getItem.instructor eq true>checked</cfif>></td>
</tr>
<tr>
<td>Tool of the season:</td>
<td><input type="checkbox" name="toolpic" value="yes" <cfif IsDefined("GetItem.toolpic") and getItem.toolpic eq true>checked</cfif>></td>
</tr>

<TR>
<TD VALIGN="Bottom">
	Placement: 
</TD>
<TD>
	(Items will be displayed within the group in order by this number)<BR>
	<cfinput type="Text" name="ItemOrder" size="60" required="no" validate="integer" message="Item placement must be an integer" value="#iord#">
</TD>
</TR>

<td VALIGN="Bottom">
Options:
</td>
<td>
Specify options such as color, pattern, or bead type.  List all the available
options separated by semi-colons.  These options will appear in a drop-down list
in the catalog.<BR>
<cfif IsDefined("URL.ItemID")>
	<cfquery name="GetOpts" datasource="#dsn#">
		select OptionName from ItemOptions
			Where ItemID = #URL.ItemID#
	</cfquery>
	<cfif GetOpts.RecordCount GT 0>
		<cfset iopts=ValueList(GetOpts.OptionName, ";")>
	</cfif>
</cfif>
<input type="text" name="ItemOpts" size="60" <cfif isdefined("iopts")>value="#iopts#"</cfif>>
</td>
</tr>
<tr>
<td valign="Bottom"> 
Sizes:
</td>
<TD>
Specify available sizes, such as clothing size, ring size, or jewelry length.
List all the available sizes separated by semi-colons.  These sizes will appear 
in a drop-down list in the catalog.<BR>
<cfif IsDefined("URL.ItemID")>
	<cfquery name="GetSizes" datasource="#dsn#">
		select Size from ItemSizes
			Where ItemID = #URL.ItemID#
	</cfquery>
	<cfif GetSizes.RecordCount GT 0>
		<cfset iszs=ValueList(GetSizes.Size, ";")>
	</cfif>
</cfif>

<input type="text" name="ItemSize" size="60" <cfif IsDefined("iszs")>value="#iszs#"</cfif>>
</TD>
</tr>


<TR>
<TD COLSPAN=2 ALIGN=Center>
<input type="submit" value="#subtext#">
&nbsp;&nbsp;<input type="button" value="Start New Group" onClick="location.href='addgroup.cfm<cfif isdefined("URL.LineID")>?LineID=#URL.LineID#</cfif>';">
</TD>
</TR>
</TABLE>


</cfform>
</cfoutput>
<HR>
<a name="eitem"></a>
<cfinclude template="listitems.cfm">
<cfinclude template="bottom.cfm">