//version551//

// note that the str values defined here are initially NOT directly overridden by values
// in 'user/sumac_string.settings', though they may be indirectly overridden by package-specific values

var sumac_innerHTML_shared_sids = [
{id : "G2NE1", str : "Please supply missing login information"},
{id : "G2NE2", str : "Please enter your email address"},

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
{id : "G2NF12", str : "Email", fld : "loginemail", blk : "loginemail"},
{id : "G2NF13", str : "Password", fld : "loginpassword"},
{id : "G2NF14", str : "How should we stay in contact with you?"},

{id : "G2NH1", str : "Personal Information"},
{id : "G2NH2", str : "If this is your first time"},
{id : "G2NH3", str : "Fill out all information"},
{id : "G2NH4", str : "If you are Returning"},
{id : "G2NH5", str : "Login"},
{id : "G2NH6", str : "Personal Information"},
{id : "G2NH7", str : ""},
{id : "G2NH8", str : ""},

{id : "G2PE1", str : "Please supply missing personal or payment information"},
{id : "G2PE2", str : "Please supply missing payment information"},

{id : "G2PF1", str : "Card Number", fld : "cardnumber", blk : "cardnumber"},
{id : "G2PF2", str : "Expiration", fld : ["cardexpmonth","cardexpyear"], blk : "expiration"},
{id : "G2PF3", str : "Month"},
{id : "G2PF4", str : "Year"},
{id : "G2PF5", str : "Card Security Code", fld : "cardsecurity", blk : "cardsecurity"},
{id : "G2PF6", str : "Name on Credit Card", fld : "carduser", blk : "carduser"},

{id : "G2PH1", str : "Payment Options"},
];

var sumac_value_shared_sids = [
{id : "G2NL1", str : "Login", tag : "input"},
{id : "G2NL2", str : "Forgot password", tag : "input"},

{id : "G2PL1", str : "Donate now!", tag : "input"},
{id : "G2PL2", str : "Donate %5 now!", tag : "input"},
];
