<?php
//version5681//

	define("SUMAC_CODE_VERSION" , '5.6.8.1');
	define("SUMAC_CODE_DATE" , '2015-02-27');

//version 4 is the multi-function version combining the four earlier Sumac online packages
//			and adding a new package, Courses.
//			Comments in the latest releases of the four single packages contain their history.
//			As of April 2013 there are still no live users for the Courses package.

//version 4.1.7, 24 April 2013
//	1. Allow user to define the messages that say what to do when you have finished picking tickets for an event (ticketing)
//	2. Allow user to request a note to accompany ticket payment and accept that note and pass it to Sumac (ticketing)
//	3. Use '%0','%1', etc for substitutions instead of '&0','&1', etc in user-accessible messages (donations)
//version 4.1.8, 05 May 2013
//	1. Implement 'SINGLE_PACKAGE' flag that allows 4.x behaviour to more closely follow 3.x versions.
//	2. Add many new parameters to make titling with multiple packages behave properly.
//	3. Add many other new parameters to make the user setting of instructions with multiple packages behave properly.
//	4. Fix bug in membership renewal introduced in 4.1.7
//	5. Modify testing HTML (sumac.htm) to allow simulation of version 3 packages.
//version 4.2.0, 28 May 2013
//	1. Revise membership renewal to support new memberships - change renewal to membership throughout.
//	2. New feature in Membership - Optional Extras
//	3. Fix to ensure currency symbol is always available for java code
//version 4.2.1, 29 May 2013
//	1. Correct documentation in guide to say that initial package name is 'membership' not 'renewal'
//version 4.3.0, 04 July 2013
//	1. New feature in Ticketing - option to use a seating-plan image with dropdowns for seat picks
//	2. Totally new feature - email statistics, implemented almost entirely in 'newsletter.php'
//	3. sumac_formatMessage() function made available to JavaScript as well as PHP
//	4. DTD files for 'theatre' and 'course_catalog' separated from 'organisation'.
//version 4.3.2, 01 August 2013
//	1. Revised layout for seat selection panel
//	2. Default background colour for seat selection (= <location> theatre_colour attribute) changed to White
//	3. (incorporated from 4.2.2) Fix bug in handling login with a ticketing order with value of zero
//version 4.3.3, 16 August 2013
//	1. Relabel Credit and Debit columns as Payments and Charges in Courses Financial report
//	2. Right-justify balance column in Courses Financial report
//	3. Left-justify exam name column in Courses Education report
//version 4.4.0, 25 August 2013
//	1. All strings suitable for user replacement instead of being defined as constants here,
//		are now retrieved from /user/sumac_strings.settings by new function sumac_load_strings()
//version 4.5.0, 01 September 2013
//	1. Implementation of 'No Catalog' form of Courses package where catalog (course listing) is OUTSIDE Sumac online
//	2. Convert all remaining uses of '&0','&1', etc for substitutions in messages into Sumac standard of '%0','%1', etc.
//	3. Fix bug in sumac_formatDate() when using 'yyy' in pattern
//	4. Prevent the 'floating' navigation bars from drifting around the main table by adding 'clear:both;' to table style
//	5. Restore 4.3.3 fixes to Financial and Education reports that got somehow dropped from 4.4.0
//version 5.0.0, 10 October 2013
//	1. Implementation of new package, Directories
//	2. Add User IP address as part of websitedata in all messages sent to Sumac
//version 5.0.1, 07 November 2013
//	1. Fix bug in Directories where '<','>','&','"' in selection values were not being converted
//	2. Fix bug in Directories where button for choosing same or new window for results only worked for first directory
//	3. Updates to the user guide that had been missed from the previous two releases
//	4. Don't display an empty box when there is no 'text' attribute for a directory
//	5. Change the positioning of some buttons on the new Directories pages
//	6. Shrink to a minimum the lefthand column on the selection page for Directories
//	7. Add minimal formatting for directory entries: blank between list entries and boldness for table column titles
//	8. Correct HTML errors in Directories entries display: class cannot be null, </th> should have been </td>
//version 5.0.2, 11 November 2013
//	1. Add more minimal formatting for directory entries: thin grey grid lines around table cells
//	2. Ensure HTML sizing of the directory 'search' button adjusts to accommodate any text length
//	3. Add installation verification utility (requires a version number in all code files)
//	4. Transfer the PHP version check from the startup files to this utility
//version 5.0.3, 11 November 2013
//	1. Another update missing from the user guide ('nodirectory')
//	2. Add support for new entry points to the Courses package giving access to the Personal stuff (via login)
//	3. Allow a new user to access the Courses Personal stuff for Forms or Payment
//	4. Fix the behaviour when login/logout is used within the Directories package
//version 5.0.4, 11 November 2013
//	1. Improve support for the new Courses entry points by allowing hiding of the courses navbar
//version 5.1.0, 26 November 2013
//	1. Implement resuming of a Sumac session properly (drop sumac_resume_or_start.php file)
//	2. Implement enforced session expiration
//	3. Allow user login without an active package
//	4. Also hide logout option when Courses navbar is hidden
//	5. Avoid saving invalid account details (which makes a user seem logged in when actually not)
//	6. Make use of HTML <meta> tag to tell browsers there is javascript in the pages
//	7. Add private <meta> description entry to identify independent windows/tabs that aren't chained to other Sumac pages
//	8. Supply an OK button (just the Back button in disguise) for directory lists
//version 5.1.0.1, 13 December 2013
//	1. Fix the old single-package startup files that emulate earlier versions - the session time must be initialised
//version 5.1.1, 13 December 2013
//	1. Suppress the display of a person's address and phone on the four payment pages when logins don't use passwords
//version 5.1.2, 17 December 2013
//	1. Allow one-off and monthly recurring donations to be combined on one page
//	2. Also allow monthly donations to use instruction and label strings separate from the one-time ones
//version 5.1.2.1, 19 December 2013
//	1. Put crucial instructions for making use of new 5.1.2 code into parameter settings guide (no code change)
//version 5.1.2.2, 08 January 2014
//	1. (as in 5.1.0.1a) Fix referer checks to support direct use of sumac_start_new_session.php as well as sumac_start.php
//	2. Recognise and handle selected-session parameters in register request when resuming a session
//version 5.1.2.3, 10 January 2014
//	1. Correct reference to constant in message when no directories are available (CE1/EE1)
//version 5.1.2.4 31 January 2014
//	1. Change default for 'display_availability' attribute in 'seating_plan' element in 'theatre' to 'false'
//version 5.1.2.5, 04 February 2014
//	1. Fix bug relating to spaces in the online foldername for the PHP code
//	2. Apply 'display_availability' attribute in 'seating_plan' element in 'theatre' to column in seat legend table
//version 5.2.0, 09 March 2014
//	1. New version of online Donations package ('donation2') incorporating new Login ('login2') and Payment ('payment2') internal components
//	2. Implement a new way of handling text strings using Javascript and enabling the user to see their IDs with a debug parameter
//	3. Add concept of 'global'/'generation2' user parameters and text strings with 'G2xxx' IDs instead of 'D2xxx' (or similar)
//	4. Separate off CSS and JS files into their own subfolders and adapt installation_verify procedure to handle them
//	5. Fix Membership package so that new user can use it by adding details (bug since 4.2.0)
//	6. Use lower case for 'sumac_str' index in SESSION because session_clean1() assumes all index names are in lowercase
//	7. Make default startup file in sumac.htm be 'sumac_start_new_session.php' to avoid confusion from resuming old sessions
//	8. Add options for 'no cents' and 'no commas' in PHP centsToPrintableDollars() utility
//	9. Add option to assign an HTML id attribute to the expiry-month and expiry-year dropdowns
//	10. Enhance the JS for restoring values entered in an HTML form to include selections and picks and checks as well as text
//	11. Fix tiny bug in start when there is no HTTP 'referer', i.e. the browser URL line was used directly
//version 5.2.0.1, 11 March 2014
//	1. Restore operation of user styling parameters
//	2. Restore Powered by Sumac footer line
//	3. Make fonts all the same by default, and remove any default blue colouring
//	4. Fix bug. Donation Details section title should be hidden when there are no fields in it
//	5. Fix bug. 'fund' setting must be defaulted when there is no user selection
//	6. Workaround bug in Ticketing. Prevent crash when rows of inward-facing seats in the same block have unequal numbers
//version 5.3.0, 23 March 2014
//	1. New online package for combining login with a named Form ('singleform2') (using 'login2')
//	2. Fix bug in Ticketing (see 5.2.0.1). Handle rows of inward-facing seats in the same block with unequal numbers
//	3. Make passwords be restored as well as entered text
//	4. Fix bug in new donations - monthly flag was restored after error, but frequency (a 'hidden' input) was not restored to match
//	5. Rename functions in donation2, payment2, login2, and geth2 packages
//	6. Allow 'class' in geth2 functions to handle more than a single class
//	7. Use distinct HTML global attribute "data-sumac-reqby" rather than a class name for checking for required inputs
//	8. Add 'donation2' as legal package name in parameter guide
//version 5.4.0, 10 April 2014
//	1. Enhancement to Ticketing package to allow a promotion code for discounts on event prices
//	[Note that the DTD for the 'theatre' has new elements and attributes - earlier versions won t like the new 'theatre']
//	2. Specify "utf-8" as the character set of the HTML pages
//version 5.4.1, 08 May 2014
//  1. Add communication preferences in OTS contact update.
//     accountdetails.dtd has a new element
//version 5.5.0, 11 May 2014
//	1. New online package for allowing user to just register their contact details ('signup2') (using 'login2')
//	2. 'login2' now includes new communications preference checkboxes in personal details entry page
//	3. As a result of 'login2' change, communications preference checkboxes are automatically supported by 'donation2' and 'singleform2' packages
//	4. New value 'w' for all the 'omitfields' parameters that allow the commprefs checkboxes to be omitted
//	5. 'donation2' and 'singleform2' packages default to omitting the commprefs; 'signup2' defaults to including them
//	6. Always include title lines in login2 Personal Information panel, but default them to spaces when the Login panel is suppressed
//	7. Allow a password in login2 Personal Information panel even when the Login panel is suppressed, but automatically suppress it fro 'donation2'
//	8. Fix bug in donations2. Only first char of frequency parameter (d2frequency) should be checked.
//	9. Fix class of checkboxlist fields in singleform2
//version 5.5.1, 14 May 2014
//	1. Ticketing promotion codes need to be case insensitive
//	2. Remove the leading asterisk from all default labels for entry fields in the Personal Details panel for adding a user
//	3. Change the 'Powered by Sumac ...' messages and links for singleform2 and signup2 pages to something more suitable
//	4. Change the Communication Preferences label in the panels for adding or updating a user to something a more chatty
//	5. Add a set of ten new user parameters, one per package plus a default one, to supply a URL for the Sumac online to exit to (in place of HTTP_REFERER)
//	6. Change the invalid URL when no HTTP_REFERER is available to 'no-available-return-url' instead of 'browser'
//version 5.5.2, 27 May 2014
//	1. Do not use the 'ENTITY %' device for including dtd files within other dtd files - seems to be broken in some recent PHP versions
//	2. Prevent double clicking of New Donation Donate button by covering the page with a 'please wait' screen (?only affects iframes?)
//	3. Remove accidental line from user/over_sumac_donation2.css that makes final OK button disappear
//version 5.5.3, 07 Jul 2014
//	1. Fix to 8 of the PHP files to allow 'sumac_start.php' as a referer since it can be one
//version 5.6.0, 12 Jun 2014 [***NOT RELEASED***]
//	1. Initial parts of online package 'grantreview2' (using 'login2' and 'form2')
//	2. Revise 'singleform2' to use 'form2'.
//	3. Add omitted <html></html> tags to type-2 packages
//	4. Include DOCTYPE and <html> in geth2_head() function and include </body> and </html> in geth2_body_footer() function
//	5. Adapt the new user string replacement logic to cope with strings occurring more than once in a page
//	6. New HTTP method for POSTing unencrypted data
//	7. When checking for missing fields, don't complain about 'mandatory' fields that aren't even visible
//version 5.6.0.1, 13 Jun 2014 [***NOT RELEASED***]
//	1. Add user parameter for file-transfer blocksize
//version 5.6.1, 15 Jul 2014
//	1. Improvements to Courses package:
//	2. Pay for multiple registrations together
//	3. Edit registration details of sessions in registration basket
//	4. Delete sessions from registration basket
//	5. Reset details to defaults in registration form
//version 5.6.2, 17 Jul 2014
//	1. Include <feature> elements in the <organisation> record to show what Sumac does and does not support
//version 5.6.3, 09 Sep 2014
//	1. New login/'create account' page for old packages
//	2. Support for optional message codes in Sumac responses so that user messages can override the Sumac ones
//	3. Avoid loading of a lot of unnecessary user string settings by loading the overrides only for the particular package
//	4. Fix to list of possible referers for sumac_form_updated
//	5. Fix class of payment2 button and classes of buttons in signup2
//	6. Improve sumac_getFormTemplateElement() and relocate it to sumac_xml.php
//	7. Fix to title button class name - 'sumac_titlebutton'
//	8. Improve logic for positioning cursor for retrying missing data entry fields
//	9. Support for local insertions (%-0, %-1, etc) in sumac_geth2 text strings
//	10. Support for different sizes of logo in 'help' button
//	note that GrantReview2 package is still not implemented
//version 5.6.3.1, 18 Sep 2014
//	1. Fix bug caused by wrong POST name for order when course-catalog order value is $0.
//	2. Fix related minor bug: when course-catalog order value is $0, should proceed to pay for course after login, not go back to catalog
//	3. Clean up two PHP 'notification' errors of uninit variables; $prices at line 174 of sumac_course_chosen, and $_POST['mincentscharge'] at line 128 of sumac_payment_made
//version 5.6.3.2, 19 Sep 2014
//	1. In a listing of a directory, suppress the display of field entries with no data.
//version 5.6.3.4, 17 Oct 2014 (incorporating an unreleased 5.6.3.3)
//	1. Show a choice of funds in the new Donation package even if there is only one fund (choice of it or nothing).
//	2. Eliminate use of iconv to convert Sumac's data from UTF-8 to ISO-8859-1 - this has been unnecessary and WRONG since version 5.4.0 release last April
//version 5.6.3.5, 28 Oct 2014
//	1. Fix bug in new forms management where the first choice in a required dropdown selection is rejected by the 'missing' check.
//version 5.6.3.6, 09 Nov 2014
//	1. Fix bug in ticketing where min requirement for promotion was never reached for block seating because test was against tickets instead of seats.
//version 5.6.4, 08 Oct 2014
//	1. Implementation of Grant Review package
//	2. Fix to detect invalid certificate key
//	3. Broadening of embedded form2 procedure to generate the HTML form, necessitating change to singleform2 use of it
//	4. Add the logged-in user-name as a global parameter, %2.
//version 5.6.4.2, 15 Oct 2014
//	1. Many improvements and corrections to the code for uploading, downloading, and deleting attachments
//	2. Remove the form-id display from the form2 visible output. Include it as a tooltip instead.
//	3. Relabel buttons for compressing and expanding a form - use Hide/Show details
//	4. Use byte offset rather than blocknumber to identify chunks of an attachment
//	5. Revise and correct the logic for retrying the submission of a form which has been returned as 'bad'
//	6. Fix silly bug in handling of variable insertions in geth2
//	7. Do NOT allow actual forms in the grantapplication elements in the accountdetails document
//	8. Only show the final-report form when an application has reached the accepted stage (not when its under review)
//	9. Make the 'singleform2' package work again - changes to forms2 had broken it
//version 5.6.4.4, 19 Oct 2014
//	1. More improvements and corrections to the code for uploading, downloading, and deleting attachments
//	2. Allow Sumac to limit the number of attachments a form may have (maxattachments attribute on template)
//	3. Present grant review home pages in table form
//	4. Include the 'form-owner' id (i.e. the grant application id) when creating attachments and requesting forms
//	5. Remove the PHP_EOL from the geth2 tag builders for TR and TD - the EOL char creates an unwanted DOM text node
//version 5.6.4.5, 06 Nov 2014
//	1. Allow Sumac to specify that attachments to a form must have unique types (reuse_attachment_types attribute on template)
//	2. Revised form hiding and compression to allow choice of both and neither
//	3. Reformat title lines of grant-application form pages as message with inserts
//	4. Allow message inserts to be treated as an array
//	5. Fix bug where empty-string insert terminate the list of inserts
//	6. Redesign implementation of JS methods to prevent too many attachments or too many of one type
//	7. CSS rationalisation and changes
//	8. Clean up HTML head values to declare UTF-8 coding and otherwise to meet HTML5 standards
//	9. Use standard geth2 methods for creating the attachment add and delete iframe windows so that users can override messages
//	10. Fix bug in attachment result iframe display: use button instead of JS in anchor href
//	11. Provide CSS to allow empty grant-application categories to have their headers hidden
//version 5.6.4.6, 06 Nov 2014
//	1. Add missing code for viewing attachments on completed forms
//version 5.6.4.7, 12 Nov 2014
//	1. Employ parallel timed process to detect completion of attachment download and hide spinning circle
//	2. Make session timeout behave more and cleanly and friendlily and include message in new login
//	3. Suppress address and phone in personal details for new (type2) logins with no user password
//	4. Restructure user strings for title line in grant application page to allow complete suppression of null fields
//	5. Add info about max attachments and reusability of types to attachments heading in form pages
//	6. Update strings for SingleForm2 to support the new forms features of attachments and scrolling
//	7. Make the 'begun' date in a grantapplication optional
//	8. Suppress the 'Add attachment' invitation when no user is yet logged in
//	9. Perform referer checking in the form2 upload/download/delete/dldone code
//	10. Add session id to session status display
//version 5.6.5, 28 Nov 2014
//	1. Use a master copy of the string settings and only load the overrides from the user file
//	2. Implement a new Membership screen design - Membership2.
//	3. Allow the client to make the OK button in the new Donation and new Membership 'payment received' screens into a Logout button (via new user parameter 'exitlogout')
//	4. Give all packages using the 'sumac_geth2_abort_page()' code the same OK/Logout choice based on the same parameter
//	5. Give all packages using the 'sumac_geth2_exit_package_page()' code the same choice (e.g. Signup2 and all users of forms2)
//	6. Fix. Check new 'x2nouserpw' parameters associated with new Login screens (NOT old 'nouserpw' one) when testing to see whether or not user details shoud be displayed
//	7. Align payment Card Number with Expiry fields
//	8. Fix bug in displaying String Ids with multiple occurrences on one page
//	9. Use new utility function unloadExtrasDocument() instead of directly unsetting $_SESSION variable
//	10. Add '$textisonlyid' to sumac_geth2_sumac_subtitle() function to allow variable text subtitles
//version 5.6.5.1, 02 Dec 2014
//	1. Fix old renewal code to get the right plan attribute for the name with the new DTD
//	2. Drop all references to unused element 'fee_title' that has been removed from the new DTD
//version 5.6.6, 07 Dec 2014
//	1. Implement new feature in new Donations to allow user to search for funds with keywords and string matching
//	2. Add new user parameter that controls whether or not selecting a fund is mandatory in a Donation
//	3. Add "new membership" as Sumac <feature> so that old renewal code can tell which attributes to use in the Sumac document
//	4. Fix handling of inserts in messages from Sumac to handle omitted inserts
//	5. Allow inclusion of package-dependent Javacript in standard HTML end-of-body code in sumac_geth2_body footer()
//	[6. Fix bug -in Sumac - introduced by trimming all attribute values in Sumac - cOTSRenewal.isRenewalOptExtra() and cOTSMembership.isOptExtra() were broken]
//version 5.6.6.1, 13 Dec 2014
//	1. Fix serious bug in new Membership code where switching from the initially selected plan to another DID NOT UPDATE THE PAYABLE AMOUNT
//	2. Restore warning message in Payment panel of new Membership package that clicking the button will authorise a charge
//	3. Add the new Membership package messages to sumac_strings.settings so user can know what to override without using &debug=stringids
//	4. Fix Javascript error that prevented run-time text replacement from working (affecting fund searching feature in new Donations)
//	5. Make user-defined currency symbol available to Javascript in new packages
//	6. Allow inserts in text replacements to be references to text replacements themselves (needed for restored warnings)
//	7. Allow run-time text replacement to work with value replacements as well as innerHTML ones (needed for restored warnings)
//	8. Allow run-time text replacement to hide global insert markers (needed for restored warnings)
//version 5.6.7, 18 Jan 2015
//	1. Implement a new Ticketing screen design - 'ticketing2'.
//	2. Allow Courses to be sorted by Instructor as well as other 3 columns
//	3. Put parsedXML display in new design '2' packages from which it had been left out (including the new login2)
//	4. Restore warning message in Payment panel of new Donation package as in 5.6.6.1 for new Membership
//	5. Recognize the difference between one-off payments and recurring payments in the warnings.
//	6. Add the new Single Form package messages to sumac_strings.settings so user can know what to override without using &debug=stringids
//	7. Fix display of matched fund keywords to avoid repetition through case insensitivity
//	8. New user parameter 'd2fundkey' to restrict fund list and suppress searching
//	9. Hide new Membership options table when there are no options at all. Add 'no options available' line when one plan has no options but others have.
//	10. Parse each line of the sumac_strings.settings files separately so that extra '|' chars don't mess the whole file up
//	11. Reduce the inserts in all geth2 methods to a single parameter which is either one value or an array
//	12. Add a new structure 'sumac_attribute_sids' to the text replacement system to allow any attribute value (e.g. title) to be a user string
//	13. Fix crash in ticketing when customer removes all seats/tickets for a single event from the basket
//	14. Add support in sumac_geth2_head() for generated CSS and forcing immediate JS load
//	15. Ensure (via new utility) that the 'master' string settings and the actual js string settings agree.
//	16. Make the 'm2' user parameter settings work for the new Membership package as well as the old one.
//	17. Fix all the footer link-to-Sumac.com text according to Jenny and Peter and use same font for all
//	18. Include sumac_geth2_sumac_software_link() and sumac_geth2_user(SUMAC_USER_BOTTOM...) in sumac_geth2_body_footer()
//	19. Make sure new package mainpages all have unique div id
//	20. Hide underscores in Grant Review status names
//	21.	Make user email get autofocus in login page
//version 5.6.8, 08 Feb 2015
//	1. Add new donation detail fields, Notes and Alias ('recognize me as'), to new Donations package
//	2. Add new login fields, Title (Mr/Mrs/etc) and Contact Source, to all logins and to Contact Update package - all omitted by default
//	3. Allow posted variables that are arrays to be restored when returning to a form
//	4. Add mechanism to allow the associated display of a hidden variable to be restored when returning to a form
//	5. Amend string replacement handler to allow the text to cover multiple lines for use in e.g. HTML title attributes
//	6. Fix utilities to include new Membership and new Ticketing in 'appearances2' set for abort pages
//	7. Allow the Hide/Show buttons and panel in geth2 to be set as initially hidden
//	8. Simplify and improve the layout of sumac.htm, including using checkboxes rather than t/f text
//	9. Rename geth2 variables $labelid and $scan to $label and $labelisonlyid to match use of $text and $textisonlyid
//	10. Use 'g2omitfields' parameter instead of having all the individual package 'omitfields' parameters to set 'w' (CommPrefs) as omitted by default
//	11. Improve the HTML in sumac_test_response.php and title it sensibly
//	12. Put parsedXML display in sumac_getFunctionCompletedHTML() from which it had been left out
//	13. Use 'adduser' as more appropriate button name in login2, rather than 'submitform'
//	14. Tiny fix in payment2 - drop unnecessary retrieval of payment-card values
//	15.	Remove spurious documentation on unused parameters r2romitfields, r2rmustfields, r2rstatesdd from parameter settings guide
//	16. Prevent autofocus in user email field from bypassing the HTTPS check
//version 5.6.8.1, 27 Feb 2015
//	1. Fix the 'newsletter' feature that stopped reporting to Sumac in November 2013 when the 'sumac_get_parameter()' function was changed
//	2. Fix bug in Grant Review. When no 'Help' URL is given for a grantapplication there should be no link to click on.
//	3. Fix bug in new Ticketing. Discount codes not working because Javascript function sumac_formatMessage() left out.
//	4. Fix bug in new Membership. Long option names that run into a second line cause text overlapping.

