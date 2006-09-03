// Version: 24 Mar 2004
// Corrections:
// - Replace a "vertical-align:center" by "vertical-align:middle" in getCalendarStyles()
// - Apply this correction: http://www.mattkruse.com/scripts/support/ubb/Forum10/HTML/000554.html
// - Apply this correction: http://www.mattkruse.com/scripts/support/ubb/Forum10/HTML/000934.html

// ===================================================================
// Author: Matt Kruse <matt@mattkruse.com>
// WWW: http://www.mattkruse.com/
//
// NOTICE: You may use this code for any purpose, commercial or
// private, without any further permission from the author. You may
// remove this notice from your final code if you wish, however it is
// appreciated by the author if at least my web site address is kept.
//
// You may *NOT* re-distribute this code in any way except through its
// use. That means, you can include it in your product, or your web
// site, or any other form where the code is actually being used. You
// may not put the plain javascript up on your site for download or
// include it in your javascript libraries for download. 
// If you wish to share this code with others, please just point them
// to the URL instead.
// Please DO NOT link directly to my .js files from your site. Copy
// the files to your server and use them there. Thank you.
// ===================================================================

/*===================================================================
 Author: Matt Kruse
 
 View documentation, examples, and source code at:
     http://www.JavascriptToolbox.com/

 NOTICE: You may use this code for any purpose, commercial or
 private, without any further permission from the author. You may
 remove this notice from your final code if you wish, however it is
 appreciated by the author if at least the web site address is kept.

 This code may NOT be distributed for download from script sites, 
 open source CDs or sites, or any other distribution method. If you
 wish you share this code with others, please direct them to the 
 web site above.
 
 Pleae do not link directly to the .js files on the server above. Copy
 the files to your own server for use with your site or webapp.
 ===================================================================*/
var Position = (function() {
  // Resolve a string identifier to an object
  // ========================================
  function resolveObject(s) {
    if (document.getElementById && document.getElementById(s)!=null) {
      return document.getElementById(s);
    }
    else if (document.all && document.all[s]!=null) {
      return document.all[s];
    }
    else if (document.anchors && document.anchors.length && document.anchors.length>0 && document.anchors[0].x) {
      for (var i=0; i<document.anchors.length; i++) {
        if (document.anchors[i].name==s) { 
          return document.anchors[i]
        }
      }
    }
  }
  
  var pos = {};
  // Set the position of an object
  // =============================
  pos.set = function(o,left,top) {
    if (typeof(o)=="string") {
      o = resolveObject(o);
    }
    if (o==null || !o.style) {
      return false;
    }
    o.style.position = "absolute";
    
    // If the second parameter is an object, it is assumed to be the result of getPosition()
    if (typeof(left)=="object") {
      var pos = left;
      left = pos.left;
      top = pos.top;
    }
    
    o.style.left = left + "px";
    o.style.top = top + "px";
    return true;
  };
  
  // Retrieve the position and size of an object
  // ===========================================
  pos.get = function(o) {
    var fixBrowserQuirks = true;
      // If a string is passed in instead of an object ref, resolve it
    if (typeof(o)=="string") {
      o = resolveObject(o);
    }
    
    if (o==null) {
      return null;
    }
    
    var left = 0;
    var top = 0;
    var width = 0;
    var height = 0;
    var parentNode = null;
    var offsetParent = null;
  
    
    offsetParent = o.offsetParent;
    var originalObject = o;
    var el = o; // "el" will be nodes as we walk up, "o" will be saved for offsetParent references
    while (el.parentNode!=null) {
      el = el.parentNode;
      if (el.offsetParent==null) {
      }
      else {
        var considerScroll = true;
        /*
        In Opera, if parentNode of the first object is scrollable, then offsetLeft/offsetTop already 
        take its scroll position into account. If elements further up the chain are scrollable, their 
        scroll offsets still need to be added in. And for some reason, TR nodes have a scrolltop value
        which must be ignored.
        */
        if (fixBrowserQuirks && window.opera) {
          if (el==originalObject.parentNode || el.nodeName=="TR") {
            considerScroll = false;
          }
        }
        if (considerScroll) {
          if (el.scrollTop && el.scrollTop>0) {
            top -= el.scrollTop;
          }
          if (el.scrollLeft && el.scrollLeft>0) {
            left -= el.scrollLeft;
          }
        }
      }
      // If this node is also the offsetParent, add on the offsets and reset to the new offsetParent
      if (el == offsetParent) {
        left += o.offsetLeft;
        if (el.clientLeft && el.nodeName!="TABLE") { 
          left += el.clientLeft;
        }
        top += o.offsetTop;
        if (el.clientTop && el.nodeName!="TABLE") {
          top += el.clientTop;
        }
        o = el;
        if (o.offsetParent==null) {
          if (o.offsetLeft) {
            left += o.offsetLeft;
          }
          if (o.offsetTop) {
            top += o.offsetTop;
          }
        }
        offsetParent = o.offsetParent;
      }
    }
    
  
    if (originalObject.offsetWidth) {
      width = originalObject.offsetWidth;
    }
    if (originalObject.offsetHeight) {
      height = originalObject.offsetHeight;
    }
    
    return {'left':left, 'top':top, 'width':width, 'height':height
        };
  };
  
  // Retrieve the position of an object's center point
  // =================================================
  pos.getCenter = function(o) {
    var c = this.get(o);
    if (c==null) { return null; }
    c.left = c.left + (c.width/2);
    c.top = c.top + (c.height/2);
    return c;
  };
  
  return pos;
})();


