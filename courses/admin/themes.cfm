<cfinclude template="header.cfm">
<cfquery name="GetThemes" datasource="#dsn#">
	select tid, theme from themes order by
		theme
</cfquery>
<div class="sidebar">
	<ul>
		<li><a href="themes.cfm">New Theme</a></li>
		<cfoutput query="GetThemes">
			<li><a href="themes.cfm?tid=#tid#">#theme#</a></li>
		</cfoutput>
	</ul>
</div>
<div class="form">
<h2>Update Course Themes</h2>

<cfif IsDefined("URL.x")>
	<cfoutput>
		<B>Theme has been updated!</B><P>
		</P>
	</cfoutput>
</cfif>


<cfoutput>
<cfform name="part" action="theme_update.cfm" method="POST" scriptsrc="http://kite.boreal.org/cfide/scripts">

<cfif IsDefined("url.tid")>
	<cfquery name="GetTheme" datasource="#dsn#">
		select * from themes where
			tid=<cfqueryparam cfsqltype="CF_SQL_INTEGER" value="#url.tid#">
	</cfquery>
	<cfif GetTheme.recordcount GT 0>
		<cfoutput>
		<input type="hidden" name="tid" value="#url.tid#">
		</cfoutput>
	</cfif>
</cfif>

<label for="theme">Theme name:</label>
<cfif IsDefined("GetTheme.theme")><cfset val=getTheme.theme><cfelse><cfset val=""></cfif>
<cfinput type="text" name="theme" id="theme" size="50" required="Yes" value="#val#" maxlength="100" message="Please enter the theme name">
<br/>
<label for="page_title">Title for theme page:</label>
<input type="text" name="page_title" id="page_title" size="50" <cfif isDefined("getTheme.page_title")> value="#getTheme.page_title#"</cfif>>
<br/>
<label for="page_desc">Theme description:</label>
<textarea name="page_desc" id="page_desc" rows="5" cols="40"><cfif isDefined("GetTheme.page_desc")>#getTheme.page_desc#</cfif></textarea>
<br/>

<input type="submit" value="Save Theme">
<cfif IsDefined("getTheme.tid")>
 	<input type="button"  class="button" onclick="if (confirm('Are you sure you want to delete the #GetTheme.theme# theme?  This will also delete any courses that are under this theme.')) { location.href='theme_update.cfm?tid=#url.tid#&del=yes'; }" value="Delete Theme">
</cfif>
</cfform>
</cfoutput>
</div>
<br clear="all"/><br/>
<cfinclude template="footer.cfm">