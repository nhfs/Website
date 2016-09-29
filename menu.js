/*
   Deluxe Menu Data File
   Created by Deluxe Tuner v3.2
   http://deluxe-menu.com
*/

var key="161b1488exid";

// -- Deluxe Tuner Style Names
var itemStylesNames=["NHFS","NHFS dropdown","NHFS subdropdown",];
var menuStylesNames=[];
// -- End of Deluxe Tuner Style Names

//--- Common
var isHorizontal=1;
var smColumns=1;
var smOrientation=0;
var dmRTL=0;
var pressedItem=-2;
var itemCursor="default";
var itemTarget="_self";
var statusString="link";
var blankImage="menu.files/blank image filename";
var pathPrefix_img="";
var pathPrefix_link="";

//--- Dimensions
var menuWidth="";
var menuHeight="";
var smWidth="";
var smHeight="";

//--- Positioning
var absolutePos=0;
var posX="0px";
var posY="0px";
var topDX=0;
var topDY=0;
var DX=0;
var DY=0;
var subMenuAlign="left";
var subMenuVAlign="top";

//--- Font
var fontStyle=["normal 10px Garamond","normal 10px Garamond"];
var fontColor=["#000000","#000000"];
var fontDecoration=["none","none"];
var fontColorDisabled="#AAAAAA";

//--- Appearance
var menuBackColor="#FFFFFF";
var menuBackImage="";
var menuBackRepeat="repeat";
var menuBorderColor="#999999";
var menuBorderWidth=1;
var menuBorderStyle="none";

//--- Item Appearance
var itemBackColor=["#FFFFFF","#4792E6"];
var itemBackImage=["",""];
var beforeItemImage=["",""];
var afterItemImage=["",""];
var beforeItemImageW="";
var afterItemImageW="";
var beforeItemImageH="";
var afterItemImageH="";
var itemBorderWidth=0;
var itemBorderColor=["#6655FF","#665500"];
var itemBorderStyle=["none","none"];
var itemSpacing=0;
var itemPadding="0px";
var itemAlignTop="left";
var itemAlign="left";

//--- Icons
var iconTopWidth=24;
var iconTopHeight=24;
var iconWidth=16;
var iconHeight=16;
var arrowWidth=9;
var arrowHeight=9;
var arrowImageMain=["",""];
var arrowWidthSub=0;
var arrowHeightSub=0;
var arrowImageSub=["",""];

//--- Separators
var separatorImage="";
var separatorWidth="100%";
var separatorHeight="3px";
var separatorAlignment="left";
var separatorVImage="";
var separatorVWidth="3px";
var separatorVHeight="100%";
var separatorPadding="3px";

//--- Floatable Menu
var floatable=0;
var floatIterations=6;
var floatableX=1;
var floatableY=1;
var floatableDX=15;
var floatableDY=15;

//--- Movable Menu
var movable=0;
var moveWidth=12;
var moveHeight=20;
var moveColor="#AA0000";
var moveImage="";
var moveCursor="default";
var smMovable=0;
var closeBtnW=15;
var closeBtnH=15;
var closeBtn="";

//--- Transitional Effects & Filters
var transparency="100";
var transition=24;
var transOptions="";
var transDuration=300;
var transDuration2=200;
var shadowLen=3;
var shadowColor="#777777";
var shadowTop=1;

//--- CSS Support (CSS-based Menu)
var cssStyle=0;
var cssSubmenu="";
var cssItem=["",""];
var cssItemText=["",""];

//--- Advanced
var dmObjectsCheck=0;
var saveNavigationPath=1;
var showByClick=0;
var noWrap=1;
var smShowPause=200;
var smHidePause=1000;
var smSmartScroll=1;
var topSmartScroll=0;
var smHideOnClick=1;
var dm_writeAll=0;
var useIFRAME=0;
var dmSearch=0;

//--- AJAX-like Technology
var dmAJAX=0;
var dmAJAXCount=0;
var ajaxReload=0;

//--- Dynamic Menu
var dynamic=0;

//--- Popup Menu
var popupMode=0;

//--- Keystrokes Support
var keystrokes=0;
var dm_focus=1;
var dm_actKey=113;

//--- Sound
var onOverSnd="";
var onClickSnd="";

var itemStyles = [
    ["itemWidth=133px","itemBackColor=#E6A109,#FDD679","itemBorderWidth=1","itemBorderStyle=solid,","itemBorderColor=#4C290D,#4C290D","fontStyle='normal 16px Garamond',''","fontColor=#000000,"],
    ["itemBackColor=#FDD679,#DAE4F3","beforeItemImage=http://www.northhouse.org/images/Menu/padding.jpg,","afterItemImage=http://www.northhouse.org/images/Menu/padding.jpg,","beforeItemImageW=3","afterItemImageW=3","itemBorderWidth=1","itemBorderStyle=solid,","itemBorderColor=#FFFFFF,","fontStyle='normal 16px Goudy Old Style',''","fontColor=#4C290D,"],
    ["itemBackColor=#DAE4F3,#FFFFFF","itemBorderWidth=1","itemBorderStyle=solid,","itemBorderColor=#FFFFFF,","fontStyle='normal 14px Goudy Old Style',''","fontColor=#4C290D,"],
];
var menuStyles = [
];

