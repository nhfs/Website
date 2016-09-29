<cfif not IsDefined("URL.ItemID")>
	<cflocation url="additem.cfm">
</cfif>
<!--- first - delete associated pictures, if any --->
<cfquery name="GetGID" datasource="#dsn#">
	select GroupID, Grouped from Items
		Where ItemID=#URL.ItemID#
</cfquery>

<cfquery name="CheckGroup" datasource="#dsn#">
	Select NbrInGroup from Groups
	Where GroupID=#GetGID.GroupID#
</cfquery>

<cfif CheckGroup.NbrInGroup EQ 1>
	<cfquery datasource="#dsn#" name="GetPic">
		select Picture, LgPicture, thumb from Groups INNER JOIN Items
			ON Items.GroupID = Groups.GroupID
			Where ItemID=#URL.ItemID#
	</cfquery>
</cfif>

<cftransaction>
	<cfquery name="DelItem" datasource="#dsn#">
		Delete from Items
			WHERE ItemID=#URL.ItemID#
	</cfquery>

	<cfquery name="DelItems" datasource="#dsn#">
		Delete from ItemSizes
			WHERE ItemID=#URL.ItemID#
	</cfquery>

	<cfquery name="DelItemo" datasource="#dsn#">
		Delete from ItemOptions
			WHERE ItemID=#URL.ItemID#
	</cfquery>

	<cfif CheckGroup.NbrInGroup eq 1>
		<cfquery name="DelGroup" datasource="#dsn#">
			delete from Groups 
				where GroupID = #GetGID.GroupID#
		</cfquery>
	<cfelse>
		<cfquery name="UpdGroup" datasource="#dsn#">
			update Groups 
				set NbrInGroup = #CheckGroup.NbrInGroup# - 1
				where GroupID = #GetGID.GroupID#
		</cfquery>
	</cfif>

</cftransaction>

<cfif CheckGroup.NbrInGroup EQ 1>
	<cfif GetPic.Picture GT "">
		<cfset fn="#fileloc#" & Replace(GetPic.Picture, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>

	<cfif GetPic.thumb GT "">
		<cfset fn="#fileloc#" & Replace(GetPic.thumb, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>

	<cfif GetPic.LgPicture GT "">
		<cfset fn="#fileloc#" & Replace(GetPic.LgPicture, "/","\","All")>
		<cflock name="Filelock" Type="Exclusive" Timeout="30">
			<cffile action="Delete"
				file=#fn#>
		</cflock>
	</cfif>
</cfif>

<cfif GetGID.Grouped>
	<cflocation url="addgroup.cfm?z=Z">
<cfelse>
	<cflocation url="additem.cfm?z=Z">
</cfif>