//none of the constant values defined in this file are visible in the user interface, except the last three groups
// ***** CHANGING THEM WILL HAVE UNPREDICTABLE CONSEQUENCES *****
//MOST of them do NOT need translating for localisation - see the last three groups for possible exceptions

//these ELEMENT, ATTRIBUTE, and VALUE constants are XML keywords.
//they have to match what is in the DTD files and what Sumac java code sends

	define("SUMAC_ELEMENT_ACCOUNTDETAILSERROR",'accountdetailserror');
	define("SUMAC_ELEMENT_ACCOUNTDETAILS",'accountdetails');
	define("SUMAC_ELEMENT_ADDRESSLINE2",'addressline2');
	define("SUMAC_ELEMENT_ADDRESS",'address');
	define("SUMAC_ELEMENT_ADDRESSLINE1",'addressline1');
	define("SUMAC_ELEMENT_ATTACHMENT",'attachment');
	define("SUMAC_ELEMENT_BLOCK",'block');
	define("SUMAC_ELEMENT_BLOCKRECEIVED",'blockreceived');
	define("SUMAC_ELEMENT_BLOCKSENT",'blocksent');
	define("SUMAC_ELEMENT_CATEGORY",'category');
	define("SUMAC_ELEMENT_CELLPHONE",'cellphone');
	define("SUMAC_ELEMENT_CHOICE",'choice');
	define("SUMAC_ELEMENT_CHUNKY_CONTACT_DETAILS",'chunkycontactdetails');
	define("SUMAC_ELEMENT_CITY",'city');
	define("SUMAC_ELEMENT_COMMUNICATION_PREFERENCE",'commpref');
	define("SUMAC_ELEMENT_CONTACT_ID",'contact_id');
	define("SUMAC_ELEMENT_CONTACT_SOURCE",'contact_source');
	define("SUMAC_ELEMENT_CONTACTSOURCEID",'contactsourceid');
	define("SUMAC_ELEMENT_COUNTRY",'country');
	define("SUMAC_ELEMENT_COURSE_CATALOG",'course_catalog');
	define("SUMAC_ELEMENT_COURSE",'course');
	define("SUMAC_ELEMENT_COURSE_GROUPING",'course_grouping');
	define("SUMAC_ELEMENT_CURRENT_MEMBERSHIP",'current_membership');
	define("SUMAC_ELEMENT_CURRENT_PLAN",'current_plan');
	define("SUMAC_ELEMENT_DEDUCTION_DAY",'deduction_day');
	define("SUMAC_ELEMENT_DELIVERY_MECHANISM",'delivery_mechanism');
	define("SUMAC_ELEMENT_DIRECTORY",'directory');
	define("SUMAC_ELEMENT_DIRECTORY_ENTRIES",'directory_entries');
	define("SUMAC_ELEMENT_E",'e'); // directory entry
	define("SUMAC_ELEMENT_ED",'ed'); // directory entry definition
	define("SUMAC_ELEMENT_EDUCATIONRECORDS",'educationrecords');
	define("SUMAC_ELEMENT_EDUCATIONDETAIL",'educationdetail');
	define("SUMAC_ELEMENT_EVENT",'event');
	define("SUMAC_ELEMENT_EXAMRESULT",'examresult');
	define("SUMAC_ELEMENT_EXTRAS",'extras');
	define("SUMAC_ELEMENT_EXTRA_CENTS",'extra_cents');
	define("SUMAC_ELEMENT_F",'f'); // directory entry field
	define("SUMAC_ELEMENT_FD",'fd'); // directory entry field definition
	define("SUMAC_ELEMENT_FEATURE",'feature');
	define("SUMAC_ELEMENT_FILLEDFORM",'filledform');
	define("SUMAC_ELEMENT_FINANCIALACCOUNT",'financialaccount');
	define("SUMAC_ELEMENT_FINANCIALDETAIL",'financialdetail');
	define("SUMAC_ELEMENT_FIRSTNAME",'firstname');
	define("SUMAC_ELEMENT_FORMATTACHMENT",'formattachment');
	define("SUMAC_ELEMENT_FORMDATA",'formdata');
	define("SUMAC_ELEMENT_FORMFIELD",'formfield');
	define("SUMAC_ELEMENT_FORMTEMPLATE",'formtemplate');
	define("SUMAC_ELEMENT_FORMSLIST",'formslist');
	define("SUMAC_ELEMENT_FUND",'fund');
	define("SUMAC_ELEMENT_GRANTAPPLICATION_FORM","grantapplication_form");
	define("SUMAC_ELEMENT_GRANTAPPLICATION","grantapplication");
	define("SUMAC_ELEMENT_HOMEPHONE",'homephone');
	define("SUMAC_ELEMENT_INFORMATION_SOURCE",'information_source');
	define("SUMAC_ELEMENT_INPUT_LABEL",'input_label');
	define("SUMAC_ELEMENT_INSTRUCTOR",'instructor');
	define("SUMAC_ELEMENT_LASTNAME",'lastname');
	define("SUMAC_ELEMENT_MESSAGECODE",'messagecode');
	define("SUMAC_ELEMENT_MESSAGE",'message');
	define("SUMAC_ELEMENT_MESSAGEINSERT",'messageinsert');
	define("SUMAC_ELEMENT_MINIMUM_CENTS",'minimum_cents');
	define("SUMAC_ELEMENT_NAME",'name');
	define("SUMAC_ELEMENT_NAMEPREFIX",'nameprefix');
	define("SUMAC_ELEMENT_OPTIONAL_EXTRA",'optional_extra');
	define("SUMAC_ELEMENT_ORGANISATION",'organisation');
	define("SUMAC_ELEMENT_PAYMENT_CARD",'payment_card');
	define("SUMAC_ELEMENT_PHONE",'phone');
	define("SUMAC_ELEMENT_PLAN",'plan');
	define("SUMAC_ELEMENT_POSTCODE",'postcode');
	define("SUMAC_ELEMENT_PRICING",'pricing');
	define("SUMAC_ELEMENT_PRODUCTION_GROUPING",'production_grouping');
	define("SUMAC_ELEMENT_PRODUCTION",'production');
	define("SUMAC_ELEMENT_PROMOTION",'promotion');
	define("SUMAC_ELEMENT_PROVINCE",'province');
	define("SUMAC_ELEMENT_REMARK",'remark');
	define("SUMAC_ELEMENT_REQUIREMENT",'requirement');
	define("SUMAC_ELEMENT_REQUIREMENT_QUERY",'requirement_query');
	define("SUMAC_ELEMENT_RESPONSE",'response');
	define("SUMAC_ELEMENT_ROW",'row');
	define("SUMAC_ELEMENT_SEAT",'seat');
	define("SUMAC_ELEMENT_SEATSALES",'seatsales');
	define("SUMAC_ELEMENT_SEATING_PLAN",'seating_plan');
	define("SUMAC_ELEMENT_SELECTOR",'selector');
	define("SUMAC_ELEMENT_SELECTED_SESSION",'selected_session');
	define("SUMAC_ELEMENT_SESSION_COST",'session_cost');
	define("SUMAC_ELEMENT_SESSION_FEE",'session_fee');
	define("SUMAC_ELEMENT_SESSION",'session');
	define("SUMAC_ELEMENT_STAGE",'stage');
	define("SUMAC_ELEMENT_THEATRE",'theatre');
	define("SUMAC_ELEMENT_TOTAL_CENTS",'total_cents');

	define("SUMAC_ATTRIBUTE_AMOUNT_REQUESTED",'amount_requested');
	define("SUMAC_ATTRIBUTE_AMOUNT_GRANTED",'amount_granted');
	define("SUMAC_ATTRIBUTE_AREA_COLOUR",'area_colour');
	define("SUMAC_ATTRIBUTE_ATTACHMENTS",'attachments');
	define("SUMAC_ATTRIBUTE_ATTENDANCE_STATUS",'attendance_status');
	define("SUMAC_ATTRIBUTE_BLOCKSIZE",'blocksize');
	define("SUMAC_ATTRIBUTE_BOOKING_STATUS",'booking_status');
	define("SUMAC_ATTRIBUTE_BOOKING_MESSAGE",'booking_message');
	define("SUMAC_ATTRIBUTE_CENTS_FEE",'cents_fee');
	define("SUMAC_ATTRIBUTE_CENTS_PRICE",'cents_price');
	define("SUMAC_ATTRIBUTE_CHOSEN",'chosen');
	define("SUMAC_ATTRIBUTE_CODE",'code');
	define("SUMAC_ATTRIBUTE_COLOUR",'colour');
	define("SUMAC_ATTRIBUTE_CONTEXT",'context');
	define("SUMAC_ATTRIBUTE_COURSE_GROUPINGS",'course_groupings');
	define("SUMAC_ATTRIBUTE_COST_CENTS",'cost_cents');
	define("SUMAC_ATTRIBUTE_COURSE_NAME",'course_name');
	define("SUMAC_ATTRIBUTE_CREDIT_CENTS",'credit_cents');
	define("SUMAC_ATTRIBUTE_DATE_ACCEPTED",'date_accepted');
	define("SUMAC_ATTRIBUTE_DATE_BEGUN",'date_begun');
	define("SUMAC_ATTRIBUTE_DATE_CLOSED",'date_closed');
	define("SUMAC_ATTRIBUTE_DATE_REGISTERED",'date_registered');
	define("SUMAC_ATTRIBUTE_DATE_REVIEWED",'date_reviewed');
	define("SUMAC_ATTRIBUTE_DATE_SUBMITTED",'date_submitted');
	define("SUMAC_ATTRIBUTE_DATE",'date');
	define("SUMAC_ATTRIBUTE_DATATYPE",'datatype');
	define("SUMAC_ATTRIBUTE_DESCRIPTION",'description');
	define("SUMAC_ATTRIBUTE_DETAIL",'detail');
	define("SUMAC_ATTRIBUTE_DEFAULT",'default');
	define("SUMAC_ATTRIBUTE_DEBIT_CENTS",'debit_cents');
	define("SUMAC_ATTRIBUTE_DISCOUNT",'discount');
	define("SUMAC_ATTRIBUTE_DISPLAYTYPE",'displaytype');
	define("SUMAC_ATTRIBUTE_DISPLAY_AVAILABILITY",'display_availability');
	define("SUMAC_ATTRIBUTE_DURATION_UNIT",'duration_unit');
	define("SUMAC_ATTRIBUTE_DURATION",'duration');
	define("SUMAC_ATTRIBUTE_EDGE",'edge');
	define("SUMAC_ATTRIBUTE_ERROR",'error');
	define("SUMAC_ATTRIBUTE_EXTRA_CENTS",'extra_cents');
	define("SUMAC_ATTRIBUTE_EXTRA_NAME",'extra_name');
	define("SUMAC_ATTRIBUTE_EXTRA_REASON",'extra_reason');
	define("SUMAC_ATTRIBUTE_EXPLANATION",'explanation');
	define("SUMAC_ATTRIBUTE_FACE",'face');
	define("SUMAC_ATTRIBUTE_FEE_CENTS",'fee_cents');
	define("SUMAC_ATTRIBUTE_FIELDID",'fieldid');
	define("SUMAC_ATTRIBUTE_FILE",'file');
	define("SUMAC_ATTRIBUTE_FILESIZE",'filesize');
	define("SUMAC_ATTRIBUTE_FINAL_CENTS",'final_cents');
	define("SUMAC_ATTRIBUTE_FORM_ID",'form_id');
	define("SUMAC_ATTRIBUTE_GRANTEE",'grantee');
	define("SUMAC_ATTRIBUTE_HAPPENING",'happening');
	define("SUMAC_ATTRIBUTE_HEIGHT",'height');
	define("SUMAC_ATTRIBUTE_HELPURL",'helpurl');
	define("SUMAC_ATTRIBUTE_ID",'id');
	define("SUMAC_ATTRIBUTE_INSTRUCTIONS",'instructions');
	define("SUMAC_ATTRIBUTE_INITIALVALUE",'initialvalue');
	define("SUMAC_ATTRIBUTE_INSTRUCTOR",'instructor');
	define("SUMAC_ATTRIBUTE_INVALID",'invalid');
	define("SUMAC_ATTRIBUTE_INITIAL_CENTS",'initial_cents');
	define("SUMAC_ATTRIBUTE_IS_PERCENTAGE",'is_percentage');
	define("SUMAC_ATTRIBUTE_KEYWORDS",'keywords');
	define("SUMAC_ATTRIBUTE_LABEL",'label');
	define("SUMAC_ATTRIBUTE_LETTER_CODE",'letter_code');
	define("SUMAC_ATTRIBUTE_LOCATED_AT",'located_at');
	define("SUMAC_ATTRIBUTE_MAX",'max');
	define("SUMAC_ATTRIBUTE_MAX_TICKETS",'max_tickets');
	define("SUMAC_ATTRIBUTE_MAXATTACHMENTS",'maxattachments');
	define("SUMAC_ATTRIBUTE_MARK",'mark');
	define("SUMAC_ATTRIBUTE_METHOD",'method');
	define("SUMAC_ATTRIBUTE_MIN",'min');
	define("SUMAC_ATTRIBUTE_MIN_TICKETS",'min_tickets');
	define("SUMAC_ATTRIBUTE_MULTIPLE",'multiple');
	define("SUMAC_ATTRIBUTE_NAME",'name');
	define("SUMAC_ATTRIBUTE_NUMBERMIN",'numbermin');
	define("SUMAC_ATTRIBUTE_NUMBERMAX",'numbermax');
	define("SUMAC_ATTRIBUTE_OCCUPIABLE",'occupiable');
	define("SUMAC_ATTRIBUTE_OFFSET",'offset');
	define("SUMAC_ATTRIBUTE_ONSALE",'onsale');
	define("SUMAC_ATTRIBUTE_OPTIONAL_COSTS",'optional_costs');
	define("SUMAC_ATTRIBUTE_PER_PERSON",'per_person');
	define("SUMAC_ATTRIBUTE_PER_ARTICLE",'per_article');
	define("SUMAC_ATTRIBUTE_PLANS",'plans');
	define("SUMAC_ATTRIBUTE_PRICED_AT",'priced_at');
	define("SUMAC_ATTRIBUTE_PROMOTIONS",'promotions');
	define("SUMAC_ATTRIBUTE_QUERIES",'queries');
	define("SUMAC_ATTRIBUTE_QUERY_LABEL",'query_label');
	define("SUMAC_ATTRIBUTE_RADIUS",'radius');
	define("SUMAC_ATTRIBUTE_REMARKS",'remarks');
	define("SUMAC_ATTRIBUTE_REPEATABLE",'repeatable');
	define("SUMAC_ATTRIBUTE_REPORT_BEGUN",'report_begun');
	define("SUMAC_ATTRIBUTE_REPORT_ID",'report_id');
	define("SUMAC_ATTRIBUTE_REQUIRED",'required');
	define("SUMAC_ATTRIBUTE_REQUIRED_COSTS",'required_costs');
	define("SUMAC_ATTRIBUTE_REUSE_ATTACHMENT_TYPES",'reuse_attachment_types');
	define("SUMAC_ATTRIBUTE_REVIEW_BEGUN",'review_begun');
	define("SUMAC_ATTRIBUTE_REVIEW_ID",'review_id');
	define("SUMAC_ATTRIBUTE_REVIEW_RANK",'review_rank');
	define("SUMAC_ATTRIBUTE_ROW_LABELING",'row_labeling');
	define("SUMAC_ATTRIBUTE_ROW_COUNT",'row_count');
	define("SUMAC_ATTRIBUTE_ROW_COLOUR",'row_colour');
	define("SUMAC_ATTRIBUTE_SEAT_NUMBERING",'seat_numbering');
	define("SUMAC_ATTRIBUTE_SELECTION",'selection');
	define("SUMAC_ATTRIBUTE_SEATS_PER_ROW",'seats_per_row');
	define("SUMAC_ATTRIBUTE_SEATS_AVAILABLE",'seats_available');
	define("SUMAC_ATTRIBUTE_SELLING_UNIT",'selling_unit');
	define("SUMAC_ATTRIBUTE_SIZE",'size');
	define("SUMAC_ATTRIBUTE_SOLD",'sold');
	define("SUMAC_ATTRIBUTE_START_DATE",'start_date');
	define("SUMAC_ATTRIBUTE_STATUS",'status');
	define("SUMAC_ATTRIBUTE_STYLE",'style');
	define("SUMAC_ATTRIBUTE_TEXT",'text');
	define("SUMAC_ATTRIBUTE_THEATRE_COLOUR",'theatre_colour');
	define("SUMAC_ATTRIBUTE_TIME",'time');
	define("SUMAC_ATTRIBUTE_TITLE",'title');
	define("SUMAC_ATTRIBUTE_TOTAL_CENTS",'total_cents');
	define("SUMAC_ATTRIBUTE_TYPE",'type');
	define("SUMAC_ATTRIBUTE_UNDER_INSTRUCTOR",'under_instructor');
	define("SUMAC_ATTRIBUTE_UNIT_OF_MEASURE",'unit_of_measure');
	define("SUMAC_ATTRIBUTE_USING_LAYOUT",'using_layout');
	define("SUMAC_ATTRIBUTE_VALUELIST",'valuelist');
	define("SUMAC_ATTRIBUTE_VERSION",'version');
	define("SUMAC_ATTRIBUTE_WEIGHT",'weight');
	define("SUMAC_ATTRIBUTE_WHEN_MODIFIED",'when_modified');
	define("SUMAC_ATTRIBUTE_WHEN_NEEDED_BY",'when_needed_by');
	define("SUMAC_ATTRIBUTE_WITH_REQUIREMENT",'with_requirement');
	define("SUMAC_ATTRIBUTE_WIDTH",'width');
	define("SUMAC_ATTRIBUTE_X",'x');
	define("SUMAC_ATTRIBUTE_Y",'y');

	define("SUMAC_VALUE_DELIVERY_MECHANISM",'delivery_mechanism');
	define("SUMAC_VALUE_FALSE",'false');
	define("SUMAC_VALUE_FORWARD",'forward');
	define("SUMAC_VALUE_INFORMATION_SOURCE",'information_source');
	define("SUMAC_VALUE_LEFT",'left');
	define("SUMAC_VALUE_NONE",'none');
	define("SUMAC_VALUE_RIGHT",'right');
	define("SUMAC_VALUE_TRUE",'true');