/* SOURCE FILE: PopupWindow.js */

/* 
PopupWindow.js
Author: Matt Kruse
Last modified: 02/16/04

DESCRIPTION: This object allows you to easily and quickly popup a window
in a certain place. The window can either be a DIV or a separate browser
window.

COMPATABILITY: Works with Netscape 4.x, 6.x, IE 5.x on Windows. Some small
positioning errors - usually with Window positioning - occur on the 
Macintosh platform. Due to bugs in Netscape 4.x, populating the popup 
window with <STYLE> tags may cause errors.

USAGE:
// Create an object for a WINDOW popup
var win = new PopupWindow(); 

// Create an object for a DIV window using the DIV named 'mydiv'
var win = new PopupWindow('mydiv'); 

// Set the window to automatically hide itself when the user clicks 
// anywhere else on the page except the popup
win.autoHide(); 

// Show the window relative to the anchor name passed in
win.showPopup(anchorname);

// Hide the popup
win.hidePopup();

// Set the size of the popup window (only applies to WINDOW popups
win.setSize(width,height);

// Populate the contents of the popup window that will be shown. If you 
// change the contents while it is displayed, you will need to refresh()
win.populate(string);

// set the URL of the window, rather than populating its contents
// manually
win.setUrl("http://www.site.com/");

// Refresh the contents of the popup
win.refresh();

// Specify how many pixels to the right of the anchor the popup will appear
win.offsetX = 50;

// Specify how many pixels below the anchor the popup will appear
win.offsetY = 100;

NOTES:
1) Requires the functions in AnchorPosition.js

2) Your anchor tag MUST contain both NAME and ID attributes which are the 
   same. For example:
   <A NAME="test" ID="test"> </A>

3) There must be at least a space between <A> </A> for IE5.5 to see the 
   anchor tag correctly. Do not do <A></A> with no space.

4) When a PopupWindow object is created, a handler for 'onmouseup' is
   attached to any event handler you may have already defined. Do NOT define
   an event handler for 'onmouseup' after you define a PopupWindow object or
   the autoHide() will not work correctly.
*/ 

// Set the position of the popup window based on the anchor
function PopupWindow_getXYPosition(anchorname) {
	var coordinates = Position.get(anchorname);
    this.x = coordinates.left;
    this.y = coordinates.top;
	}
// Set width/height of DIV/popup window
function PopupWindow_setSize(width,height) {
	this.width = width;
	this.height = height;
	}
// Fill the window with contents
function PopupWindow_populate(contents) {
	this.contents = contents;
	this.populated = false;
	}
// Set the URL to go to
function PopupWindow_setUrl(url) {
	this.url = url;
	}
// Set the window popup properties
function PopupWindow_setWindowProperties(props) {
	this.windowProperties = props;
	}
