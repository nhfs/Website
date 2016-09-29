<cfif not IsDefined("URL.ItemID") and not isdefined("Url.groupid")>
	<cflocation url="index.cfm">
</cfif>
<cfquery datasource="#dsn#" name="GetCat">
	Select catid, subcats.scatid from subcats
		INNER JOIN (_lines INNER JOIN 
		(groups INNER JOIN Items ON groups.groupId=items.groupid)
		ON _lines.lineID=groups.lineid) on subcats.scatid=_lines.scatid 
		WHERE ItemID = <cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#URL.ItemID#">
</cfquery>
<cfif GetCat.RecordCount eq 0>
	<script language="Javascript">
		alert ('Error finding this item.  Please go back and try again');
		history.back(1);
	</script>
	<cfabort>
<cfelse>
	<cfset url.catid=GetCat.CatID>
	<cfset url.scatid=GetCat.scatid>
</cfif>

<cfset masthead="canoe">
<cfinclude template="Templates/topper.cfm">
<!-- shopping cart software copyright 2003 Boreal Access.  All rights reserved -->
<cfinclude template="Templates/sidebar.cfm">
<script language="JavaScript">
function checkform(fm) {
	var ok=true;
	var msg="";

	if (!(fm.ItemOptions == null) && fm.ItemOptions.selectedIndex == 0) {
		msg += "Please select an option\n";
		ok=false;
	}
	if (!(fm.ItemSizes == null) && fm.ItemSizes.selectedIndex == 0) {
		msg += "Please select a size\n";
		ok=false;
	}
	if (!ok) {
		alert (msg);
	}
	return ok;
}
	
</script>

<cfquery name="GetItem" datasource="#dsn#">
	select items.*, groups.* from groups INNER JOIN Items
		ON Groups.groupid = items.groupid
		<cfif isDefined("url.itemid")>
		where ItemID = #url.itemid#
		<cfelse>
		where GroupID = #url.groupid#
		</cfif>
</cfquery>

<div class="data">
<cfset grp=1>
<cfoutput query="GetItem">

<cfquery name="GetOpts" datasource="#dsn#">
	Select * from ItemOptions
		Where ItemID = #ItemID#
</cfquery>
<cfquery name="GetSizes" datasource="#dsn#">
	Select * from ItemSizes
		Where ItemID = #ItemID#
</cfquery>

<cfif grp eq 1>
<!--- get picture dimensions --->
<cfif picture GT "">
	<cfset imageCFC = createObject("component","image")>
	<cfset imgInfo = imageCFC.getImageInfo("", "#fileloc##picture#")>
	<cfif imgInfo.width GT 250>
		<div class="right"><cfinclude template="Templates/cartcnt.cfm"></div>
		<div align="center">
		<cfif LgPicture GT "">
			<img src="#lgpicture#" alt="#groupname#" border="0">
		<cfelseif Picture GT "">
			<IMG SRC="#Picture#" Alt="#groupName#" BORDER="0">
		</cfif>
		</div>
	<cfelse>
		<div class="descleft" style="width: #imgInfo.width#px;">
		<cfif LgPicture GT "">
			<img src="#lgpicture#" alt="#groupname#" border="0">
		<cfelseif Picture GT "">
			<IMG SRC="#Picture#" Alt="#groupName#" BORDER="0">
		</cfif>
		</div>
		<div class="descright">
		<div class="right"><cfinclude template="Templates/cartcnt.cfm"></div>
	</cfif>
</cfif>
</cfif>

<cfif grp eq 1><B>#GroupName#</b><br/>
	<cfif grouped><br/>#REReplace(GroupDesc,"\n[\s]*","<P>","All")#<BR>
	</cfif>
</cfif>
<cfif grouped><br/><b>#itemName#<br/></cfif></B>
	<cfif newitem eq true><span class="red">*NEW ARRIVAL*</span></cfif>
	<cfif bestseller eq true><span class="red">*BEST SELLER*</span></cfif>
	<cfif recommended eq true or instructor eq true><span class="red">*RECOMMENDED*</span></cfif>
	<cfif newitem eq true or bestseller eq true or recommended eq true or instructor eq true><br/></cfif>
</br>
<cfif grouped>#REReplace(ItemDesc,"\n[\s]*","<P>","All")#<BR><cfelse>
#REReplace(GroupDesc,"\n[\s]*","<P>","All")#<BR>
</cfif>	
<cfif cid GT 0>
	<cfquery name="getCourse" datasource="#dsn#">
		select name, isevent from courses where cid=#cid#
	</cfquery>
	<cfif getCourse.recordcount GT 0>
	<br/><b>If you like this item, you may also be interested in our 
	<a href="../courses/courses/course.cfm?cid=#cid#"><i>#getCourse.name#</i></a> 
	<cfif getcourse.isevent eq true>event<cfelse>course</cfif>.</b>
	<br/><br/>
	</cfif>
</cfif>

<cfform action="viewcart.cfm?ID=#ItemID#" method="POST" scriptSrc="http://kite.boreal.org/CFIDE/scripts" onSubmit="return checkform(_CF_this)">
	<input type="Hidden" name="ItemCode" value="#ItemCode#">
	<cfif outofstock>
		<b>Sorry, this item is temporarily out of stock</b>
	<cfelseif Price eq 0>	
		<B>Please call for pricing:</b> #co_phone#<BR>
	<cfelse>
		<cfif GetOpts.RecordCount GT 0>
			<b>Options:</b> 
			<cfselect query="GetOpts" 
				display="OptionName" 
				value="OptionName" 
				name="ItemOptions" 
				queryPosition="below">
			<option value="--select--">--Select--</option></cfselect><br/>
		</cfif>
		<cfif GetSizes.RecordCount GT 0>
			<b>Size:</b> 
			<cfselect query="GetSizes" 
				display="Size" 
				value="Size"
				name="ItemSizes" 
				queryPosition="below">
			<option value="--select--">--Select--</option>	</cfselect>
		</cfif>
		<b>Price:</b> <cfif saleprice GT 0><span class="struck"></cfif>#DollarFormat(Price)#
		<cfif saleprice gt 0></span> <span class="sale"><br/>On sale: #dollarformat(saleprice)#</span></cfif>
		<br/>
		<b>Quantity:</b> <input name="QtyOrdered" value="1" size="2"><br/>
		<input type="Submit" Value="Order">
	</cfif>
</cfform>

<cfif isdefined("imgInfo") and imgInfo.width LE 250></div><br clear="all"/></cfif>

<a href="catalog.cfm?catid=#Getcat.Catid#&scatid=#GetCat.scatid###i#ItemID#">Back to Item List</a>
<cfset grp=grp+1>
</cfoutput>	
</div>
<br clear="all"/>
<br/>
<cfinclude template="Templates/bottom.cfm">