//these DERIVED constants are used internally like the XML attributes in the preceding section

	define("SUMAC_DERIVED_BLOCK_INDEX",'block_index');
	define("SUMAC_DERIVED_HAS_INDIVIDUAL_SEATS",'has_individual_seats');
	define("SUMAC_DERIVED_LAST_OF_SET",'last_of_set');
	define("SUMAC_DERIVED_MAX_OCCUPIABLE_SEATS",'max_occupiable_seats');
	define("SUMAC_DERIVED_PRICE",'price');
	define("SUMAC_DERIVED_ROWS",'rows');

//these REQUEST constants are keywords used in the protocal for messages sent to the Sumac transaction server

	define("SUMAC_REQUEST_KEYWORD_EVENT",'event');
	define("SUMAC_REQUEST_KEYWORD_INCLUDE",'include');
	define("SUMAC_REQUEST_KEYWORD_USERDATA",'userdata');
	define("SUMAC_REQUEST_KEYWORD_USERIPADDR",'useripaddr');
	define("SUMAC_REQUEST_KEYWORD_COURSESESSION",'coursesession');
	define("SUMAC_REQUEST_KEYWORD_EMAILLINK",'emaillink');
	define("SUMAC_REQUEST_KEYWORD_CONTACTID",'contactid');
	define("SUMAC_REQUEST_KEYWORD_EMAILUSER",'emailuser');
	define("SUMAC_REQUEST_KEYWORD_TICKETS",'tickets');
	define("SUMAC_REQUEST_KEYWORD_VERSION",'version');
	define("SUMAC_REQUEST_KEYWORD_REQUEST",'request');
	define("SUMAC_REQUEST_KEYWORD_FORM",'form');
	define("SUMAC_REQUEST_KEYWORD_COURSESESSIONNAME",'coursesessionname');
	define("SUMAC_REQUEST_KEYWORD_COURSESESSIONDATE",'coursesessiondate');
	define("SUMAC_REQUEST_KEYWORD_SELECTORS",'selectors');
	define("SUMAC_REQUEST_KEYWORD_FORMNAME",'formname');
	define("SUMAC_REQUEST_KEYWORD_FORMOWNER",'formowner');
	define("SUMAC_REQUEST_KEYWORD_ATTACHMENT",'attachment');
	define("SUMAC_REQUEST_KEYWORD_MEMBERSHIP",'membership');

	define("SUMAC_REQUEST_INCLUDE_CHUNKS",'chunks');
	define("SUMAC_REQUEST_INCLUDE_COURSECATALOG",'coursecatalog');
	define("SUMAC_REQUEST_INCLUDE_DIRECTORIES",'directories');
	define("SUMAC_REQUEST_INCLUDE_GRANTEE_GRANT_APPLICATIONS","granteegrapps");
	define("SUMAC_REQUEST_INCLUDE_REVIEWER_GRANT_APPLICATIONS","reviewergrapps");
	define("SUMAC_REQUEST_INCLUDE_GRANTFORMS","grantforms");
	define("SUMAC_REQUEST_INCLUDE_MEMBERSHIP",'membership');
	define("SUMAC_REQUEST_INCLUDE_SELECTEDSESSION",'selectedsession');
	define("SUMAC_REQUEST_INCLUDE_SINGLEFORM",'singleform');
	define("SUMAC_REQUEST_INCLUDE_THEATRE",'theatre');

	define("SUMAC_REQUEST_PARAM_ORGANISATION",'organisation');
	define("SUMAC_REQUEST_PARAM_NEWFORM",'newform');
	define("SUMAC_REQUEST_PARAM_FILLEDFORM",'filledform');
	define("SUMAC_REQUEST_PARAM_FORMUPDATE",'formupdate');
	define("SUMAC_REQUEST_PARAM_DONATION",'donation');
	define("SUMAC_REQUEST_PARAM_TICKETINGEXTRAS",'ticketingextras');
	define("SUMAC_REQUEST_PARAM_REGISTRATIONEXTRAS",'registrationextras');
	define("SUMAC_REQUEST_PARAM_PAYMENT",'payment');
	define("SUMAC_REQUEST_PARAM_SEATSALES",'seatsales');
	define("SUMAC_REQUEST_PARAM_UPDATEUSER",'updateuser');
	define("SUMAC_REQUEST_PARAM_ACCOUNTLOGIN",'accountlogin');
	define("SUMAC_REQUEST_PARAM_ADDUSER",'adduser');
	define("SUMAC_REQUEST_PARAM_EMAILSTATS",'emailstats');
	define("SUMAC_REQUEST_PARAM_RENEWAL",'renewal');
	define("SUMAC_REQUEST_PARAM_PASSWORD",'password');
	define("SUMAC_REQUEST_PARAM_DIRECTORYENTRIES",'directoryentries');
	define("SUMAC_REQUEST_PARAM_CREATE_ATTMT",'createattmt');
	define("SUMAC_REQUEST_PARAM_OPEN_ATTMT",'openattmt');
	define("SUMAC_REQUEST_PARAM_SET_ATTMT",'setattmt');
	define("SUMAC_REQUEST_PARAM_WRITE_ATTMT_BLOCK",'writeattmtblock');
	define("SUMAC_REQUEST_PARAM_READ_ATTMT_BLOCK",'readattmtblock');
	define("SUMAC_REQUEST_PARAM_MEMBERSHIPEXTRAS",'membershipextras');

