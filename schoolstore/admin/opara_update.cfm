<cfif not IsDefined("form.sh_text#ordering_code#")>
	<cflocation url="addopara.cfm">
</cfif>

<cfquery name="GetData" datasource="#dsn#">
	select * from shopping_data
</cfquery>


<!--- ordering info --->
<cfquery name="GetPara" dbtype="query">
	select * from GetData where pid='#ordering_code#'
</cfquery>

<cfif GetPara.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into shopping_data
			(pid, sh_text)
			values (
				'#ordering_code#',
				'#form["sh_text#ordering_code#"]#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update shopping_data
			Set sh_text='#form["sh_text#ordering_code#"]#'
			where pid='#ordering_code#'
	</cfquery>
</cfif>

<!--- guarantee info --->
<cfquery name="GetPara" dbtype="query">
	select * from GetData where pid='#guarantee_code#'
</cfquery>

<cfif GetPara.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into shopping_data
			(pid, sh_text)
			values (
				'#guarantee_code#',
				'#form["sh_text#guarantee_code#"]#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update shopping_data
			Set sh_text='#form["sh_text#guarantee_code#"]#'
			where pid='#guarantee_code#'
	</cfquery>
</cfif>

<!--- returns info --->
<cfquery name="GetPara" dbtype="query">
	select * from GetData where pid='#return_code#'
</cfquery>

<cfif GetPara.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into shopping_data
			(pid, sh_text)
			values (
				'#return_code#',
				'#form["sh_text#return_code#"]#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update shopping_data
			Set sh_text='#form["sh_text#return_code#"]#'
			where pid='#return_code#'
	</cfquery>
</cfif>

<!--- delivery info --->
<cfquery name="GetPara" dbtype="query">
	select * from GetData where pid='#delivery_code#'
</cfquery>

<cfif GetPara.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into shopping_data
			(pid, sh_text)
			values (
				'#delivery_code#',
				'#form["sh_text#delivery_code#"]#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update shopping_data
			Set sh_text='#form["sh_text#delivery_code#"]#'
			where pid='#delivery_code#'
	</cfquery>
</cfif>

<!--- shipping info --->
<cfquery name="GetPara" dbtype="query">
	select * from GetData where pid='#shipping_code#'
</cfquery>

<cfif GetPara.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into shopping_data
			(pid, sh_text)
			values (
				'#shipping_code#',
				'#form["sh_text#shipping_code#"]#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update shopping_data
			Set sh_text='#form["sh_text#shipping_code#"]#'
			where pid='#shipping_code#'
	</cfquery>
</cfif>

<!--- privacy info --->
<cfquery name="GetPara" dbtype="query">
	select * from GetData where pid='#privacy_code#'
</cfquery>

<cfif GetPara.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into shopping_data
			(pid, sh_text)
			values (
				'#privacy_code#',
				'#form["sh_text#privacy_code#"]#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update shopping_data
			Set sh_text='#form["sh_text#privacy_code#"]#'
			where pid='#privacy_code#'
	</cfquery>
</cfif>

<!--- security info --->
<cfquery name="GetPara" dbtype="query">
	select * from GetData where pid='#security_code#'
</cfquery>

<cfif GetPara.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into shopping_data
			(pid, sh_text)
			values (
				'#security_code#',
				'#form["sh_text#security_code#"]#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update shopping_data
			Set sh_text='#form["sh_text#security_code#"]#'
			where pid='#security_code#'
	</cfquery>
</cfif>

<!--- gift info --->
<cfquery name="GetPara" dbtype="query">
	select * from GetData where pid='#gift_code#'
</cfquery>

<cfif GetPara.RecordCount eq 0>
<!--- no existing data - add a new record --->
	<cfquery name="AddData" datasource="#dsn#">
		insert into shopping_data
			(pid, sh_text)
			values (
				'#gift_code#',
				'#form["sh_text#gift_code#"]#'
			)
	</cfquery>
<cfelse>
	<!--- update existing record --->
	<cfquery name="UpdData" datasource="#dsn#">
		Update shopping_data
			Set sh_text='#form["sh_text#gift_code#"]#'
			where pid='#gift_code#'
	</cfquery>
</cfif>

<cflocation url="addopara.cfm?x=x">
