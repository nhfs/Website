<cfif not isdefined("URL.CatID")>
	<cflocation url="addcategory.cfm">
</cfif>

<cfquery datasource="#dsn#" name="GetPic">
	select CatPicture from Categories
		Where CatID='#URL.CatID#'
</cfquery>

<cfquery datasource="#dsn#" name="DelID">
	Delete from Categories
		WHERE CatID = '#URL.CatID#'
</cfquery>

<!--- delete associated pictures, if any --->
<cfif GetPic.CatPicture GT "">
	<cfset fn="#fileloc#" & Replace(GetPic.CatPicture, "/","\","All")>
	<cflock name="FileLock" type="Exclusive" timeout="30">
	<cffile action="Delete"
		file=#fn#>
	</cflock>
</cfif>

<cflocation url="addcategory.cfm?z=Z">