//these FEATURE constants correspond to features that the Sumac backend may or may not support

	define("SUMAC_FEATURE_COURSES_MULTISESSIONPAYMENT",'multi-session selection');
	define("SUMAC_FEATURE_NEW_MEMBERSHIP",'new membership');

//these ID, CLASS, and NAME constants are labels used in the HTML DOM and many must match uses in Javascript functions

	define("SUMAC_ID_BASKET_OF_TICKETS",'sumac_basket_of_tickets');
	define("SUMAC_ID_BUTTON_ADD_PREFIX",'sumac_addbutton_');
	define("SUMAC_ID_BUTTON_ANOTHER_EVENT",'sumac_more_button');
	define("SUMAC_ID_BUTTON_BUY_MEMBERSHIP",'sumac_buy_membership_button');
	define("SUMAC_ID_BUTTON_CANCEL",'sumac_cancel_button');
	define("SUMAC_ID_BUTTON_CHECK_OUT",'sumac_checkout_button');
	define("SUMAC_ID_BUTTON_EDU_HISTORY",'sumac_button_eduhistory');
	define("SUMAC_ID_BUTTON_FIN_HISTORY",'sumac_button_finhistory');
	define("SUMAC_ID_BUTTON_FORMS_LIST",'sumac_button_formslist');
	define("SUMAC_ID_BUTTON_PREFIX",'sumac_button_');
	define("SUMAC_ID_BUTTON_SELECT",'sumac_select_button');
	define("SUMAC_ID_BUTTON_SUBTRACT_PREFIX",'sumac_subtractbutton_');
	define("SUMAC_ID_DIV_ACTIONS",'sumac_divactions');
	define("SUMAC_ID_DIV_ADDUSER",'sumac_divadduser');
	define("SUMAC_ID_DIV_BUYER",'sumac_divbuyer');
	define("SUMAC_ID_DIV_CLOSE",'sumac_divclose');
	define("SUMAC_ID_DIV_DONATION",'sumac_divdonation');
	define("SUMAC_ID_DIV_EDU_HISTORY",'sumac_div_eduhistory');
	define("SUMAC_ID_DIV_FATAL",'sumac_divfatal');
	define("SUMAC_ID_DIV_FIN_HISTORY",'sumac_div_finhistory');
	define("SUMAC_ID_DIV_FORGOTTEN",'sumac_divforgotten');
	define("SUMAC_ID_DIV_FORMS_LIST",'sumac_div_formslist');
	define("SUMAC_ID_DIV_IDENTIFY",'sumac_dividentify');
	define("SUMAC_ID_DIV_LEGEND",'sumac_divlegend');
	define("SUMAC_ID_DIV_LOGIN",'sumac_divlogin');
	define("SUMAC_ID_DIV_MEMBERSHIP",'sumac_divmembership');
	define("SUMAC_ID_DIV_ORDERBASKET",'sumac_divorderbasket');
	define("SUMAC_ID_DIV_PAYMENT",'sumac_divpayment');
	define("SUMAC_ID_DIV_PLANS",'sumac_divplans');
	define("SUMAC_ID_DIV_SEATINGPLAN",'sumac_divseatingplan');
	define("SUMAC_ID_DIV_SELECT",'sumac_divselect');
	define("SUMAC_ID_DIV_STAGE",'sumac_divstage');
	define("SUMAC_ID_DIV_SUMAC_FORM",'sumac_div_sumacform');
	define("SUMAC_ID_DIV_THANKS",'sumac_divthanks');
	define("SUMAC_ID_DIV_UPDATE",'sumac_divupdate');
	define("SUMAC_ID_DIV_USER_PREFIX",'sumac_divuser_');
	define("SUMAC_ID_FORM_ACTION",'sumac_form_action');
	define("SUMAC_ID_FORM_ADDUSER",'sumac_form_adduser');
	define("SUMAC_ID_FORM_BUY",'sumac_form_buy');
	define("SUMAC_ID_FORM_DONATE",'sumac_form_donate');
	define("SUMAC_ID_FORM_LOGIN",'sumac_form_login');
	define("SUMAC_ID_FORM_MEMBERSHIP",'sumac_form_membership');
	define("SUMAC_ID_FORM_OPENFORM",'sumac_form_openform');
	define("SUMAC_ID_FORM_PASSWORD",'sumac_form_password');
	define("SUMAC_ID_FORM_REGISTER",'sumac_form_register');
	define("SUMAC_ID_FORM_SELECT_DIRECTORY",'sumac_form_selectdirectory');
	define("SUMAC_ID_FORM_SUMAC_FORM",'sumac_form_sumacform');
	define("SUMAC_ID_FORM_UPDATE",'sumac_form_update');
	define("SUMAC_ID_INPUT_TEXT_PREFIX",'sumac_inputtext_');
	define("SUMAC_ID_INPUT_PROMOCODE",'sumac_input_promocode');
	define("SUMAC_ID_POST_COSTOFMEMBERSHIP",'sumac_post_costofmembership');
	define("SUMAC_ID_POST_MEMBERSHIPPLAN",'sumac_post_membershipplan');
	define("SUMAC_ID_REGISTRATION_OPTIONS",'sumac_registration_options_');
	define("SUMAC_ID_REGISTRATION_DEFAULTS",'sumac_registration_defaults_');
	define("SUMAC_ID_SPAN_BLOCK_AVAILABLE_PREFIX",'sumac_seats_available_block_');
	define("SUMAC_ID_SPAN_COST_AFTER_DISCOUNT",'sumac_cost_after_discount');
	define("SUMAC_ID_SPAN_INITIAL_AVAILABLE_SUFFIX",'_initial');
	define("SUMAC_ID_SPAN_PAYMENT",'sumac_span_payment');
	define("SUMAC_ID_SPAN_PROMOTION_DISCOUNT",'sumac_promotion_discount');
	define("SUMAC_ID_SPAN_SEATS_AVAILABLE_PREFIX",'sumac_seats_available_seat_');
	define("SUMAC_ID_SPAN_TOTAL_COST",'sumac_total_cost');
	define("SUMAC_ID_TABLE_BUTTONTABLE",'sumac_buttontable');
	define("SUMAC_ID_TABLE_TICKETTABLE",'sumac_tickettable');
	define("SUMAC_ID_TD_COST_AFTER_DISCOUNT",'sumac_td_cost_after_discount');
	define("SUMAC_ID_TD_DESCRIPTION",'sumac_description');
	define("SUMAC_ID_TD_PROMOTION_DISCOUNT",'sumac_td_promotion_discount');
	define("SUMAC_ID_TD_SELECT",'sumac_td_select');
	define("SUMAC_ID_TD_STATUS",'sumac_status');
	define("SUMAC_ID_TD_TOTAL",'sumac_total');
	define("SUMAC_ID_TD_TOTAL_CENTS",'sumac_total_cents');
	define("SUMAC_ID_TD_UPDATESTATUS",'sumac_updatestatus');
	define("SUMAC_ID_TR_PICKED_LIST",'sumac_tickets_in_basket');

	define("SUMAC_CLASS_ADD_SUBTRACT",'addsubtract');
	define("SUMAC_CLASS_EVENTHEADER",'sumac_eventheader');
	define("SUMAC_CLASS_NONSEAT",'nonseat');
	define("SUMAC_CLASS_PREFIX_ROWS",'rows_');
	define("SUMAC_CLASS_PREFIX_AREA",'area_');
	define("SUMAC_CLASS_PREFIX_USER",'user_');
	define("SUMAC_CLASS_PREFIX_SEAT",'seat_');
	define("SUMAC_CLASS_PREFIX_SECTION",'section_');
	define("SUMAC_CLASS_PREFIX_TICKET",'ticket_');
	define("SUMAC_CLASS_SOLD_SEAT",'sold_seat');
	define("SUMAC_CLASS_STAGE",'stage');
	define("SUMAC_CLASS_SUFFIX_INWARD_FACING",'_inface');
	define("SUMAC_CLASS_SUFFIX_EMPTY",'_empty');
	define("SUMAC_CLASS_SUFFIX_UNNUMBERED",'_block');
	define("SUMAC_CLASS_THEATRE",'theatre');

	define("SUMAC_NAME_BUTTON_ADD_PREFIX",'addbutton_');
	define("SUMAC_NAME_INPUT_PREFIX_HOW_MANY",'howmany_');