// Refresh the displayed contents of the popup
function PopupWindow_refresh() {
	if (this.divName != null) {
		// refresh the DIV object
		if (this.use_gebi) {
			document.getElementById(this.divName).innerHTML = this.contents;
			}
		else if (this.use_css) { 
			document.all[this.divName].innerHTML = this.contents;
			}
		else if (this.use_layers) { 
			var d = document.layers[this.divName]; 
			d.document.open();
			d.document.writeln(this.contents);
			d.document.close();
			}
		}
	else {
		if (this.popupWindow != null && !this.popupWindow.closed) {
			if (this.url!="") {
				this.popupWindow.location.href=this.url;
				}
			else {
				this.popupWindow.document.open();
				this.popupWindow.document.writeln(this.contents);
				this.popupWindow.document.close();
			}
			this.popupWindow.focus();
			}
		}
	}
// Position and show the popup, relative to an anchor object
function PopupWindow_showPopup(anchorname) {
    this.visible = true;   // MY CHANGES: keep the visible state up to date
	this.getXYPosition(anchorname);
	this.x += this.offsetX;
	this.y += this.offsetY;
	if (!this.populated && (this.contents != "")) {
		this.populated = true;
		this.refresh();
		}
	if (this.divName != null) {
		// Show the DIV object
		if (this.use_gebi) {
			document.getElementById(this.divName).style.left = this.x + "px";
			document.getElementById(this.divName).style.top = this.y + "px";
			document.getElementById(this.divName).style.display = "block";  // MY CHANGES: replaced: visibility = "visible"
			}
		else if (this.use_css) {
			document.all[this.divName].style.left = this.x;
			document.all[this.divName].style.top = this.y;
			document.all[this.divName].style.display = "block";  // MY CHANGES: replaced: visibility = "visible"
			}
		else if (this.use_layers) {
			document.layers[this.divName].left = this.x;
			document.layers[this.divName].top = this.y;
			document.layers[this.divName].display = "block";  // MY CHANGES: replaced: visibility = "visible"
			}
		}
	else {
		if (this.popupWindow == null || this.popupWindow.closed) {
			// If the popup window will go off-screen, move it so it doesn't
			if (this.x<0) { this.x=0; }
			if (this.y<0) { this.y=0; }
			if (screen && screen.availHeight) {
				if ((this.y + this.height) > screen.availHeight) {
					this.y = screen.availHeight - this.height;
					}
				}
			if (screen && screen.availWidth) {
				if ((this.x + this.width) > screen.availWidth) {
					this.x = screen.availWidth - this.width;
					}
				}
			var avoidAboutBlank = window.opera || ( document.layers && !navigator.mimeTypes['*'] ) || navigator.vendor == 'KDE' || ( document.childNodes && !document.all && !navigator.taintEnabled );
			this.popupWindow = window.open(avoidAboutBlank?"":"about:blank","window_"+anchorname,this.windowProperties+",width="+this.width+",height="+this.height+",screenX="+this.x+",left="+this.x+",screenY="+this.y+",top="+this.y+"");
			}
		this.refresh();
		}
        // MY CHANGES: moved here from the constructor
      	if (!window.listenerAttached) {
    		window.listenerAttached = true;
	    	PopupWindow_attachListener();
		}

	}
// Hide the popup
function PopupWindow_hidePopup() {
    this.visible = false;   // MY CHANGES: keep the visible state up to date
	if (this.divName != null) {
		if (this.use_gebi) {
			document.getElementById(this.divName).style.display = "none";  // MY CHANGES: replaced: visibility = "hidden"
			}
		else if (this.use_css) {
			document.all[this.divName].style.display = "none";  // MY CHANGES: replaced: visibility = "hidden"
			}
		else if (this.use_layers) {
			document.layers[this.divName].display = "none";  // MY CHANGES: replaced: visibility = "hidden"
			}
		}
	else {
		if (this.popupWindow && !this.popupWindow.closed) {
			this.popupWindow.close();
			this.popupWindow = null;
			}
		}
	}
