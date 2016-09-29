<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?xml version="1.0" encoding="utf-8"?>


<html>
<head>
<title>Clear Old Courses</title>
</head>

<body>
<cfquery name="ClearCourses" datasource="#dsn#">
	delete from course_dates where startdt <= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
</cfquery>


</body>
</html>