//these PACKAGE and FUNCTION constants are state constants used internally by the PHP

	define("SUMAC_PACKAGE_CONTACT_UPDATE",'contact');
	define("SUMAC_PACKAGE_COURSES",'courses');
	define("SUMAC_PACKAGE_DONATION",'donation');
	define("SUMAC_PACKAGE_MEMBERSHIP",'membership');
	define("SUMAC_PACKAGE_MEMBERSHIP2",'membership2');
	define("SUMAC_PACKAGE_TICKETING",'ticketing');
	define("SUMAC_PACKAGE_TICKETING2",'ticketing2');
	define("SUMAC_PACKAGE_DIRECTORIES",'directories');
	define("SUMAC_PACKAGE_DONATION2",'donation2');
	define("SUMAC_PACKAGE_SINGLEFORM2",'singleform2');
	define("SUMAC_PACKAGE_SIGNUP2",'signup2');
	define("SUMAC_PACKAGE_GRANTREVIEW2G","grantreview2g");
	define("SUMAC_PACKAGE_GRANTREVIEW2R","grantreview2r");

	define("SUMAC_FUNCTION_LEAVE",'leave');
	define("SUMAC_FUNCTION_LOGIN",'login');
	define("SUMAC_FUNCTION_LOGOUT",'logout');
	define("SUMAC_FUNCTION_REGISTER",'register');

//these SESSION constants are the keys for retrieving the 'user-adjustable' constants from the $_SESSION array

	define("SUMAC_SESSION_NAME",'SUMACSESS');
