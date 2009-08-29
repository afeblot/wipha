/*
Copyright Alexandre Feblot, 2005-2009
http://globs.org

This software is a computer program whose purpose is to let people share
their iPhoto Library on the web, and let their users easily download a
bundle of pictures.

This software is governed by the CeCILL  license under French law and
abiding by the rules of distribution of free software.  You can  use, 
modify and/ or redistribute the software under the terms of the CeCILL
license as circulated by CEA, CNRS and INRIA at the following URL
"http://www.cecill.info". 

As a counterpart to the access to the source code and  rights to copy,
modify and redistribute granted by the license, users are provided only
with a limited warranty  and the software's author,  the holder of the
economic rights,  and the successive licensors  have only  limited
liability. 

In this respect, the user's attention is drawn to the risks associated
with loading,  using,  modifying and/or developing or reproducing the
software by the user in light of its specific status of free software,
that may mean  that it is complicated to manipulate,  and  that  also
therefore means  that it is reserved for developers  and  experienced
professionals having in-depth computer knowledge. Users are therefore
encouraged to load and test the software's suitability as regards their
requirements in conditions enabling the security of their systems and/or 
data to be ensured and,  more generally, to use and operate it in the 
same conditions as regards security. 

The fact that you are presently reading this means that you have had
knowledge of the CeCILL license and that you accept its terms.
*/

// Using behaviour.js: http://bennolan.com/behaviour

// //-----------------------------------------------------------------------------
// function sendAjax(params, responseFnct) {   // responseFnct = Function(XMLHttpRequest)
//     new Ajax.Request('ajax.php', 
// 				{method: 'get', parameters: params, onComplete: responseFnct}
// 				);
// }

//-----------------------------------------------------------------------------
function sendAjax(params, responseFnct, container) {
    if (container!=undefined) {
       // responseFnct = Function(Object container, String response)
        new Ajax.Updater(container, 'ajax.php',
				    {method: 'get', parameters: params, insertion: responseFnct}
				    );
    } else {
       // responseFnct = Function(XMLHttpRequest)
        new Ajax.Request('ajax.php', 
				    {method: 'get', parameters: params, onComplete: responseFnct}
				    );
    }
}

//-----------------------------------------------------------------------------
function backToThumbs() {
    var nodes = document.getElementsByClassName('photo');   // One node expected
    var id = nodes[0].id.slice(1);
    window.location = "main.php?"+Form.serialize('searchform1')+"&pos=-"+id;
}

//-----------------------------------------------------------------------------
function toggleSearchType() {
    var nodes = document.getElementsByClassName('search');
    var sbt;
    for(var i=0; i<nodes.length; i++) {
        if (Element.hasClassName(nodes[i], "simple")) {
            Element.removeClassName(nodes[i], "simple");
            Element.addClassName(nodes[i], "advanced");
            sbt = "advanced";
        } else {
            Element.addClassName(nodes[i], "simple"); // bug !!!
            Element.removeClassName(nodes[i], "advanced");
            sbt = "simple";
        }
    }
    document.cookie = 'searchbarType='+sbt+'; path=/';
}

//-----------------------------------------------------------------------------
function showKeywords(elt) {
    instance = elt.id.charAt(elt.id.length-1);
    var list = $('keywords'+instance);
    var win = (instance==1) ? winkw1 : winkw2;
    // Workaound the fact that the form has a position:relative
    // and that the popup div must be included in the form:
    // remove the position of the form from the actual pos.
    var anchpos = Position.get(elt);
    var formpos = Position.get('searchform'+instance);
    win.offsetY = anchpos.height-formpos.top-3;
    win.offsetX = -formpos.left;

    win.showPopup(elt.id);
    return false;
}

//-----------------------------------------------------------------------------
function updateKeywords(elt) {
    var sel = $F(elt.id);
    var nb = sel.length;
    instance = elt.id.charAt(elt.id.length-1);
    var bt = $('chkeywords'+instance);
    if (nb==0) {
        val = "Keywords";
    } else if (nb==1) {
        val = "1 Keyword";
    } else {
        val = nb+" Keywords";
    }
    bt.value = val;

    // Create the tooltip with the list of selected keywords
    var list = new Array();
    for(var i=0; i<elt.options.length; i++) {
        if (elt.options[i].selected) {
            list.push(elt.options[i].label.replace(/\s+\(\d+\)/, ""));
        }
    }
    bt.title = list.join(", ");
}

//-----------------------------------------------------------------------------
// if the search bar has been set to simple, don't take in account the advanced
// parameter in the request
function cleanSearchFields() {
    var nodes = document.getElementsByClassName('search');
    if (Element.hasClassName(nodes[0], "simple")) {
        $('sp1').value="";
        $('period1').value="";
        var kws = $('keywords1');
        for(var i=0; i<kws.options.length; i++) {
            kws.options[i].selected = false;
        }

        try {
            $('sp2').value="";
            $('period2').value="";
            var kws = $('keywords2');
            for(var i=0; i<kws.options.length; i++) {
                kws.options[i].selected = false;
            }
        } catch(e) {}
    }
}

