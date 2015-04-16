import re
# encoding: UTF-8
def get_words_from_string(s):
    return set(re.findall(re.compile('\w+'), s.lower()))

def get_words_from_file(fname):
    with open(fname, 'rb') as inf:
        return get_words_from_string(inf.read())

def all_words(needle, haystack):
    return set(needle).issubset(set(haystack))

def any_words(needle, haystack):
    return set(needle).intersection(set(haystack))

mystring = """html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"  no cache headers  end no cache headers  BEGIN: SectionID <!-- _m1SecID="000000328-004"; _m1IsActive=1; _m1PosV=1; _m1PosH=1; _m1PosX=0; _m1PosY=20; // -->  END: SectionID  CSS Stylesheet <!-- /* vBulletin 3 CSS For Style 'Default Style' (styleid: 1) */ body {  background: #e1e1e2;  color: #000000;  font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  margin: 0px 0px 0px 0px;  padding: 0px;  background-attachment: fixed; } a:link, body_alink {  color: #22229c;  text-decoration: none; } a:visited, body_avisited {  color: #22229c;  text-decoration: none; } a:hover, a:active, body_ahover {  color: #ff4400;  text-decoration: underline; } .page {  background: #ffffff;  color: #000000; } td, th, p, li {  font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; } .tborder {  background: #d1d1e1;  color: #000000;  border: 1px solid #6b82ac; } .tcat {  background: #869bbf url(http://www.ausfish.com.au/vforum/images/gradients/gradient_tcat.gif) repeat-x top left;  color: #ffffff;  font: bold 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; } .tcat a:link, .tcat_alink {  color: #ffffff;  text-decoration: none; } .tcat a:visited, .tcat_avisited {  color: #ffffff;  text-decoration: none; } .tcat a:hover, .tcat a:active, .tcat_ahover {  color: #ffff66;  text-decoration: underline; } .thead {  background: #5c7099 url(http://www.ausfish.com.au/vforum/images/gradients/gradient_thead.gif) repeat-x top left;  color: #ffffff;  font: bold 11px tahoma, verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; } .thead a:link, .thead_alink {  color: #ffffff; } .thead a:visited, .thead_avisited {  color: #ffffff; } .thead a:hover, .thead a:active, .thead_ahover {  color: #ffff00; } .tfoot {  background: #3e5c92;  color: #e0e0f6;  padding:2px; } .tfoot a:link, .tfoot_alink {  color: #e0e0f6; } .tfoot a:visited, .tfoot_avisited {  color: #e0e0f6; } .tfoot a:hover, .tfoot a:active, .tfoot_ahover {  color: #ffff66; } .alt1, .alt1Active {  background: #f5f5ff;  color: #000000; } .alt2, .alt2Active {  background: #e1e4f2;  color: #000000; } td.inlinemod {  background: #ffffcc;  color: #000000;  font-size: 12px; } .wysiwyg {  background: #f5f5ff;  color: #000000;  font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; } textarea, .bginput {  background: #f5f5ff;  color: #000000;  font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  border: 1px solid #6b82ac; } .button {  background: #ffffff;  color: #000000;  font: 11px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  border: 1px solid #6b82ac;  margin-left: 3px; } select {  background: #cfcfcf;  color: #1f1f1f;  font: 11px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  border: 1px solid #6b82ac; } option, optgroup {  font-size: 11px;  font-family: verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; } .smallfont {  font: 11px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; } .time {  color: #666686; } .navbar {  font: 11px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; } .highlight {  color: #ff0000;  font-weight: bold; } .fjsel {  background: #f7f7f7;  color: #000000; } .panel {  background: #e4e7f5 url(http://www.ausfish.com.au/vforum/images/gradients/gradient_panel.gif) repeat-x top left;  color: #000000;  padding: 10px;  border: outset 2px; } .panelsurround {  background: #d5d8e5 url(http://www.ausfish.com.au/vforum/images/gradients/gradient_panelsurround.gif) repeat-x top left;  color: #000000; } legend {  color: #22229c;  font: 11px tahoma, verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; } .vbmenu_control {  background: #738fbf;  color: #ffffff;  font: bold 11px tahoma, verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  padding: 3px 6px 3px 6px;  white-space: nowrap; } .vbmenu_control a:link, .vbmenu_control_alink {  color: #ffffff;  text-decoration: none; } .vbmenu_control a:visited, .vbmenu_control_avisited {  color: #ffffff;  text-decoration: none; } .vbmenu_control a:hover, .vbmenu_control a:active, .vbmenu_control_ahover {  color: #ffffff;  text-decoration: underline; } .vbmenu_popup {  background: #ffffff;  color: #000000;  border: 1px solid #6b82ac; } .vbmenu_option {  background: #bbc7ce;  color: #000000;  font: 11px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  white-space: nowrap;  cursor: pointer; } .vbmenu_option a:link, .vbmenu_option_alink {  color: #22229c;  text-decoration: none; } .vbmenu_option a:visited, .vbmenu_option_avisited {  color: #22229c;  text-decoration: none; } .vbmenu_option a:hover, .vbmenu_option a:active, .vbmenu_option_ahover {  color: #000000;  text-decoration: none; } .vbmenu_hilite {  background: #8a949e;  color: #ffffff;  font: 11px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  white-space: nowrap;  cursor: pointer; } .vbmenu_hilite a:link, .vbmenu_hilite_alink {  color: #ffffff;  text-decoration: none; } .vbmenu_hilite a:visited, .vbmenu_hilite_avisited {  color: #ffffff;  text-decoration: none; } .vbmenu_hilite a:hover, .vbmenu_hilite a:active, .vbmenu_hilite_ahover {  color: #ffffff;  text-decoration: none; } /* ***** styling for 'big' usernames on postbit etc. ***** */ .bigusername { font-size: 14pt; } /* ***** small padding on 'thead' elements ***** */ td.thead, div.thead { padding: 3px; } /* ***** basic styles for multi-page nav elements */ .pagenav a { text-decoration: none; } .pagenav td { padding: 2px 4px 2px 4px; } /* ***** define margin and font-size for elements inside panels ***** */ .fieldset { margin-bottom: 6px; } .fieldset, .fieldset td, .fieldset p, .fieldset li { font-size: 11px; } /* ***** don't change the following ***** */ form { display: inline; } label { cursor: default; } .normal { font-weight: normal; } .inlineimg { vertical-align: middle; } .sized {  width:100%;  } .borderline {  border-right:1px solid #6b82ac;  border-left:1px solid #6b82ac;  border-bottom:1px solid #6b82ac;  } .gfx_logo{  background: url(images/misc/_gfx-logo-bg.gif) repeat-x top left;  } .footer_x {  background: #ffffff url(images/misc/_foot-bg.gif) repeat-x top left;  padding-right:6px;  padding-left:6px;  padding-top:3px;  } .footer {  color:#000000;  font:10px verdana;  } .footer a:link {  color:#ffffff;  text-decoration:none;  } .footer a:visited {  color:#ffffff;  text-decoration:none;  } .footer a:hover, .footer a:active {  text-decoration:underline;  color:#ffff66;  } .btm_x {  background: url(images/misc/_btm-x.gif) repeat-x top left;  height:5px;  width:100%;  margin: 0;  padding: 0;  } .btm_lft {  background: url(images/misc/_btm-lft.gif) no-repeat;  height:5px;  width:5px;  margin:0;  padding: 0;  float:left;  } .btm_rht {  background: url(images/misc/_btm-rht.gif) no-repeat;  height:5px;  width:5px;  margin:0; padding:  0; float:right;  } /* Start Sub-forums-list in Columns */ .subforum {  padding:0;  margin: 0; } .subforum li {  font: 11px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  display:inline;  width:150px;  float:left;  padding-left:0.5em;  padding-right:0.5em; } .clear {  clear:both; } /* End */ .vbmenu_control2 {  background: url(images/misc/_cat-top_x.gif) repeat-x top left;  margin:0;  padding-right:5px;  padding-left:5px;  padding-top:3px;  color:#ffffff;  font: bold 11px tahoma, verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif; white-space: nowrap;  } .vbmenu_control2 a:link, .vbmenu_control_alink {  color:#ffffff;  text-decoration:none;  } .vbmenu_control2 a:visited, .vbmenu_control_avisited {  color:#ffffff;  text-decoration:none;  } .vbmenu_control2 a:hover, .vbmenu_control2 a:active, .vbmenu_control_ahover {  color:#ffff66;  text-decoration:underline;  } .cat-top_x {  background: url(images/misc/_cat-top_x.gif) repeat-x top left;  margin:0;  padding-left:1px;  padding-top:3px;  color:#ffffff;  font: bold 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;  } .cat-top_x a:link {  color:#ffffff;  text-decoration: none;  } .cat-top_x a:visited {  color:#ffffff;  text-decoration: none;  } .cat-top_x a:hover, .cat-top_x a:active {  color:#ffff66;  text-decoration:underline;  } .cat-top_xrhtcheck {  background: url(images/misc/_cat-top_x.gif) repeat-x top left; } .cat-btm_x {  background: url(images/misc/_cat-btm_x.gif) repeat-x top left;  height:7px;  width:100%;  margin: 0;  padding: 0;  } .cat-btm_lft {  background: url(images/misc/_cat-btm_lft.gif) no-repeat;  height:7px;  width:7px;  margin:0;  padding: 0;  float:left;  } .cat-btm_rht {  background: url(images/misc/_cat-btm_rht.gif) no-repeat;  height:7px;  width:7px;  margin:0; padding:  0; float:right;  } .pbl-info {  padding-left: 3px;  padding-right: 2px;  padding-top: 2px;  padding-bottom: 2px;  background-color: #ffffff;  border: 1px solid #6b82ac;  margin-bottom: 2px; } .pin-anc {  background: #ffffff;  color: #000000; } html {  scrollbar-base-color: #b2b2b2;  scrollbar-track-color: #f2f2f2;  scrollbar-face-color: #d8d8d8;  scrollbar-arrow-color: #546435; } -->  / CSS Stylesheet <!-- var SESSIONURL = "s=0a7a6732879133340b89869d05a443b0&"; var IMGDIR_MISC = "http://www.ausfish.com.au/vforum/images/misc"; var vb_disable_ajax = parseInt("1", 10); // --> Australian Fishing Community Chatboards - Fishing Forums  Added by ACEMenu Creator, NavSurf.com var ace_path = 'http://www.ausfish.com.au/'; #echo banner="" <!-- if (typeof phpAds_adg == 'undefined') {  document.write('<scr'+'ipt language="JavaScript" type="text/javascript" src="http://www.ausfish.com.au/adserver/adg.js"></scr'+'ipt>'); } //-->  ACEDisplayMenu(); Ausfish Stickers <!-- if (typeof phpAds_adg == 'undefined') {  document.write('<scr'+'ipt language="JavaScript" type="text/javascript" src="http://www.ausfish.com.au/adserver/adg.js"></scr'+'ipt>'); } //-->  // Begin Current Moon Phase HTML (c) CalculatorCat.com // CURRENT MOONvar ccm_cfg = { pth:'http://www.moonmodule.com/cs/', fn:'ccm_v1.swf', lg:'en', hs:2, tc:'FFFFFF', bg:'000000', mc:'', fw:103, fh:151.8, js:0, msp:0 }moon info // end moon phase HTML // Forecast  & Current Temp Full  details here     <!-- function log_out() {  ht = document.getElementsByTagName("html");  ht[0].style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)";  if (confirm('Are you sure you want to log out?'))  {  return true;  }  else  {  ht[0].style.filter = "";  return false;  } } //-->  tbl bdr  tbl bdr Australian Fishing Community Chatboards ::  Forum :  Home Page ::  content table  open content container  guest welcome message Welcome to the Australian Fishing Community Chatboards.  If you require any help be sure to check out the HELP section. You have to register before you can post. To start viewing messages, select the forum that you want to visit from the selection below.  / guest welcome message  breadcrumb, login, pm info  Australian Fishing Community Chatboards  login form Member Name Remember Me? Password  / login form  / breadcrumb, login, pm info  nav buttons bar Register  start vBPicGallery plugin Gallery  end vBPicGallery plugin Help Members List Calendar vBookie Arcade Search Today's Posts Mark Forums Read  / nav buttons bar  start vBPicGallery plugin Member Galleries  end vBPicGallery plugin  top Main Chat Area  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts General Fishing Chat For general fishing related chat. Please only post in here if your post is fishing related and does not suit one of the other 29 chat board categories. Mary River?  by cammy  Today 05:23 AM 9,344 102,902 Freshwater Chat Freshwater specific chat. Techniques, locations, etc. Check out Fitzy's website for a host of Freshwater Fishing information. Spin Rod and Reel for Barra  by Barraboy7  Yesterday 10:01 PM 3,032 24,400 Saltwater Chat Saltwater specific chat. Techniques, locations, etc. Checkout Nugget's website for Articles, Tips and info on Saltwater Fishing Keppel Island in a 4.6m boat?  by greyfox  Today 05:47 AM 7,175 66,541 Fly Chat Fly fishing, tying, gear, locations, etc a couple of GT's  by nuggstar  Yesterday 11:09 PM 833 6,428 Tackle & Accessories Chat Need some advice on buying new gear, looking for comments or recommendations from other anglers. How many rods have you...  by krazyfisher  Today 05:47 AM 5,159 44,020 Electronics Chat about GPS, Sounders, Radios, Digital Cameras and other fishing related electronics - Sponsored by Fishon Marine P66 Transducer users  by Hagar  Yesterday 11:37 PM 1,812 15,331 Boating Chat Need some advice on buying a new boat. Or wanting to modify your existing one. Looking for comments or recommendations from other anglers. - Sponsored by Fisher Boats Check out The Shark's...  by nigelr  Today 05:41 AM 9,239 109,185 Fishing Videos Members can share their fishing videos here. Videos can be of anything fishing related, Hints N Tips, Catches, Reports, Recipes, Tackle Chat, etc. Craazzzy!!!  by griz066  03-12-2007 02:01 PM 121 1,442 Rod Building Rod building, repairs, binding through to custom wraps and weaves. Beginners through to experienced rod builders. Workshops, advice and photos displayed. Blank choice for Saltiga 15  by Clonoid  Today 12:25 AM 276 2,644 Recipes Caught it but not sure how to cook it, or just want some advice on cleaning and preparation? You can also post your favourite recipe here for all to enjoy. Any scallop recipes?  by Whitey81  04-12-2007 01:29 PM 198 1,561 Going Fishing? - Meet & Greets Catch up on the water with other members for a fish or chat. Organise a Meet & Greet with other members. Got a spare spot on your boat, or looking for a spare spot or fishing companion? Accomadation With Boat Mooring  by sherkier  Yesterday 09:18 PM 3,278 21,202 New members New members introduce yourself here Mackay to Hervey Bay  by stesslecc60  Yesterday 08:04 PM 366 2,205 Archived Fishing Reports This section contains fishing reports prior to 23-10-2005. Please refer to the new reports sections for reports after this date. wide caloundra saturday  by outsiderskip  24-10-2005 06:12 AM 4,284 39,369  top Fishing Reports  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts Freshwater Reports Where they are biting in the Fresh - rivers and impoundments. first toga  by cammy  Today 05:53 AM 819 8,857 Saltwater Reports - Estuary, Beach, Bay & River What's happening in the salt - Estuary, Beach, Bay & River Sponsored by - Webster's Twinfisher Monster at Scarborough  by jaredluke  Today 05:57 AM 5,025 57,357 Saltwater Reports -Offshore What's happening in the salt - Offshore Sponsored by Webster's Twinfisher Off the cape  by outsiderskip  Today 05:36 AM 2,038 27,320  top Camping & Offroad Chat  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts General Camping Chat Locations, camp cooking, recipes, camping gear. Check out Derek's website for camp oven cooking ideas and info. Where are we all going/ What...  by Reel  Yesterday 11:05 PM 1,795 8,832 4WD and Off Road Chat Anything to do with 4WD/Offroad. Cruiser 200 series  by luckyone  Today 01:48 AM 1,170 13,337  top Fishing News  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts News Discussion on conservation, press releases, politics, current and future Legislation and any other matters that have the potential to affect recreational fishing Save Moreton Bay Rally  by Fafnir  Yesterday 10:42 PM 754 9,801  top Classifieds  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts Boats - Motors - Props - Boat Trailers Cruisecraft Explorer 500  by Steve@Haypoint  Today 05:05 AM 430 4,454 Electronics & Chandlery Reelax Game Poles  by mowerman  Yesterday 08:29 PM 217 1,360 Rods & Reels Qaulity Rod for sale!!!!!!!!!  by nuggstar  Yesterday 10:56 PM 317 2,443 Tackle & accessories Dyneema Braid Tournament  by Tri-Kun  04-12-2007 03:38 PM 135 1,056 Other and Non fishing related Light force  by bungie  Yesterday 07:00 PM 442 2,475 Listing forum for items under $200 Items under $200.00 can be listed here for free and will be moved to the appropriate section for you. Please read the Terms & Conditions thread for further info. Terms & Conditions - Read...  by mamu  08-10-2007 07:02 PM 1 14 Wanted small hp motors  by nuggstar  Today 12:18 AM 1,101 4,481  top Lost and/or Stolen Property  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts Lost and/or Stolen Property List your lost or stolen fishing tackle and fishing related items here, rods, reels, boats, trailers, etc. Stolen: My Barra  by nuggstar  Today 12:30 AM 208 1,583  top Ausfish Members Fishing Photos  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts Freshwater Fishing Photos Post up your favourite freshwater pics, along with info on species, bait/lure/fly, rod, reel, location, etc. Surprise Bass  by Jim_Tait  Today 04:45 AM 403 3,833 Saltwater Fishing Photos Post up your favourite saltwater pics, along with info on species, bait/lure/fly, rod, reel, location, etc. jack x 3 = awesome night  by cammy  Today 05:55 AM 1,041 11,591  top Fish Stocking Chat  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts Freshwater Fish Stocking A forum to discuss freshwater fish stocking related issues. Feel free to ask/answer questions or simply give your opinion on a patricular topic that relates to fish stocking in freshwater. Converting Estuarine Fish to...  by Baron01  03-12-2007 12:17 PM 188 1,260  top Chat Board Help  vBSponsors  / vBSponsors  /top   Forum Last Post Threads Posts Chat Board & General Internet Info & Help For assistance in using the features of the chat boards. Also post comments and suggestions regarding the chat boards, and requests for additional features. endless frustration when...  by Ausfish  Yesterday 10:19 PM 937 5,482  top Test Area  vBSponsors  / vBSponsors  /top  what's going on box  top What's Going On?  /top  Australian Fishing Community Chatboards Statistics  Threads: 67,035,  Posts: 630,949,  Members: 21,877 Welcome to our newest member, tommy crowe  end what's going on box Mark Forums Read       View Forum Leaders    Forum Contains New Posts    Forum Contains No New Posts    Forum is Closed for Posting <!-- vbphrase['doubleclick_forum_markread'] = "Double-click this icon to mark this forum and its contents as read"; init_forum_readmarker_system(); //-->  / close content container  /content area table All times are GMT +10. The time now is 06:04 AM. Contact Us -  Archive -  Home pg -  Forum -  Top vB style modified @ GFXstyles  Do not remove or your scheduled tasks will cease to function  Do not remove or your scheduled tasks will cease to function vB Ad Management by =RedTyger=  Do not remove this copyright notice  Powered by vBulletin® Version 3.6.4Copyright ©2000 - 2007, Jelsoft Enterprises Ltd.vB.Sponsors  Do not remove this copyright notice <!--  // Main vBulletin Javascript Initialization  vBulletin_init(); //-->  tbl bdr  tbl bdr <!-- if (typeof phpAds_adg == 'undefined') {  document.write('<scr'+'ipt language="JavaScript" type="text/javascript" src="http://www.ausfish.com.au/adserver/adg.js"></scr'+'ipt>'); } //-->  Added by ACEMenu Creator, NavSurf.com ACECreateMenu()  BEGIN: M1ACV Release 2.1.1  (c)2002-2006, MediaOne Network Inc. All Rights Reserved.  END: M1ACV """
search_words = get_words_from_string(mystring)
print search_words;

newstring  = ' '.join(search_words);
print newstring