//note that the session_name is also defined in the start files that begin with sumac_session_clean1()
	define("SUMAC_STR",'sumac_str');
	define("SUMAC_SESSION_REPEATABLE_STR",'sumac_repeatable_str');
	define("SUMAC_SESSION_ACCOUNT_DETAILS",'sumac_account_details');
	define("SUMAC_SESSION_ACTIVE_SORT_DIRECTION",'sumac_active_sort_direction');
	define("SUMAC_SESSION_ACTIVE_COURSE_GROUPING",'sumac_active_course_grouping');
	define("SUMAC_SESSION_ACTIVE_PACKAGE",'sumac_active_package');
	define("SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING",'sumac_active_production_grouping');
	define("SUMAC_SESSION_ACTIVE_SORT_COLUMN",'sumac_active_sort_column');
	define("SUMAC_SESSION_ALLOW_EITHER_FORMAT",'sumac_allow_either_format');
	define("SUMAC_SESSION_ALLOW_PARTY_BOOKING",'sumac_allow_party_booking');
	define("SUMAC_SESSION_ALLOWHTTP",'sumac_allowhttp');
	define("SUMAC_SESSION_AMOUNT_TO_PAY",'sumac_amount_to_pay');
	define("SUMAC_SESSION_ANY_OTHER_ERROR",'sumac_any_other_error');
	define("SUMAC_SESSION_AVAILABLE_SEAT_COUNT",'sumac_available_seat_count');
	define("SUMAC_SESSION_BGCOLORS_FOR_TABLES",'sumac_bgcolors_for_tables');
	define("SUMAC_SESSION_BGCOLORS_FOR_ROWS",'sumac_bgcolors_for_rows');
	define("SUMAC_SESSION_BLOCKS_DETAILS",'sumac_blocks_details');
	define("SUMAC_SESSION_BLOCKS_LTOR",'sumac_blocks_ltor');
	define("SUMAC_SESSION_BODYCOLOUR",'sumac_bodycolour');
	define("SUMAC_SESSION_BORDERCOLORS_FOR_TABLES",'sumac_bordercolors_for_tables');
	define("SUMAC_SESSION_BUTTON_HEIGHT",'sumac_button_height');
	define("SUMAC_SESSION_BUTTON_WIDTH",'sumac_button_width');
	define("SUMAC_SESSION_CATALOG_DATE_DISPLAY_FORMAT",'sumac_catalog_date_display_format');
	define("SUMAC_SESSION_CATALOG_NAME",'sumac_catalog_name');
	define("SUMAC_SESSION_CATALOG_MISSING",'sumac_catalog_missing');
	define("SUMAC_SESSION_CATALOGURL",'sumac_catalogurl');
	define("SUMAC_SESSION_CHOSENTEXT1",'sumac_chosentext1');
	define("SUMAC_SESSION_CHOSENTEXT2",'sumac_chosentext2');
	define("SUMAC_SESSION_CHUNKY_CONTACT_DETAILS",'sumac_chunky_contact_details');
	define("SUMAC_SESSION_CHUNKY_CONTACT_DOC",'sumac_chunky_contact_doc');
	define("SUMAC_SESSION_COMBINED_DONATION_PREF",'sumac_combined_donation_pref');
	define("SUMAC_SESSION_CONNECTION_FAILED",'sumac_connection_failed');
	define("SUMAC_SESSION_CONTACT_LINK",'sumac_contact_link');
	define("SUMAC_SESSION_CONTACT_ID",'sumac_contact_id');
	define("SUMAC_SESSION_COURSE_GROUP_COUNT",'sumac_course_group_count');
	define("SUMAC_SESSION_COURSE_COUNT",'sumac_course_count');
	define("SUMAC_SESSION_COURSE_GROUPINGS",'sumac_course_groupings');
	define("SUMAC_SESSION_COURSE_GROUPING_NAMES",'sumac_course_grouping_names');
	define("SUMAC_SESSION_COURSES_LINK",'sumac_courses_link');
	define("SUMAC_SESSION_COURSES_NO_CATALOG",'sumac_courses_no_catalog');
	define("SUMAC_SESSION_COURSE_SELECTIONS",'sumac_course_selections');
	define("SUMAC_SESSION_CRADDORLOGIN",'sumac_craddorlogin');
	define("SUMAC_SESSION_CRADDCONTACT",'sumac_craddcontact');
	define("SUMAC_SESSION_CULOGINWOPW",'sumac_culoginwopw');
	define("SUMAC_SESSION_CULOGIN",'sumac_culogin');
	define("SUMAC_SESSION_DATE_DISPLAY_FORMAT",'sumac_date_display_format');
	define("SUMAC_SESSION_DEBUG",'sumac_debug');
	define("SUMAC_SESSION_DEFAULT_SORT_COLUMN",'sumac_default_sort_column');
	define("SUMAC_SESSION_DEFAULT_SORT_DIRECTION",'sumac_default_sort_direction');
	define("SUMAC_SESSION_DEFAULT_COURSE_FEE_NAME",'sumac_default_course_fee_name');
	define("SUMAC_SESSION_DETAILWIDE",'sumac_detailwide');
	define("SUMAC_SESSION_DETAILTALL",'sumac_detailtall');
	define("SUMAC_SESSION_DIRECTORIES_LINK",'sumac_directories_link');
	define("SUMAC_SESSION_DONATION_LINK",'sumac_donation_link');
	define("SUMAC_SESSION_DONATION_COMMEM_QUERY2",'sumac_donation_commem_query2');
	define("SUMAC_SESSION_DONATION_COMMEM_QUERY1",'sumac_donation_commem_query1');
	define("SUMAC_SESSION_DONATION_COMMEM_LINES",'sumac_donation_commem_lines');
	define("SUMAC_SESSION_DPADDCONTACT",'sumac_dpaddcontact');
	define("SUMAC_SESSION_DPADDORLOGIN",'sumac_dpaddorlogin');
	define("SUMAC_SESSION_EDUCATIONDETAIL_COUNT",'sumac_educationdetail_count');
	define("SUMAC_SESSION_EMAILADDRESS",'sumac_emailaddress');
	define("SUMAC_SESSION_ERROR_COUNT",'sumac_error_count');
	define("SUMAC_SESSION_ETBGCOLOUR",'sumac_etbgcolour');
	define("SUMAC_SESSION_EVENT_NAMES",'sumac_event_names');
	define("SUMAC_SESSION_EXCLUDE_THEATRE",'sumac_exclude_theatre');
	define("SUMAC_SESSION_EXCLUDE_COURSECATALOG",'sumac_exclude_coursecatalog');
	define("SUMAC_SESSION_EXCLUDE_DIRECTORIES",'sumac_exclude_directories');
	define("SUMAC_SESSION_EXCLUDE_GRANTFORMS",'sumac_exclude_grantforms');
	define("SUMAC_SESSION_EXTRAS",'sumac_extras');
	define("SUMAC_SESSION_FATAL_ERROR",'sumac_fatal_error');
	define("SUMAC_SESSION_FIELDS_HIDDEN",'sumac_fields_hidden');
	define("SUMAC_SESSION_FIELDS_MANDATORY",'sumac_fields_mandatory');
	define("SUMAC_SESSION_FINANCIALDETAIL_COUNT",'sumac_financialdetail_count');
	define("SUMAC_SESSION_FOLDER",'sumac_folder');
	define("SUMAC_SESSION_FONTS",'sumac_fonts');
	define("SUMAC_SESSION_FORMER_PASSWORD",'sumac_former_password');
	define("SUMAC_SESSION_FORMS_OPEN_CHOICE",'sumac_forms_open_choice');
	define("SUMAC_SESSION_FORM_FLAG_0",'sumac_form_flag_0');
	define("SUMAC_SESSION_FORMTEMPLATE_COUNT",'sumac_formtemplate_count');
	define("SUMAC_SESSION_FORM_FLAG_1",'sumac_form_flag_1');
	define("SUMAC_SESSION_FORM",'sumac_form');
	define("SUMAC_SESSION_FREQUENCY",'sumac_frequency');
	define("SUMAC_SESSION_H1_CRTITLE",'sumac_h1_crtitle');
	define("SUMAC_SESSION_H1_CUTITLE",'sumac_h1_cutitle');
	define("SUMAC_SESSION_H1_DPTITLE",'sumac_h1_dptitle');
	define("SUMAC_SESSION_H1_MRTITLE",'sumac_h1_mrtitle');
	define("SUMAC_SESSION_H1_TOTITLE",'sumac_h1_totitle');
	define("SUMAC_SESSION_H1_TITLE_COLOUR",'sumac_h1_title_colour');
	define("SUMAC_SESSION_H1_TITLE_MONTHLY",'sumac_h1_title_monthly');
	define("SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT",'sumac_history_date_display_format');
	define("SUMAC_SESSION_HTTPCONFIRMED",'sumac_httpconfirmed');
	define("SUMAC_SESSION_INCPAYNOTE",'sumac_incpaynote');
	define("SUMAC_SESSION_INFACING_BUTTON_WIDTH",'sumac_button_width_infacing');
	define("SUMAC_SESSION_INSTRUCTIONS_UPDATE",'sumac_instructions_update');
	define("SUMAC_SESSION_INSTRUCTIONS_MEMBERSHIP_ONLYPLAN",'sumac_instructions_membership_onlyplan');
	define("SUMAC_SESSION_INSTRUCTIONS_MEMBERSHIP",'sumac_instructions_membership');
	define("SUMAC_SESSION_INSTRUCTIONS_CANNOT_OFFER",'sumac_instructions_cannot_offer');
	define("SUMAC_SESSION_INSTRUCTIONS_COMPLETE_THE_COURSE_ORDER",'sumac_instructions_complete_the_course_order');
	define("SUMAC_SESSION_INSTRUCTIONS_COMPLETE_THE_ORDER",'sumac_instructions_complete_the_order');
	define("SUMAC_SESSION_INSTRUCTIONS_BUY",'sumac_instructions_buy');
	define("SUMAC_SESSION_INSTRUCTIONS_COURSE_ORDER",'sumac_instructions_course_order');
	define("SUMAC_SESSION_INSTRUCTIONS_EMAIL_PASSWORD",'sumac_instructions_email_password');
	define("SUMAC_SESSION_INSTRUCTIONS_DONATE",'sumac_instructions_donate');
	define("SUMAC_SESSION_INSTRUCTIONS_LOGIN",'sumac_instructions_login');
	define("SUMAC_SESSION_INSTRUCTIONS_ORDER",'sumac_instructions_order');
	define("SUMAC_SESSION_INVALID_SERVER_RESPONSE",'sumac_invalid_server_response');
	define("SUMAC_SESSION_LEAVE_LINK",'sumac_leave_link');
	define("SUMAC_SESSION_LOGGED_IN_NAME",'sumac_logged_in_name');
	define("SUMAC_SESSION_MANDFLDCOL",'sumac_mandfldcol');
	define("SUMAC_SESSION_MEMBER_LINK",'sumac_member_link');
	define("SUMAC_SESSION_MEMBERSHIP_PLAN",'sumac_membership_plan');
	define("SUMAC_SESSION_MEMBERSHIP_OPTIONS",'sumac_membership_options');
	define("SUMAC_SESSION_MRLOGINWOPW",'sumac_mrloginwopw');
	define("SUMAC_SESSION_NAVBGFILEXT",'sumac_navbgfilext');
	define("SUMAC_SESSION_NAVBGFOLDER",'sumac_navbgfolder');
	define("SUMAC_SESSION_NULLID_COUNTER",'sumac_nullid_counter');
	define("SUMAC_SESSION_OMIT_FINANCIAL_HISTORY",'sumac_omit_financial_history');
	define("SUMAC_SESSION_OMIT_FORMS_SUMMARY",'sumac_omit_forms_summary');
	define("SUMAC_SESSION_OMIT_BLOCK_SELECTOR",'sumac_omit_block_selector');
	define("SUMAC_SESSION_OMIT_PERSONAL_HISTORY",'sumac_omit_personal_history');
	define("SUMAC_SESSION_OMIT_COURSES_NAVBAR",'sumac_omit_courses_navbar');
	define("SUMAC_SESSION_OPTEXTRA_NATEXT",'sumac_optextra_natext');
	define("SUMAC_SESSION_ORGANISATION",'sumac_organisation');
	define("SUMAC_SESSION_ORGANISATION_DOC",'sumac_organisation_doc');
	define("SUMAC_SESSION_ORGANISATION_NAME",'sumac_organisation_name');
	define("SUMAC_SESSION_PACKAGE_COUNT",'sumac_package_count');
	define("SUMAC_SESSION_PARAMETER_SETTINGS",'sumac_parameter_settings');
	define("SUMAC_SESSION_PAY_BUTTON",'sumac_pay_button');
	define("SUMAC_SESSION_PAYACCOUNT",'sumac_payaccount');
	define("SUMAC_SESSION_PAYCOURSE",'sumac_paycourse');
	define("SUMAC_SESSION_PAYNOTETEXT",'sumac_paynotetext');
	define("SUMAC_SESSION_PH_SCALE",'sumac_ph_scale');
	define("SUMAC_SESSION_PORT",'sumac_port');
	define("SUMAC_SESSION_POST_LOGIN_ACTION",'sumac_post_login_action');
	define("SUMAC_SESSION_POST_LOGIN_DIV",'sumac_post_login_div');
	define("SUMAC_SESSION_PPBGCOLOUR",'sumac_ppbgcolour');
	define("SUMAC_SESSION_PRE_CURRENCY_SYMBOL",'sumac_pre_currency_symbol');
	define("SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES",'sumac_pricings_for_seat_classes');
	define("SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES",'sumac_pricings_for_section_classes');
	define("SUMAC_SESSION_PRODUCTION_GROUPINGS",'sumac_production_groupings');
	define("SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING",'sumac_production_detail_showing');
	define("SUMAC_SESSION_REQUEST_ERROR",'sumac_request_error');
	define("SUMAC_SESSION_RESULTS_SHOW_CHOICE",'sumac_results_show_choice');
	define("SUMAC_SESSION_RESULTS_FORMAT_CHOICE",'sumac_results_format_choice');
	define("SUMAC_SESSION_RETURN",'sumac_return');
	define("SUMAC_SESSION_SBAVCOLOUR",'sumac_sbavcolour');
	define("SUMAC_SESSION_SBBGCOLOUR",'sumac_sbbgcolour');
	define("SUMAC_SESSION_SCALE",'sumac_scale');
	define("SUMAC_SESSION_SEATS_AVAILABLE",'sumac_seats_available');
	define("SUMAC_SESSION_SEAT_PRICINGS",'sumac_seat_pricings');
	define("SUMAC_SESSION_SEATSALES_DOC",'sumac_seatsales_doc');
	define("SUMAC_SESSION_SEAT_GRADES",'sumac_seat_grades');
	define("SUMAC_SESSION_SELECT_AN_EVENT",'sumac_select_an_event');
	define("SUMAC_SESSION_SEPARATE_MONTHLY_INSTRUCTIONS",'sumac_separate_monthly_instructions');
	define("SUMAC_SESSION_SINGLE_PACKAGE",'sumac_single_package');
	define("SUMAC_SESSION_SOURCE",'sumac_source');
	define("SUMAC_SESSION_SUPPRESS_USER_OVERRIDE_CSS",'sumac_suppress_user_override_css');
	define("SUMAC_SESSION_SUPPORTED_FEATURES",'sumac_supported_features');
	define("SUMAC_SESSION_TEXTCOLOUR",'sumac_textcolour');
	define("SUMAC_SESSION_TEXT_REPEATS",'sumac_text_repeats');
	define("SUMAC_SESSION_TH_SCALE",'sumac_th_scale');
	define("SUMAC_SESSION_THEATRE_MISSING",'sumac_theatre_missing');
	define("SUMAC_SESSION_THEATRE_NAME",'sumac_theatre_name');
	define("SUMAC_SESSION_TICKETING_LINK",'sumac_ticketing_link');
	define("SUMAC_SESSION_TICKETING_EVENT",'sumac_ticketing_event');
	define("SUMAC_SESSION_TICKET_BASKET",'sumac_ticket_basket');
	define("SUMAC_SESSION_TICKETING_PROMOTIONS",'sumac_ticketing_promotions');
	define("SUMAC_SESSION_TIMESTAMP",'sumac_timestamp');
	define("SUMAC_SESSION_TOADDORLOGIN",'sumac_toaddorlogin');
	define("SUMAC_SESSION_TOADDCONTACT",'sumac_toaddcontact');
	define("SUMAC_SESSION_TOTAL_CENTS",'sumac_total_cents');
	define("SUMAC_SESSION_USE_PASSWORDS",'sumac_use_passwords');
	define("SUMAC_SESSION_USERDATA",'sumac_userdata');
	define("SUMAC_SESSION_WEBSITE_DATA",'sumac_website_data');

	define("SUMAC_STATUS_APPLICATION_IN_PROGRESS",'in_progress');
	define("SUMAC_STATUS_APPLICATION_UNDER_REVIEW",'under_review');
	define("SUMAC_STATUS_APPLICATION_ACCEPTED",'accepted');

	define("SUMAC_USERPAR_SELECTORHT",'sumac_up_selectorht');
	define("SUMAC_USERPAR_SELECTORBG",'sumac_up_selectorbg');
	define("SUMAC_USERPAR_SELECTEDBG",'sumac_up_selectedbg');
	define("SUMAC_USERPAR_SESSEXPIRY",'sumac_up_sessexpiry');
	define("SUMAC_USERPAR_D2FIXEDAMT",'sumac_up_d2fixedamt');
	define("SUMAC_USERPAR_D2AMOUNTS",'sumac_up_d2amounts');
	define("SUMAC_USERPAR_D2FREQUENCY",'sumac_up_d2frequency');
	define("SUMAC_USERPAR_D2NOLOGIN",'sumac_up_d2nologin');
	define("SUMAC_USERPAR_D2ASNAVLNK",'sumac_up_d2asnavlnk');
	define("SUMAC_USERPAR_D2FUNDREQD",'sumac_up_d2fundreqd');
	define("SUMAC_USERPAR_D2FUNDKEY",'sumac_up_d2fundkey');

	define("SUMAC_USERPAR_NOUSERPW",'sumac_up_nouserpw');
	define("SUMAC_USERPAR_OMITFIELDS",'sumac_up_omitfields');
	define("SUMAC_USERPAR_MUSTFIELDS",'sumac_up_mustfields');
	define("SUMAC_USERPAR_STATESDD",'sumac_up_statesdd');
	define("SUMAC_USERPAR_EXITURL",'sumac_up_exiturl');
	define("SUMAC_USERPAR_ATTTYPES",'sumac_up_atttypes');
	define("SUMAC_USERPAR_ATTMTBLOCK",'sumac_up_attmtblock');
	define("SUMAC_USERPAR_SINGLEREG",'sumac_up_singlereg');

	define("SUMAC_DOWNLOAD_IN_PROGRESS",'sumac_download_in_progress');

	define("SUMAC_USERPAR_EXITLOGOUT",'sumac_up_exitlogout');