// Pass an event and return whether or not it was the popup DIV that was clicked
function PopupWindow_isClicked(e) {
	if (this.divName != null) {
		if (this.use_layers) {
			var clickX = e.pageX;
			var clickY = e.pageY;
			var t = document.layers[this.divName];
			if ((clickX > t.left) && (clickX < t.left+t.clip.width) && (clickY > t.top) && (clickY < t.top+t.clip.height)) {
				return true;
				}
			else { return false; }
			}
		else if (document.all) { // Need to hard-code this to trap IE for error-handling
			var t = window.event.srcElement;
			while (t.parentElement != null) {
				if (t.id==this.divName) {
					return true;
					}
				t = t.parentElement;
				}
			return false;
			}
		else if (this.use_gebi && e) {
			var t = e.originalTarget;
            // MY CHANGES: hack for Safari
            if (navigator.userAgent.indexOf("Safari") != -1) {
                t = window.event.srcElement;
            }
			while (t.parentNode != null) {
				if (t.id==this.divName) {
					return true;
					}
				t = t.parentNode;
				}
			return false;
			}
		return false;
		}
	return false;
	}

// Check an onMouseDown event to see if we should hide
function PopupWindow_hideIfNotClicked(e) {
    // MY CHANGES: hide only if it's visible
	if (this.autoHideEnabled && !this.isClicked(e) && this.visible) {
		this.hidePopup();
        // MY CHANGES: addition
        document.onmouseup = window.popupWindowOldEventListener;
        window.popupWindowOldEventListener = null;
        window.listenerAttached = false;
		}
	}
// Call this to make the DIV disable automatically when mouse is clicked outside it
function PopupWindow_autoHide() {
	this.autoHideEnabled = true;
	}
// This global function checks all PopupWindow objects onmouseup to see if they should be hidden
function PopupWindow_hidePopupWindows(e) {
	for (var i=0; i<popupWindowObjects.length; i++) {
		if (popupWindowObjects[i] != null) {
			var p = popupWindowObjects[i];
			p.hideIfNotClicked(e);
			}
		}
	}
// Run this immediately to attach the event listener
function PopupWindow_attachListener() {
	if (document.layers) {
		document.captureEvents(Event.MOUSEUP);
		}
	window.popupWindowOldEventListener = document.onmouseup;
	if (window.popupWindowOldEventListener != null) {
		document.onmouseup = new Function("window.popupWindowOldEventListener(); PopupWindow_hidePopupWindows();");
		}
	else {
		document.onmouseup = PopupWindow_hidePopupWindows;
		}
	}
// CONSTRUCTOR for the PopupWindow object
// Pass it a DIV name to use a DHTML popup, otherwise will default to window popup
function PopupWindow() {
	if (!window.popupWindowIndex) { window.popupWindowIndex = 0; }
	if (!window.popupWindowObjects) { window.popupWindowObjects = new Array(); }
// MY CHANGES: moved to the end of PopupWindow_showPopup
//     if (!window.listenerAttached) {
//     	window.listenerAttached = true;
// 	    PopupWindow_attachListener();
// 	}
	this.index = popupWindowIndex++;
	popupWindowObjects[this.index] = this;
	this.divName = null;
	this.popupWindow = null;
	this.width=0;
	this.height=0;
	this.populated = false;
	this.visible = false;
	this.autoHideEnabled = false;
	
	this.contents = "";
	this.url="";
	this.windowProperties="toolbar=no,location=no,status=no,menubar=no,scrollbars=auto,resizable,alwaysRaised,dependent,titlebar=no";
	if (arguments.length>0) {
		this.type="DIV";
		this.divName = arguments[0];
		}
	else {
		this.type="WINDOW";
		}
	this.use_gebi = false;
	this.use_css = false;
	this.use_layers = false;
	if (document.getElementById) { this.use_gebi = true; }
	else if (document.all) { this.use_css = true; }
	else if (document.layers) { this.use_layers = true; }
	else { this.type = "WINDOW"; }
	this.offsetX = 0;
	this.offsetY = 0;
	// Method mappings
	this.getXYPosition = PopupWindow_getXYPosition;
	this.populate = PopupWindow_populate;
	this.setUrl = PopupWindow_setUrl;
	this.setWindowProperties = PopupWindow_setWindowProperties;
	this.refresh = PopupWindow_refresh;
	this.showPopup = PopupWindow_showPopup;
	this.hidePopup = PopupWindow_hidePopup;
	this.setSize = PopupWindow_setSize;
	this.isClicked = PopupWindow_isClicked;
	this.autoHide = PopupWindow_autoHide;
	this.hideIfNotClicked = PopupWindow_hideIfNotClicked;
	}
