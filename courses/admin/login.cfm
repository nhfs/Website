	<cfinclude template="header.cfm">
	<cfform name="login" action="#CGI.script_name#?#CGI.query_string#" method="POST">
		Please log into the administrative system:
		<table>
		<TR>
		<TD>Login name:</TD>
		<TD><cfinput type="text" size="30" required="yes" name="j_username"></TD>
		</TR>
		<TR>
		<TD>Password:</TD>
		<TD><cfinput type="password" size="30" required="yes" name="j_password"></TD>
		</TR>
		<TR>
		<TD colspan="2" align="center">
			<input type="submit" value="Sign In">
		</TD>
		</TR>
		</table>
	</cfform>
	<cfinclude template="footer.cfm">
	<cfabort>