//these USER constants identify files or folders

	define("SUMAC_USER_BOTTOM",'bottom');
	define("SUMAC_USER_FOLDER",'user');
	define("SUMAC_USER_OVER_SUMAC",'over_sumac');
	define("SUMAC_USER_TOP",'top');

//these constants are owned by other code

	define("SUMAC_XML_HEADER",'<?xml version="1.0"?>');
	define("SUMAC_SUMAC_NULL_PASSWORD",'@wk43UwUzQ6W4#wUd7%K8YbX2w5=eHMPKEZ=RGa66=5S5cvrc');

//these FIELD constants are the user identifiers for including or excluding login fields

	define("SUMAC_FIELD_ADDRESS",'a');
	define("SUMAC_FIELD_CELLPHONE",'m');//mobile
	define("SUMAC_FIELD_CONTACTSOURCE",'o');
	define("SUMAC_FIELD_CITY",'t');		//town
	define("SUMAC_FIELD_COMMPREFS",'w');//wishes?
	define("SUMAC_FIELD_COUNTRY",'c');
	define("SUMAC_FIELD_FIRSTNAME",'f');
	define("SUMAC_FIELD_MAXLENGTH_PASSWORD",'20');
	define("SUMAC_FIELD_NAMEPREFIX",'n');
	define("SUMAC_FIELD_OPTIONAL",'OPTIONAL');
	define("SUMAC_FIELD_PHONE",'p');
	define("SUMAC_FIELD_POSTCODE",'z');	//zipcode
	define("SUMAC_FIELD_PROVINCE",'s');	//state

