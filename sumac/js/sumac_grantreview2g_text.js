//version568//

// note that the str values defined here are ALL overridden by values in 'user/sumac_string.settings'
// this means that the user instructions have not - yet - had to be changed from what the old packages required

var sumac_title_sid =
{id : "R2GB1", str : "%1 grant application"};

var sumac_innerHTML_sids = [
//%a = status
//%b = grant type/name
//%c = applicant (grantee) name
//%d = date begun
//%e not used (rank)
//%f = requested amount
//%g = amount granted
//%h = date submitted
//%i not used (date reviewed)
//%j = date accepted
//%k = date closed (i.e. withdrawn, rejected, or completed)
// in_progress
{id : "R2GC1", str : "%b grant applied for by %c%f%d [%a]"},
// under_review
{id : "R2GC2", str : "%b grant applied for by %c%f%d%h [%a]"},
// rejected
{id : "R2GC3", str : "%b grant applied for by %c%f%d%h%k [%a]"},
// accepted
{id : "R2GC4", str : "%b grant applied for by %c%f%d%h%j%g [%a]"},
// completed
{id : "R2GC5", str : "%b grant applied for by %c%f%d%h%j%g%k [%a]"},
// withdrawn
{id : "R2GC6", str : "%b grant applied for by %c%f%d%h%k [%a]"},
// withdrawn before submission
{id : "R2GC7", str : "%b grant applied for by %c%f%d, not submitted%k [%a]"},

{id : "R2GC11", str : "Application<br />type", dup : true},
{id : "R2GC12", str : "Current<br />status", dup : true},
{id : "R2GC13", str : "When<br />started", dup : true},
{id : "R2GC14", str : "When<br />submitted", dup : true},
{id : "R2GC15", str : "Amount<br />requested", dup : true},
{id : "R2GC16", str : "Amount<br />granted", dup : true},
{id : "R2GC17", str : "When<br />accepted", dup : true},
{id : "R2GC18", str : "When<br />closed", dup : true},

{id : "R2GH1", str : "Applications for %1 grants made by %2"},
{id : "R2GH2", str : "Unsubmitted Applications<br />(to be finalized or withdrawn)"},
{id : "R2GH3", str : "Active Grant Applications<br />(view or submit final report)"},
{id : "R2GH4", str : "Past Grant Applications<br />(view only)"},
{id : "R2GH5", str : "Begin a new grant application"},
{id : "R2GH6", str : "New grant application"},

{id : "R2GI1", str : "", cls : "sumac_instructions"},
{id : "R2GI2", str : "", cls : "sumac_instructions"},

{id : "R2GL1", str : "Logout"},

{id : "R2GNI1", str : "When you have logged in or created a new account with us, you will be able to work on your earlier grant applications or begin a new one"},

{id : "R2GNE1", ref : "G2NE1"},
{id : "R2GNE2", ref : "G2NE2"},
{id : "R2GNE3", ref : "G2NE3"},

{id : "R2GNF1", ref : "G2NF1"},
{id : "R2GNF2", ref : "G2NF2"},
{id : "R2GNF3", ref : "G2NF3"},
{id : "R2GNF4", ref : "G2NF4"},
{id : "R2GNF5", ref : "G2NF5"},
{id : "R2GNF6", ref : "G2NF6"},
{id : "R2GNF7", ref : "G2NF7"},
{id : "R2GNF8", ref : "G2NF8"},
{id : "R2GNF9", ref : "G2NF9"},
{id : "R2GNF10", ref : "G2NF10"},
{id : "R2GNF11", ref : "G2NF11"},
{id : "R2GNF12", ref : "G2NF12"},
{id : "R2GNF13", ref : "G2NF13"},
{id : "R2GNF14", ref : "G2NF14"},
{id : "R2GNF15", ref : "G2NF15"},
{id : "R2GNF16", ref : "G2NF16"},

{id : "R2GNH9", ref : "G2NH9"},
{id : "R2GNH10", ref : "G2NH10"},

{id : "R2GNL4", ref : "G2NL4"},
{id : "R2GNL5", ref : "G2NL5"},
{id : "R2GNL6", ref : "G2NL6"},
{id : "R2GNL8", ref : "G2NL8"},
{id : "R2GNL9", ref : "G2NL9"},

{id : "R2GNU1", ref : "G2NU1"},
{id : "R2GNU2", ref : "G2NU2"},

{id : "R2GME2", ref : "G2ME2"},
{id : "R2GME3", ref : "G2ME3"},
{id : "R2GME4", ref : "G2ME4"},

{id : "R2GMF1", ref : "G2MF1", dup : true},
{id : "R2GMF2", ref : "G2MF2", dup : true},
{id : "R2GMF3", ref : "G2MF3", dup : true},

{id : "R2GMH1", ref : "G2MH1", dup : true},
{id : "R2GMH2", ref : "G2MH2", dup : true},
{id : "R2GMH3", ref : "G2MH3", dup : true},
{id : "R2GMH4", ref : "G2MH4", dup : true},
{id : "R2GMH5", ref : "G2MH5", dup : true},

{id : "R2GML1", ref : "G2ML1", dup : true},
{id : "R2GML3", ref : "G2ML3"},
{id : "R2GML4", ref : "G2ML4", dup : true},
{id : "R2GML5", ref : "G2ML5", dup : true},
{id : "R2GML6", ref : "G2ML6", dup : true},
{id : "R2GML7", ref : "G2ML7", dup : true},
{id : "R2GML8", ref : "G2ML8", dup : true},
{id : "R2GML9", ref : "G2ML9", dup : true},
{id : "R2GML10", ref : "G2ML10", dup : true},
{id : "R2GML11", ref : "G2ML11"},
{id : "R2GML12", ref : "G2ML12"},
{id : "R2GML13", ref : "G2ML13", dup : true},
{id : "R2GML14", ref : "G2ML14", dup : true},
{id : "R2GML15", ref : "G2ML15", dup : true},
{id : "R2GML16", ref : "G2ML16"},
{id : "R2GML17", ref : "G2ML17"},
{id : "R2GML18", ref : "G2ML18"},
]

var sumac_value_sids = [
{id : "R2GNL1", ref : "G2NL1", tag : "input"},
{id : "R2GNL2", ref : "G2NL2", tag : "input"},
{id : "R2GNL7", ref : "G2NL7", tag : "input"},

{id : "R2GML2", ref : "G2ML2", tag : "input"},
]

var sumac_attribute_sids = [
]