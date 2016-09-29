//version568//

// note that the str values defined here are initially NOT directly overridden by values
// in 'user/sumac_string.settings', though they may be indirectly overridden by package-specific values

var sumac_innerHTML_shared_sids = [

//G2N for the login and personal details panel

{id : "G2NI1", str : "", cls : "sumac_instructions"},

{id : "G2NE1", str : "Please supply missing login information"},
{id : "G2NE2", str : "Please enter your email address"},
{id : "G2NE3", str : "Please supply missing personal information"},

{id : "G2NF1", str : "First Name", fld : "firstname", req : "*"},
{id : "G2NF2", str : "Last Name", fld : "lastname", blk : "lastname"},
{id : "G2NF3", str : "Address", fld : ["address1","address2"], req : "*"},
{id : "G2NF4", str : "City", fld : "city", req : "*"},
{id : "G2NF5", str : "State", fld : "province", req : "*"},
{id : "G2NF6", str : "Zip Code", fld : "postcode", req : "*"},
{id : "G2NF7", str : "Country", fld : "country", req : "*"},
{id : "G2NF8", str : "Phone Number", fld : "phone", req : "*"},
{id : "G2NF9", str : "Cell Phone", fld : "cellphone", req : "*"},
{id : "G2NF10", str : "Personal Email Address", fld : "email"},
{id : "G2NF11", str : "Password", fld : "newpassword"},
{id : "G2NF12", str : "*Email", fld : "loginemail", blk : "loginemail"},
{id : "G2NF13", str : "*Password", fld : "loginpassword"},
{id : "G2NF14", str : "How should we stay in contact with you?"},
{id : "G2NF15", str : "Title", fld : "nameprefix", req : "*"},
{id : "G2NF16", str : "How did you hear of us?", fld : "contactsource", req : "*"},

{id : "G2NH1", str : "Personal Information"},
{id : "G2NH2", str : "If this is your first time"},
{id : "G2NH3", str : "Fill out all information"},
{id : "G2NH4", str : "If you are Returning"},
{id : "G2NH5", str : "Login"},
{id : "G2NH6", str : "Personal Information"},
{id : "G2NH7", str : ""},
{id : "G2NH8", str : ""},
{id : "G2NH9", str : "Create Account"},
{id : "G2NH10", str : "Login"},

{id : "G2NL4", str : "Cancel"},
{id : "G2NL5", str : "Create one"},
{id : "G2NL6", str : "Just login"},
{id : "G2NL8", str : "Go back"},
{id : "G2NL9", str : "Log out"},

{id : "G2NU1", str : "Don't have an account?"},
{id : "G2NU2", str : "Already have an account?"},

//G2P for the payment panel

{id : "G2PI1", str : "", cls : "sumac_warning"},	//single payment
{id : "G2PI2", str : "", cls : "sumac_warning"},	//monthly or other periodic payment
{id : "G2PI3", str : "", cls : "sumac_warning"},	//zero cost order

{id : "G2PE1", str : "Please supply missing personal or payment information"},
{id : "G2PE2", str : "Please supply missing payment information"},

{id : "G2PF1", str : "Card Number", fld : "cardnumber", blk : "cardnumber"},
{id : "G2PF2", str : "Expiration", fld : ["cardexpmonth","cardexpyear"], blk : "expiration"},
{id : "G2PF3", str : "Month"},
{id : "G2PF4", str : "Year"},
{id : "G2PF5", str : "Card Security Code", fld : "cardsecurity", blk : "cardsecurity"},
{id : "G2PF6", str : "Name on Credit Card", fld : "carduser", blk : "carduser"},

{id : "G2PH1", str : "Payment"},
{id : "G2PH2", str : "Finish"},

//G2M for the form panel
{id : "G2ME1", str : "Please supply missing personal or form information"},
{id : "G2ME2", str : "Please supply missing form information"},
{id : "G2ME3", str : "Attachment %b for form %a cannot be viewed.<br />%c"},
{id : "G2ME4", str : "Attachment %b for form %a cannot be viewed.<br />Size of attachment unknown."},

{id : "G2MF1", str : "Attach this file:", dup : true},
{id : "G2MF2", str : "Name the attachment:", dup : true},
{id : "G2MF3", str : "Select type:", dup : true},

{id : "G2MH1", str : "Attachments", dup : true},
{id : "G2MH2", str : "form ids", dup : true},
{id : "G2MH3", str : "Attachment", dup : true},
{id : "G2MH4", str : ", limit %a", dup : true},
{id : "G2MH5", str : ", only one of each type", dup : true},

{id : "G2ML1", str : "[Add a new attachment]", dup : true},
{id : "G2ML3", str : "Cancel"},
{id : "G2ML4", str : "Transmit attachment", dup : true},
{id : "G2ML5", str : "Cancel", dup : true},
{id : "G2ML6", str : "View", dup : true},
{id : "G2ML7", str : "Delete", dup : true},
{id : "G2ML8", str : "Show details", dup : true},
{id : "G2ML9", str : "Hide details", dup : true},
{id : "G2ML10", str : "Show field", dup : true},
{id : "G2ML11", str : "OK"},
{id : "G2ML12", str : "Update to form %a canceled. No changes submitted."},
{id : "G2ML13", str : "Go back", dup : true},
{id : "G2ML14", str : "Remove scrolling", dup : true},
{id : "G2ML15", str : "Scroll form", dup : true},
{id : "G2ML16", str : "Close this"},
{id : "G2ML17", str : "Attachment %a (id %d)<br />of type %b and size %c bytes<br />deleted from form (id %e)."},
{id : "G2ML18", str : "Attachment %a (id %d)<br />of type %b and size %c bytes<br />attached to form (id %e)."},
{id : "G2ML19", str : "Log out"},
];

var sumac_value_shared_sids = [
{id : "G2NL1", str : "Login", tag : "input"},
{id : "G2NL2", str : "Forgot password", tag : "input"},
{id : "G2NL3", str : "OK", tag : "input"},
{id : "G2NL7", str : "Create!", tag : "input"},

{id : "G2PL1", str : "Buy!", tag : "input"},
{id : "G2PL2", str : "Pay %5 now!", tag : "input"},
{id : "G2PL3", str : "Pay %a now!", tag : "input"},
{id : "G2PL4", str : "Submit order!", tag : "input"},

{id : "G2ML2", str : "OK", tag : "input"},
];

var sumac_attribute_shared_sids = [
]