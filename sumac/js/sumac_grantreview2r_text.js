//version567//

// note that the str values defined here are ALL overridden by values in 'user/sumac_string.settings'
// this means that the user instructions have not - yet - had to be changed from what the old packages required

var sumac_title_sid =
{id : "R2RB1", str : "Review %1 grant applications"};

var sumac_innerHTML_sids = [
//%a = status
//%b = grant type/name
//%c = applicant (grantee) name
//%d = date begun
//%e = rank assigned
//%f = requested amount
//%g = amount granted
//%h = date submitted
//%i = date reviewed
//%j = date accepted
//%k = date closed (i.e. withdrawn, rejected, or completed)
// under_review
{id : "R2RC2", str : "%b grant applied for by %c%f%d%h%i%e [%a]"},
// rejected
{id : "R2RC3", str : "%b grant applied for by %c%f%d%h%i%e%k [%a]"},
// accepted
{id : "R2RC4", str : "%b grant applied for by %c%f%d%h%i%e%j%g [%a]"},
// completed
{id : "R2RC5", str : "%b grant applied for by %c%f%d%h%i%e%j%g%k [%a]"},
// withdrawn
{id : "R2RC6", str : "%b grant applied for by %c%f%d%h%i%e%k [%a]"},

{id : "R2RC11", str : "Application<br />type", dup : true},
{id : "R2RC12", str : "Current<br />status", dup : true},
{id : "R2RC13", str : "When<br />started", dup : true},
{id : "R2RC14", str : "When<br />submitted", dup : true},
{id : "R2RC15", str : "Amount<br />requested", dup : true},
{id : "R2RC16", str : "Amount<br />granted", dup : true},
{id : "R2RC17", str : "When<br />accepted", dup : true},
{id : "R2RC18", str : "When<br />closed", dup : true},
{id : "R2RC19", str : "Applicant's<br />name", dup : true},
{id : "R2RC20", str : "When<br />reviewed", dup : true},
{id : "R2RC21", str : "Rank<br />assigned", dup : true},

{id : "R2RH1", str : "Applications for %1 grants for review by %2"},
{id : "R2RH2", str : "Active Grant Applications<br />(ready for review or in review)"},
{id : "R2RH3", str : "Previously Reviewed Grant Applications<br />(view only)"},
{id : "R2RH6", str : "New grant application"},

{id : "R2RI1", str : "", cls : "sumac_instructions"},
{id : "R2RI2", str : "", cls : "sumac_instructions"},

{id : "R2RL1", str : "Logout"},

{id : "R2RNI1", str : "This login is for grant-application reviewers"},

{id : "R2RNE1", ref : "G2NE1"},
{id : "R2RNE2", ref : "G2NE2"},

{id : "R2RNF12", ref : "G2NF12"},
{id : "R2RNF13", ref : "G2NF13"},

{id : "R2RNH10", ref : "G2NH10"},

{id : "R2RNL8", ref : "G2NL8"},

{id : "R2RME2", ref : "G2ME2"},
{id : "R2RME3", ref : "G2ME3"},
{id : "R2RME4", ref : "G2ME4"},

{id : "R2RMF1", ref : "G2MF1", dup : true},
{id : "R2RMF2", ref : "G2MF2", dup : true},
{id : "R2RMF3", ref : "G2MF3", dup : true},

{id : "R2RMH1", ref : "G2MH1", dup : true},
{id : "R2RMH2", ref : "G2MH2", dup : true},
{id : "R2RMH3", ref : "G2MH3", dup : true},
{id : "R2RMH4", ref : "G2MH4", dup : true},
{id : "R2RMH5", ref : "G2MH5", dup : true},

{id : "R2RML1", ref : "G2ML1", dup : true},
{id : "R2RML3", ref : "G2ML3"},
{id : "R2RML4", ref : "G2ML4", dup : true},
{id : "R2RML5", ref : "G2ML5", dup : true},
{id : "R2RML6", ref : "G2ML6", dup : true},
{id : "R2RML7", ref : "G2ML7", dup : true},
{id : "R2RML8", ref : "G2ML8", dup : true},
{id : "R2RML9", ref : "G2ML9", dup : true},
{id : "R2RML10", ref : "G2ML10", dup : true},
{id : "R2RML11", ref : "G2ML11"},
{id : "R2RML12", ref : "G2ML12"},
{id : "R2RML13", ref : "G2ML13", dup : true},
{id : "R2RML14", ref : "G2ML14", dup : true},
{id : "R2RML15", ref : "G2ML15", dup : true},
{id : "R2RML16", ref : "G2ML16"},
{id : "R2RML17", ref : "G2ML17"},
{id : "R2RML18", ref : "G2ML18"},
]

var sumac_value_sids = [
{id : "R2RNL1", ref : "G2NL1", tag : "input"},
{id : "R2RNL2", ref : "G2NL2", tag : "input"},

{id : "R2RML2", ref : "G2ML2", tag : "input"},
]

var sumac_attribute_sids = [
]