var menuItems = [

    ["","", "http://www.northhouse.org/images/Menu/Menu - About.Yellow.jpg", "", "", "", "0", "", "", "", "", ],
        ["|welcome","http://www.northhouse.org/aboutus/welcome/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|mission","http://www.northhouse.org/aboutus/mission/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|history","http://www.northhouse.org/aboutus/history/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|directions & map","http://www.northhouse.org/aboutus/directionsandmap/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|contact us","http://www.northhouse.org/aboutus/contactus/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|staff & board","http://www.northhouse.org/aboutus/staffandboard/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|in the news","http://www.northhouse.org/aboutus/media/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|links","http://www.northhouse.org/aboutus/links/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|faq","http://www.northhouse.org/aboutus/faq/index.htm", "", "", "", "", "1", "", "", "", "", ],
    ["","", "http://www.northhouse.org/images/Menu/Menu - Courses.Yellow.jpg", "", "", "", "0", "", "", "", "", ],
        ["|by themes","", "", "", "", "", "1", "", "", "", "", ],
            ["||basketry","http://www.northhouse.org/courses/themes/basketry/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||blacksmithing & tool making","http://www.northhouse.org/courses/themes/toolmaking/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||boatbuilding","http://www.northhouse.org/courses/themes/boatbuilding/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||clothing & jewelry","http://www.northhouse.org/courses/themes/clothingandjewelry/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||fiber arts","http://www.northhouse.org/courses/themes/fiberarts/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||foods","http://www.northhouse.org/courses/themes/foods/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||knitting","http://www.northhouse.org/courses/themes/knitting/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||music & stories","http://www.northhouse.org/courses/themes/music/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||northern ecology","http://www.northhouse.org/courses/themes/northernecology/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||outdoor skills & travel","http://www.northhouse.org/courses/themes/outdoorskillsandtravel/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||painting & photography","http://www.northhouse.org/courses/themes/paintingandphotography/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||sailing","http://www.northhouse.org/courses/themes/sailing/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||shelter","http://www.northhouse.org/courses/themes/shelter/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||sustainable living","http://www.northhouse.org/courses/themes/sustainableliving/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||timber framing","http://www.northhouse.org/courses/themes/timberframing/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||traditional crafts","http://www.northhouse.org/courses/themes/traditionalcrafts/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||woodcarving","http://www.northhouse.org/courses/themes/woodcarving/index.htm", "", "", "", "", "2", "", "", "", "", ],
            ["||woodworking & furniture craft","http://www.northhouse.org/courses/themes/woodworking/index.htm", "", "", "", "", "2", "", "", "", "", ],
        ["|by date","http://www.northhouse.org/courses/courses/coursesbydate.cfm", "", "", "", "", "1", "", "", "", "", ],
        ["|for families","http://www.northhouse.org/courses/courses/coursesforkids.cfm", "", "", "", "", "1", "", "", "", "", ],
        ["|upcoming","http://www.northhouse.org/courses/upcoming/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|how to register","http://www.northhouse.org/courses/howtoregister/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|instructor profiles","http://www.northhouse.org/courses/courses/instructors.cfm", "", "", "", "", "1", "", "", "", "", ],
        ["|gift certificates","http://www.northhouse.org/courses/giftcertificates/index.htm", "", "", "", "", "1", "", "", "", "", ],
    ["","", "http://www.northhouse.org/images/Menu/Menu - Programs.Yellow.jpg", "", "", "", "0", "", "", "", "", ],
        ["|overview","http://www.northhouse.org/programs/overview/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|events","http://www.northhouse.org/programs/events/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|work study","http://www.northhouse.org/programs/workstudy/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|volunteers","http://www.northhouse.org/programs/volunteers/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|groups","http://www.northhouse.org/programs/groups/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|internships","http://www.northhouse.org/programs/internships/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|public programs","http://www.northhouse.org/programs/public%20programs.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|daily sailing","http://www.northhouse.org/programs/dailysailing/index.htm", "", "", "", "", "1", "", "", "", "", ],
    ["","", "http://www.northhouse.org/images/Menu/Menu - Campus Life.Yellow.jpg", "", "", "", "0", "", "", "", "", ],
        ["|life on campus","http://www.northhouse.org/campuslife/lifeoncampus/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|info & policies","http://www.northhouse.org/campuslife/infoandpolicies/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|lodging & business partners","http://www.northhouse.org/courses/courses/partners.cfm", "", "", "", "", "1", "", "", "", "", ],
        ["|testimonials","http://www.northhouse.org/campuslife/testimonials/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|visiting campus","http://www.northhouse.org/programs/visitingcampus/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|virtual tour","http://www.northhouse.org/campuslife/virtualtour/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|directions & map","http://www.northhouse.org/aboutus/directionsandmap/index.htm", "", "", "", "", "1", "", "", "", "", ],
    ["","", "http://www.northhouse.org/images/Menu/Menu - Get Involved.Yellow.jpg", "", "", "", "0", "", "", "", "", ],
        ["|support us now","https://raven.boreal.org/secure/northhouse/donatea.html", "", "", "", "", "1", "", "", "", "", ],
        ["|giving opportunities","http://www.northhouse.org/getinvolved/supportus/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|e-newsletter","http://www.northhouse.org/enewsletter.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|request a catalog","http://www.northhouse.org/catalog.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|conversations","http://www.northhouse.org/getinvolved/conversations/index.htm", "", "", "", "", "1", "", "", "", "", ],
        ["|blog","http://nhfs.blogspot.com/", "", "", "", "", "1", "", "", "", "", ],
        ["|press info","http://www.northhouse.org/getinvolved/pressinfo/index.htm", "", "", "", "", "1", "", "", "", "", ],
    ["","http://www.northhouse.org/schoolstore/index.cfm", "http://www.northhouse.org/images/Menu/Menu - School Store.Yellow.jpg", "http://www.northhouse.org/images/Menu/Menu - School Store.Yellow.Light.jpg", "", "_blank", "0", "", "", "", "", ],
];

dm_init();