//-----------------------------------------------------------------------------
function updateNbSelected(originalRequest) {
    var nb = originalRequest.responseText;
    var txt;
    if (nb>0) {
        txt = "<strong>"+nb+"</strong>";
        txt += (nb>1) ? " photos" : " photo";
        txt += ' to <a class="perm" href="main.php?dld">download</a>';
    } else {
        txt = "";
    }
    var nodes = document.getElementsByClassName('nbDownload');
    for(var i=0; i<nodes.length; i++) {
        nodes[i].innerHTML = txt;
    }
}

//-----------------------------------------------------------------------------
function togglePhotoSelection(elt) {
    var td = elt.parentNode.parentNode.parentNode.parentNode;
    if (td.className=="selected") {
        td.className = "";
        sendAjax("act=del&id[]="+elt.id.slice(1), updateNbSelected);
    } else {
        td.className = "selected";
        sendAjax("act=add&id[]="+elt.id.slice(1), updateNbSelected);
    }
}

//-----------------------------------------------------------------------------
function changeAllFromPage(tdClass, action) {
    elts=document.getElementsByClassName("photo");
    var ids = new Array();
    for (i=0; i<elts.length; i++) {
        var elt=elts[i];
        var td = elt.parentNode.parentNode.parentNode.parentNode;
        if (td.className!=tdClass) {
            td.className = tdClass;
            ids.push("id[]="+elt.id.slice(1));
        }
    }
    if (ids.length==0) return;
    sendAjax("act="+action+"&"+ids.join("&"), updateNbSelected);
}

//================================ SLIDESHOW ================================
var slideshowTimer;

//-----------------------------------------------------------------------------
function displayNextSlide() {
    var sec = parseInt($('time').value);
    if (isNaN(sec)) sec = 10;
    window.location=$('nextSlide').href+"&time="+sec;
}
//-----------------------------------------------------------------------------
function installTimer() {
    var sec = parseInt($('time').value);
    if (isNaN(sec)) sec = 10;
    slideshowTimer = setTimeout("displayNextSlide()", sec*1000);
}
//-----------------------------------------------------------------------------
function stopSlideshow() {
    if (slideshowTimer!=undefined) {
        clearTimeout(slideshowTimer);
        slideshowTimer = undefined;
    }
    var nodes = document.getElementsByClassName('photopage');   // One node expected
    Element.removeClassName(nodes[0], "playing");
    Element.addClassName(nodes[0], "paused");
    
}

//=========================== BROWSER SIZE =============================

//-----------------------------------------------------------------------------
// from http://www.themaninblue.com/experiment/ResolutionLayout/
function getBrowserWidth() {
	if (window.innerWidth) {
		return window.innerWidth;
    } else if (document.documentElement && document.documentElement.clientWidth != 0) {
		return document.documentElement.clientWidth;
    } else if (document.body){
        return document.body.clientWidth;
    }
	return 0;
}

//-----------------------------------------------------------------------------
function storeBrowserSize() {
    document.cookie = 'browserwidth='+getBrowserWidth()+'; path=/';
}

// These can't be registered with Behaviour
window.onresize = storeBrowserSize;
window.onfocus = storeBrowserSize;

//-----------------------------------------------------------------------------
function toggleExif(elt) {
    var phid = elt.id.slice(1);
    if (phid=="none") {
        Element.hide('exifdata');
    } else {
        Element.show('exifdata');
        if (Element.empty('exifdata')) {
            var img = document.createElement("img");
            img.src = "skin/orig/loading.gif";
            $('exifdata').appendChild(img);
            sendAjax("exif="+phid, undefined, 'exifdata');
        }
    }
    var elts = document.getElementsByClassName('exif');
    for(var i=0; i<elts.length; i++) {
        Element.toggle(elts[i]);
    }
    document.cookie = 'exif='+(phid=="none" ? "no" : "yes") +'; path=/';
}

//=============================================================================
var myrules = {
    '.photo' : function(elt) {
        elt.onclick = function() {
            togglePhotoSelection(this);
        }
    },
    '#selectAll' : function(elt) {
        elt.onclick = function() {
            changeAllFromPage("selected", "add");
        }
    },
    '#unselectAll' : function(elt) {
        elt.onclick = function() {
            changeAllFromPage("", "del");
        }
    },
    '#backToThumbs' : function(elt) {
        elt.onclick = function() {
            backToThumbs();
        }
    },
    'a.simple' : function(elt) {
        elt.onclick = function(event) {
            toggleSearchType();
            Event.stop(event);
        }
    },
    'a.advanced' : function(elt) {
        elt.onclick = function(event) {
            toggleSearchType();
            Event.stop(event);
        }
    },
    '.submitsearch' : function(elt) {
        elt.onclick = function(event) {
            cleanSearchFields();
        }
    },
    '#play' : function(elt) {
        elt.onclick = function(event) {
            displayNextSlide();
        }
    },
    '#pause' : function(elt) {
        elt.onclick = function(event) {
            stopSlideshow();
        }
    },
    '.chkeywords' : function(elt) {
        elt.onclick = function(event) {
            showKeywords(elt);
        }
    },
    '.keywords' : function(elt) {
        elt.onclick = function(event) {
            updateKeywords(elt);
        }
    },
    'a.exif' : function(elt) {
        elt.onclick = function(event) {
            toggleExif(elt);
        }
    }
};

Behaviour.register(myrules);
