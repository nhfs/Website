<cfquery name="GetCats" datasource="#dsn#">
	select catname, catid from categories
</cfquery>

<cfloop query="GetCats">
	<cfquery name="AddsCat" datasource="#dsn#">
		insert into subcats (catid, scatname, scatorder)
			values ('#catid#', '#catname#',1)
	</cfquery>
	<cfquery name="getID" datasource="#dsn#">
		select scatid from subcats where catid='#catid#'
	</cfquery>
	<cfquery name="UpdLines" datasource="#dsn#">
		update _lines
			set scatid=#Getid.scatid#
			where catid='#catid#'
	</cfquery>
</cfloop>