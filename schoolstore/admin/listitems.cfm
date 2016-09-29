<DIV ALIGN="CENTER"><B><FONT SIZE=+1>Existing Items</FONT></B></DIV>
<cfquery datasource="#dsn#" name="GetItems">
	Select Items.*, _Lines.*, Groups.*, CatName, scatname, c.name
		FROM (Items INNER JOIN (Groups INNER JOIN 
			(_Lines INNER JOIN 
			(subcats INNER JOIN Categories ON subcats.catid=Categories.catid)
			ON _Lines.sCatID = subcats.sCatID)
			ON Groups.LineID = _Lines.LineID)
			ON Items.GroupID = Groups.GroupID) LEFT JOIN courses c on groups.cid=c.cid
		ORDER BY CatName, scatname, LineOrder, LineName, GroupOrder, Groups.GroupID, ItemOrder,
		ItemName
</cfquery>
<cfset cat="">
<cfset scat="">
<cfset art=0>
<cfset GID=0>
<DIV ALIGN="Center">
<cfset catidx=1>
<cfoutput query="GetItems">
<cfif cat neq catname>
	<br/><a href="##c#catname#"><b>#catname#</b></a><br/>
	<cfset cat=catname>
	<cfset catidx=1>
</cfif>

<cfif scat NEQ sCatName>
	<A HREF="###sCatid#">#sCatName#</A>
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
<TABLE WIDTH="100%" BORDER=0>
<cfoutput query="GetItems">
	<cfif cat neq CatName>
		<tr class="tier0">
		<td Colspan="9" align="center">
			<A Name="c#Catname#"></A>
			#CatName#
		</td>
		</tr>
		<cfset cat=CatName>
	</cfif>
	<cfif scat neq sCatName>
		<tr class="tier1">
		<td Colspan="9" align="center">
			<A Name="#sCatID#"></A>
			#sCatName#
		</td>
		</tr>
		<cfset scat=sCatName>
	</cfif>
	<cfif art neq LineID>
		<tr class="tier2">
		<td colspan="9" align="Center">
			#LineName#		
		</td>
		</tr>
		<tr class="tier3">
		<td>
			Item
		</td>
		<td>
			Code
		</td>
		<td>
			Description
		</td>
		<td>
			Price
		</td>
		<td>Options</td>
		<td>
			Picture Place
		</td>
		<td></td>
		<td></td>
		<td></td>
		</tr>
		<cfset art=LineID>
	</cfif>
	<cfif Grouped and GroupID neq GID>
		<TR>
		<TD><B>#GroupName# - #GroupID#</B></TD>
		<TD></TD>
		<TD><B>#REReplace(GroupDesc,"\n","<BR>","All")#</B></TD>
		<TD></TD>
		<td></td>
		<td>
			<B><cfswitch expression=#PicturePlace#>
				<cfcase value=1>
					left
				</cfcase>
				<cfcase value=2>
					right
				</cfcase>
				<cfcase value=3>
					top
				</cfcase>
				<cfcase value=4>
					bottom
				</cfcase>
			</cfswitch></B>
		</td>	
		<td colspan="3"></td>	
		</TR>
		<cfset GID=GroupID>
	</cfif>
	<TR>
	<TD>
		<cfif Grouped>#ItemName#&nbsp;* - #GroupID#<cfelse>#GroupName#</cfif>
	</TD>
	<TD>
		#ItemCode#
	</TD>
	<td>
		<cfif Grouped>#REReplace(ItemDesc,"\n","<BR>","All")#<cfelse>#REReplace(GroupDesc,"\n","<BR>","All")#</cfif>
		<cfif name GT ""><br/><br/><b>Course:</b> #name#</cfif>
	</td>
	<td>
		<cfif saleprice GT 0><span class="struck"></cfif>#DollarFormat(Price)#
		<cfif saleprice gt 0></span><br/>
		#dollarformat(saleprice)#
		</cfif>
	</td>
	<td><cfset opts=false>
		<cfif outofstock>out of stock<cfset opts=true></cfif>
		<cfif bestseller><cfif opts><br/><cfelse><cfset opts=true></cfif>best seller</cfif>
		<cfif newitem><cfif opts><br/><cfelse><cfset opts=true></cfif>new item</cfif>
		<cfif recommended><cfif opts><br/><cfelse><cfset opts=true></cfif>staff pick</cfif>
		<cfif instructor><cfif opts><br/><cfelse><cfset opts=true></cfif>instructor pick</cfif>
	</td>
	<td>
		<cfif not Grouped>
		<cfswitch expression=#PicturePlace#>
			<cfcase value=1>
				left
			</cfcase>
			<cfcase value=2>
				right
			</cfcase>
			<cfcase value=3>
				top
			</cfcase>
			<cfcase value=4>
				bottom
			</cfcase>
		</cfswitch>
		</cfif>
	</td>
	<td>
		<cfif Grouped>
			<a href="addgroup.cfm?ItemID=#ItemID#">Edit Item</a>
			</td><td><a href="addgroup.cfm?GroupID=#GroupID#">Add New Item to Group</a>
		<cfelse>
			<a href="additem.cfm?ItemID=#ItemID#">Edit Item</a>
			</td><Td>
		</cfif>

	</td>
	<td>
		<A href="item_delete.cfm?ItemID=#ItemID#">Delete Item</A>
	</td>
	</TR>
</cfoutput>


</TABLE>
*'d items are grouped together