//these INFO, WARNING, and ERROR constants contain messages/values unsuitable for user alteration @@@? might they be localisable ?

	define("SUMAC_INFO_SUMAC_LINK",'http://sumac.com');
	define("SUMAC_INFO_DONATION_SUBLINK",'/fundraising-software/');
	define("SUMAC_INFO_MEMBERSHIP_SUBLINK",'/membership-software/');
	define("SUMAC_INFO_TICKETING_SUBLINK",'/ticketing/');
	define("SUMAC_INFO_COURSES_SUBLINK",'/course-registration/');
	define("SUMAC_INFO_CONTACT_UPDATE_SUBLINK",'/contact-management/');
	define("SUMAC_INFO_GRANTREVIEW_SUBLINK",'/awarding-grants/');
	define("SUMAC_INFO_PRODUCT_SUBLINK",'/product/');
	define("SUMAC_INFO_FOOTER_DONATION_TEXT",'Powered by Sumac Fundraising Software');
	define("SUMAC_INFO_FOOTER_MEMBERSHIP_TEXT",'Powered by Sumac Membership Software');
	define("SUMAC_INFO_FOOTER_TICKETING_TEXT",'Powered by Sumac Event Ticketing Software');
	define("SUMAC_INFO_FOOTER_COURSES_TEXT",'Powered by Sumac Course Registration Software');
	define("SUMAC_INFO_FOOTER_GRANTREVIEW_TEXT",'Powered by Sumac Grant Management Software');
	define("SUMAC_INFO_FOOTER_CONTACT_UPDATE_TEXT",'Powered by Sumac Non-profit CRM Software');
	define("SUMAC_INFO_FOOTER_PRODUCT_TEXT",'Powered by Sumac Non-profit CRM Software');

	define("SUMAC_INFO_NO_NEED_TO_WAIT",'No need to wait ... this window is now closing ...');
	define("SUMAC_INFO_PLEASE_WAIT_RETURNING",'Please wait ... returning to the main website ...');

	define("SUMAC_WARNING_SELLING_UNIT",'WARNING: "selling_units" attribute missing, using "row" elements instead of "row_count" - ');
	define("SUMAC_WARNING_SEATING_PLAN",'WARNING: "row_count", "seats_per_row", and "selling_units" attributes are invalid for seating_plan - ignoring them - ');
	define("SUMAC_WARNING_SEATS_PER_ROW",'WARNING: "seats_per_row" attribute missing, using "row" elements instead of "row_count" - ');

	define("SUMAC_WARNING_CONFIRM_HTTP",'THIS CONNECTION USES HTTP AND SO IS INSECURE.\n\nWe recommend that you click cancel now, and then click Leave.\n(If possible, try starting again using HTTPS.)\n\nIf you really want to continue, click OK now.');

	define("SUMAC_ERROR_CONTACT_NOT_VALID",'accountdetails XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_CHUNKY_CONTACT_NOT_VALID",'chunkycontactdetails XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_SEATSALES_NOT_VALID",'seatsales XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_SEATSALES_DATA_NOT_XML",'seatsales data cannot be interpreted as XML. ');
	define("SUMAC_ERROR_EXTRAS_NOT_VALID",'extras XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_EXTRAS_DATA_NOT_XML",'extras data cannot be interpreted as XML. ');
	define("SUMAC_ERROR_FORMTEMPLATE_NOT_VALID",'formtemplate XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_FORMTEMPLATE_DATA_NOT_XML",'formtemplate data cannot be interpreted as XML. ');
	define("SUMAC_ERROR_DIRECTORY_ENTRIES_NOT_VALID",'directory_entries XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_DIRECTORY_ENTRIES_DATA_NOT_XML",'directory_entries data cannot be interpreted as XML. ');
	define("SUMAC_ERROR_ATTACHMMENT_NOT_VALID",'attachment XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_ATTACHMENT_DATA_NOT_XML",'attachment data cannot be interpreted as XML. ');
	define("SUMAC_ERROR_ORGANISATION_NOT_VALID",'organisation XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_ORGANISATION_DATA_NOT_XML",'organisation data cannot be interpreted as XML. ');
	define("SUMAC_ERROR_RESPONSE_NOT_VALID",'%0 response XML is not valid according to its DTD. ');
	define("SUMAC_ERROR_RESPONSE_DATA_NOT_XML",'%0 response data cannot be interpreted as XML. ');
	define("SUMAC_ERROR_NO_FINANCIAL_ACCOUNT",'We have no financial account for you. ');
	define("SUMAC_ERROR_NO_EVENTS",'The organisation has no theatre events. ');
	define("SUMAC_ERROR_NO_EXTRAS_DATA",'No cost extras data for payment processing. ');
	define("SUMAC_ERROR_NO_FILLEDFORM",'Form update response is illegal - no filledform element');
	define("SUMAC_ERROR_NO_FORMTEMPLATE_DATA",'No formtemplate data for id %0. ');
	define("SUMAC_ERROR_NO_FORMTEMPLATE",'Form update response is illegal - no formtemplate element');
	define("SUMAC_ERROR_NO_GRANT_APPLICATION_FORMS_DATA",'No grant application forms for data for id %0. ');
	define("SUMAC_ERROR_NO_INITIAL_PACKAGE_GIVEN",'The start link does not specify which Sumac package to start with. ');
	define("SUMAC_ERROR_NO_ORGANISATION_DATA",'No organisation data. ');
	define("SUMAC_ERROR_NO_OWNER_ID",'Form update without id for owner. ');
	define("SUMAC_ERROR_NO_PACKAGES_REQUESTED",'Sumac started without including support for any packages. ');
	define("SUMAC_ERROR_NO_SINGLEFORM_TEMPLATE_DATA",'No singleform template data in Organisation document. ');
	define("SUMAC_ERROR_INITIAL_PACKAGE_NOT_VALID",'The start link specifies an invalid Sumac package, %0, to start with. ');
	define("SUMAC_ERROR_INVALID_FILLEDFORM_STATUS",'Form update response is illegal - status of filledform should be \'invalid\'. ');
	define("SUMAC_ERROR_INVALID_REFERER",'Invalid referer %0. ');
	define("SUMAC_ERROR_VERIFY_REFERER",'Unable to verify referer. ');
	define("SUMAC_ERROR_NO_ACCOUNT_DETAILS",'The account login has not been performed. ');
	define("SUMAC_ERROR_NO_BUYER_ID",'Ticket purchase without id for buyer. ');
	define("SUMAC_ERROR_NO_CHUNKY_CONTACT_DETAILS",'No chunky contact details data for update processing. ');
	define("SUMAC_ERROR_NO_CONTACT_DETAILS",'No accountdetails data for payment processing. ');
	define("SUMAC_ERROR_NO_CONTACT_ID",'Payment without id for donor. ');
	define("SUMAC_ERROR_EVENT_NOT_IN_THEATRE",'Event %0 not in theatre XML. ');
	define("SUMAC_ERROR_NO_PAYMENT_CARD",'No cards acceptable for payment. ');
	define("SUMAC_ERROR_NO_SESSION_SELECTED",'No session has been selected. ');
	define("SUMAC_ERROR_NO_SOURCE_ADDRESS",'No %0 address for organisation. ');
	define("SUMAC_ERROR_REQUIRES_SSL",'This page was reached by HTTP instead of HTTPS. This process requires the security of SSL. ');
	define("SUMAC_ERROR_XML_NOT_AS_EXPECTED",'XML should be "%0" and is not. ');
	define("SUMAC_ERROR_XML_NO_FILE",'XML file %0 not found. ');
	define("SUMAC_ERROR_XML_FILE_LOAD_FAILED",'Bad load of XML file %0. ');
	define("SUMAC_ERROR_NO_TICKETS_IN_BASKET",'No tickets in order basket. ');
	define("SUMAC_ERROR_PROBLEM_POSTING_ENCRYPTED_DATA",'Problem posting encrypted data to Sumac server');
	define("SUMAC_ERROR_PROBLEM_POSTING_UNENCRYPTED_DATA",'Problem posting data to Sumac server');
	define("SUMAC_ERROR_REQUESTED_COURSE_ID_INVALID",'The prechosen course-session id %0 is not one from the catalog');
	define("SUMAC_ERROR_ERRORS_IN_THEATRE",'Theatre laid out with %0 errors. ');
	define("SUMAC_ERROR_NO_DIRECTORY",'The directory_entries XML has no directory element. ');
	define("SUMAC_ERROR_TOOMANY_DIRECTORIES",'The directory_entries XML has more than 1 directory element. ');
	define("SUMAC_ERROR_NO_ENTRYDEFINITION",'The directory_entries XML has no ed (entry definition) element. ');
	define("SUMAC_ERROR_TOOMANY_ENTRYDEFINITIONS",'The directory_entries XML has more than 1 ed (entry definition) element. ');
	define("SUMAC_ERROR_FORM_UPDATE_HAS_NO_FORMID",'There is no form ID available to submit with the form update request ');
	define("SUMAC_ERROR_NO_SESSION_TO_REMOVE",'Attempt to delete session from empty registration basket. ');
	define("SUMAC_ERROR_INVALID_SESSION_TO_REMOVE",'Attempt to delete session %0 which is not in registration basket. ');
	define("SUMAC_ERROR_UPLOAD_CONTACTID_MISSING",'Attachment cannot be added.<br />System error: missing contact id.');
	define("SUMAC_ERROR_DOWNLOAD_CONTACTID_MISSING",'Attachment cannot be viewed.<br />System error: missing contact id.');
	define("SUMAC_ERROR_SETDELETED_CONTACTID_MISSING",'Attachment cannot be deleted.<br />System error: missing contact id.');
	define("SUMAC_ERROR_UPLOAD_OWNERID_MISSING",'Attachment cannot be added.<br />System error: missing form-owner id.');
	define("SUMAC_ERROR_DOWNLOAD_OWNERID_MISSING",'Attachment cannot be viewed.<br />System error: missing form-owner id.');
	define("SUMAC_ERROR_SETDELETED_OWNERID_MISSING",'Attachment cannot be deleted.<br />System error: missing form-owner id.');
	define("SUMAC_ERROR_UPLOAD_FORMID_MISSING",'Attachment cannot be added.<br />System error: missing form id.');
	define("SUMAC_ERROR_DOWNLOAD_FORMID_MISSING",'Attachment cannot be viewed.<br />System error: missing form id.');
	define("SUMAC_ERROR_SETDELETED_FORMID_MISSING",'Attachment cannot be deleted.<br />System error: missing form id.');
	define("SUMAC_ERROR_DOWNLOAD_ATTMTID_MISSING",'Attachment cannot be viewed.<br />System error: missing attachment id.');
	define("SUMAC_ERROR_SETDELETED_ATTMTID_MISSING",'Attachment cannot be deleted.<br />System error: missing attachment id.');

//these EXPIRY_MONTH constants are for the user to pick the expiry month of a credit card

	define("SUMAC_EXPIRY_MONTH_01",'(Jan)');
	define("SUMAC_EXPIRY_MONTH_02",'(Feb)');
	define("SUMAC_EXPIRY_MONTH_03",'(Mar)');
	define("SUMAC_EXPIRY_MONTH_04",'(Apr)');
	define("SUMAC_EXPIRY_MONTH_05",'(May)');
	define("SUMAC_EXPIRY_MONTH_06",'(Jun)');
	define("SUMAC_EXPIRY_MONTH_07",'(Jul)');
	define("SUMAC_EXPIRY_MONTH_08",'(Aug)');
	define("SUMAC_EXPIRY_MONTH_09",'(Sep)');
	define("SUMAC_EXPIRY_MONTH_10",'(Oct)');
	define("SUMAC_EXPIRY_MONTH_11",'(Nov)');
	define("SUMAC_EXPIRY_MONTH_12",'(Dec)');

//these DEFAULT constants are for the default settings for some (non-string) user parameters

	define("SUMAC_DOLLAR_SYMBOL",'$');
	define("SUMAC_BLUE_COLOUR",'#258fce');
	define("SUMAC_DATE_DISPLAY_FORMAT",'yyyy-mm-dd');
	define("SUMAC_DEFAULT_COURSE_FEE_NAME",'Course fee');
	define("SUMAC_DEFAULT_SORT_DIRECTION",'a');
	define("SUMAC_DEFAULT_SORT_COLUMN",'0');
	define("SUMAC_TEXT_FORMFIELDFLAG_0",'No');
	define("SUMAC_TEXT_FORMFIELDFLAG_1",'Yes');
	define("SUMAC_MEMBERSHIP_OPTEXTRA_NATEXT",'N/A');
	define("SUMAC_DEFAULT_THEATRE_COLOUR",'White');
	define("SUMAC_ATTACHMENT_BLOCK_SIZE",'40960');
	define("SUMAC_ATTACHMENT_MAX_BLOCKSIZE",'86016');
	define("SUMAC_DEFAULT_FORMCOMPMIN",'5');

	sumac_load_strings();
//	sumac_load_new_strings();

function sumac_load_strings()
{
//this copies the old string constants into the $_SESSION global space
//using the values in the user-accessible file

	if (isset($_SESSION[SUMAC_STR]) === false)
	{
		$error_level = error_reporting();
		$new_level = error_reporting($error_level ^ E_WARNING);
		$strings = file_get_contents('sumac_strings.settings',true);
		$strings .= file_get_contents(SUMAC_USER_FOLDER . '/' . 'sumac_strings.settings',true);
		$error_level = error_reporting($error_level);

		$strings = trim($strings);
		if ($strings == '') return '';

		$lineArray = array();
		// does file have 0A or 0D line-ending delimiter? or both, and in which order?
		$newLine = strpos($strings,"\n");
		$carriageReturn = strpos($strings,"\r");
//echo 'NL='.$newLine.',CR='.$carriageReturn.',';
		if (($newLine === false) && ($carriageReturn !== false)) $lineArray = explode("\r",$strings);
		else if (($newLine !== false) && ($carriageReturn === false)) $lineArray = explode("\n",$strings);
		else if (($newLine !== false) && ($carriageReturn !== false))
		{
			if ($carriageReturn == ($newLine + 1)) $lineArray = explode("\n\r",$strings);
			else if ($newLine == ($carriageReturn + 1)) $lineArray = explode("\r\n",$strings);
		}
		else $lineArray[0] = $strings;

// an alternative, neater-looking, way of doing the same would be:
//		$lineArray = preg_split('/\r\n|\n\r|\n|\r/', $strings);

		$sa = array();
		for ($line = 0; $line < count($lineArray); $line++)
		{
			$stringArray = explode('|',$lineArray[$line]);
			for ($i = 0; $i < (count($stringArray)); $i = $i + 3)
			{
				$sakey = strtoupper(trim($stringArray[$i]));
				//if (($i+1) < count($stringArray)) $saname = trim($stringArray[$i+1]); else $saname = '';
				if (($i+2) < count($stringArray)) $savalue = $stringArray[$i+2]; else $savalue = '';
				if ($sakey == '') continue;
	//echo $sakey . '=' . $savalue . '<br />';
	//load only the OLD strings - the new strings will be handled elsewhere
	//except for the new SUMAC (SM) strings that are only to be found in this file
				if (
					(substr($sakey,0,2) == 'D1')
					|| (substr($sakey,0,2) == 'M2')
					|| (substr($sakey,0,2) == 'T2')
					|| (substr($sakey,0,2) == 'C2')
					|| (substr($sakey,0,2) == 'U2')
					|| (substr($sakey,0,2) == 'D2')
					|| (substr($sakey,0,2) == 'F2')
					|| (substr($sakey,0,2) == 'S2')
					|| (substr($sakey,0,3) == 'R2G')
					|| (substr($sakey,0,3) == 'R2R')
					|| (substr($sakey,0,2) == 'G2')
				)
				{	//ignore all those
				}
				else
				{
					if ($savalue == '') continue;
					$sa[$sakey] = $savalue;
				}
			}
		}
		$_SESSION[SUMAC_STR] = $sa;
	}
//	else echo 'session string array already loaded'. '<br />';
//foreach($_SESSION[SUMAC_STR] as $x => $y) echo '$_SESSION[SUMAC_STR][' . $x . '] = "' . $y . '"<br />';
}

function sumac_load_new_strings()
{
//*** code not used at present - functionality transferred to sumac_load_strings() because
//*** new file system for user override strings was not adopted - old system used for new packages

//this creates a javascript user-override list for the new generation packages' text strings
	if (isset($_SESSION['sumac_override_text_js']) === false)
	{
		$error_level = error_reporting();
		$new_level = error_reporting($error_level ^ E_WARNING);
		$strings = file_get_contents(SUMAC_USER_FOLDER . '/' . 'over_sumac.strings',true);
		$error_level = error_reporting($error_level);

		if (trim($strings) == '') return '';
		$stringArray = explode('|',$strings);
		$_SESSION['sumac_override_text_js'] = 'var sumac_override_sids = [';
//that starts the javascript array variable ...
		for ($i = 0; $i < (count($stringArray)+1); $i = $i + 3)
		{
			$said = trim($stringArray[$i]);
			//ignore the middle element on each line
			if (($i+2) < count($stringArray)) $sastr = $stringArray[$i+2]; else $sastr = '';
			if ($said == '') continue;
			if ($sastr == '') continue;
			$_SESSION['sumac_override_text_js'] .= '{id:"'.$said.'",str:"'.$sastr.'"},';
// ... and that adds each replacement id+str set to it ...
		}
		$_SESSION['sumac_override_text_js'] .= '];';
// ... and that closes the array off.
	}
}

?>