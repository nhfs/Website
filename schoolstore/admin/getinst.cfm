<cfif isDefined("url.iid")>
	<cfquery name="GetInst" datasource="#dsn#">
		select iid, lname, fname, photo from instructors
		where iid=#url.iid#
	</cfquery>
	<cfoutput query="GetInst">
fname-#fname#-lname-#lname#-photo-#photo#	
	</cfoutput>
<cfelse>
fname-err-lname-err-photo-
</cfif>