/*
Alexandre Feblot, 2005-2011
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

// All of this would be much cleaner if I could embed it in an object,
// but I can't find a way to properly pass the method callbacks to
// to the behaviour and ajax functions.

{ // datemap bloc

var lastElt;
var oldElt;
var inSel;   // 0: before, 1: inSel, 2: done
var win;
var root;
var period; // Array of 1 or 2 (ordered) dates
var instance;
var receptor;


var behaviourRules = {
    'ybl' : function(elt) {
        elt.onclick = function(event) {
            Event.stop(event);
            year = elt.childNodes[0].innerHTML;
            DateMap_update(elt, year);
        }
    },
    'mbl' : function(elt) {
        elt.onclick = function(event) {
            Event.stop(event);
            month = elt.childNodes[0].innerHTML;
            year = elt.parentNode.childNodes[0].innerHTML;
            DateMap_update(elt, month+" "+year);
        }
    },
    'dbl' : function(elt) {
        elt.onclick = function(event) {
            Event.stop(event);
            day = elt.innerHTML;
            month = elt.parentNode.childNodes[0].innerHTML;
            year = elt.parentNode.parentNode.childNodes[0].innerHTML;
            DateMap_update(elt, day+" "+month+" "+year);
        }
    },
    '#datemap' : function(elt) {
        elt.onclick = function(event) {
            Event.stop(event);
            DateMap_update();
        }
    },
    '.chperiod' : function(elt) {
        elt.onclick = function(event) {
            // Get the instance from the id of the "Choose" button.
            instance = elt.id.charAt(elt.id.length-1);
            DateMap_open();
        }
    },
    '#dmok' : function(elt) {
        elt.onclick = function(event) {
            DateMap_accept();
        }
    }
};
Behaviour.register(behaviourRules);

//-----------------------------------------------------------------------------
// instance: 1 or 2
function DateMap_init(div, receptorId) {
    receptor = receptorId;      // without the instance. Ex: 'period'
    win = new PopupWindow(div);
    win.offsetX = 0;
    win.offsetY = 30;
    win.autoHide();
}

//-----------------------------------------------------------------------------
// PUBLIC
function DateMap_open() {
    var content = '<div id="dmdlg"><div id="datemap"></div>'+
                  '<div id="dmbot"><p id="dmperiod"></p><input type="button" value="OK" id="dmok"/></div>'+
                  '</div>';
    
    win.populate(content);
    oldElt=lastElt=undefined;
    win.showPopup("period"+instance);
    root = $('datemap');
    DateMap_update();
    return false;
}

//-----------------------------------------------------------------------------
// PRIVATE
// Compute the length of the segment according to the number of pictures, with 
// a log scale : length = a+b*ln(nbPictures)
function DateMap_normalizedWidth(nb, type) {
    var lMin;       // min length of the segment
    var lMax;       // max length of the segment
    var nbMin = 1;  // min number of pictures, to map to lMin
    var nbMax;      // max number of pictures, to map to lMax
    if (type=='ybl') {
        lMin = 4;
        lMax = 100;
        nbMax = 4000;
    } else if (type=='mbl') {
        lMin = 3;
        lMax = 30;
        nbMax = 500;
    } else {
        lMin = 2;
        lMax = 20;
        nbMax = 100;
    }

    var b = (lMin-lMax)/(Math.log(nbMin)-Math.log(nbMax));
    var a = lMin-b*Math.log(nbMin);
    var l = Math.round(a+b*Math.log(nb))
    return l+"ex";
}

//-----------------------------------------------------------------------------
// PRIVATE
function DateMap_addDateElts(father, elts) {
//$('date').innerHTML = " F="+father.innerHTML;
    var h = $H(elts);
    var k = h.keys();
    var v = h.values();
    var ft = father.tagName.toLowerCase();
    var ct = (ft=="ybl") ? "mbl" : (ft=="mbl") ? "dbl" : "ybl";
    for (i=0; i<k.length; i++) {
        var val = k[i];
        var child = document.createElement(ct);
        child.title = v[i]+ (v[i]==1 ? " photo" : " photos");
        child.style.minWidth = DateMap_normalizedWidth(v[i], ct);
        if (ct=="dbl") {
            child.innerHTML = val;
        } else {
            var elt = document.createElement("txt");
            elt.innerHTML = val;
            child.appendChild(elt);
        }
        father.appendChild(child);
    }
    Behaviour.apply();
}

//-----------------------------------------------------------------------------
// PRIVATE
function DateMap_pushDate(elt) {
    var ok = true;
    if (elt!=undefined) {
        if (lastElt!=undefined) {
            if ((lastElt.date!=elt.date) &&
               ((lastElt.date.indexOf(elt.date,0)!=-1) ||
                (elt.date.indexOf(lastElt.date,0)!=-1))) {
                // if lastElt and elt are of the same date period,
                //don't push, just refine or generalize the lastElt
                // example: 25 Dec 2001 to 17 Fev 2002 , click on Fev 2002
                //      --> select 25 Dec 2001 to Fev 2002.
                ok = false;
            }
        }
    }
    if (ok) {
        oldElt = lastElt;
    }
    lastElt = elt;

    // if lastElt, oldElt and elt are of the same date period,
    // (the previous push has not been done)
    // and we replace a stupid interval by the last given period
    // example: 9 Fev 2002 to 17 Fev 2002 , click on Fev 2002
    //      --> select Fev 2002.
    if (elt!=undefined) {
        if (oldElt!=undefined) {
            if ((oldElt.date!=elt.date) &&
               ((oldElt.date.indexOf(elt.date,0)!=-1) ||
                (elt.date.indexOf(oldElt.date,0)!=-1))) { 
                oldElt = elt;
            }
        }
    }
}

//-----------------------------------------------------------------------------
// PRIVATE
// - Select the father elt if all it's children are selected, then do the same
//   check for the grand-father (if any)
// - Unselect the father (and grand-father if any) if not all it's children are selected
function DateMap_correctFathersSelection(elt) {
    if (elt==undefined) return;
    elt = elt.parentNode;
    var et = elt.tagName.toLowerCase();
    if (et!="mbl" && et!="ybl") return;
    var  nbNotSel = 0;
    for (var i=0; i<elt.childNodes.length; i++) {
        var child = elt.childNodes[i];
        et = child.tagName.toLowerCase();
        if (et!="dbl" && et!="mbl" && et!="ybl") continue;
        if (child.className=="") {
            nbNotSel++;
        }
    }
    
    if (nbNotSel==0) {
        elt.className = "sel";
        DateMap_correctFathersSelection(elt);
    } else {
        elt.className = "";
        elt = elt.parentNode;
        et = elt.tagName.toLowerCase();
        if (et=="ybl") {
            elt.className = "";
        }
    }
}

//-----------------------------------------------------------------------------
// PRIVATE
// Selects all elements between oldElt and lastElt
// as we go through all elements in chrological order, let's fill in the same time
// the period variable.
function DateMap_updateSelection(elt, recurs) {
    if (recurs==undefined) {
        elt = root;
        inSel = 0;
        period = new Array();
    }

    if (inSel==0) {
        elt.className = "";
        if (elt==lastElt || elt==oldElt) {
            if (elt.date!=undefined) {
                period.push(elt.date);
            }
            if (lastElt==undefined || oldElt==undefined) {
                inSel = 2;
            } else {
                inSel = 1;
            }
            elt.className = "sel";
        }
    } else if (inSel==1) {
        elt.className = "sel";
        if (elt==lastElt || elt==oldElt) {
            if (elt.date!=undefined) {
                    period.push(elt.date);
            }
            inSel = 2;
        }
        if (oldElt==undefined || oldElt==lastElt) {
            inSel = 2;
            elt.className = "";
        }
    } else {
            elt.className = "";
    }
    
    if (elt.childNodes.length>0) {
        for (var i=0;i<elt.childNodes.length; i++) {
            var e = elt.childNodes[i];
            DateMap_updateSelection(e, true);
        }
    }

    if (recurs==undefined) {
        DateMap_correctFathersSelection(oldElt);
        DateMap_correctFathersSelection(lastElt);
        $('dmperiod').innerHTML = DateMap_simplifyPeriod(period).join(' - ');
    }
}

//-----------------------------------------------------------------------------
// PRIVATE
function DateMap_simplifyPeriod(period) {
    if (period.length!=2) {
        return period;
    }
    var d0 = period[0].split(' ').reverse();
    var d1 = period[1].split(' ').reverse();
    var d = new Array();
    if (d0[1]==d1[1]) d0.splice(1, 1);
    if (d0[0]==d1[0]) d0.splice(0, 1);
    period[0] = d0.reverse().join(' ');
    return period;
}

//-----------------------------------------------------------------------------
// PRIVATE
function DateMap_accept() {
    win.hidePopup();
    $(receptor+instance).value = DateMap_simplifyPeriod(period).join(' - ');   
    $(receptor+instance).className = "";    // remove the "error" class if necessary   
}


//-----------------------------------------------------------------------------
// PRIVATE
function DateMap_updateWithInfo(loadingImg, txt) {
    var container = loadingImg.parentNode;
    container.removeChild(loadingImg);
    if (txt!="") {
        var php = new PHP_Serializer();
        var datesInfo = php.unserialize(txt);
        DateMap_addDateElts(container, datesInfo);
    }
    DateMap_updateSelection();
}

//-----------------------------------------------------------------------------
// PRIVATE
function DateMap_update(elt, date) {
    if (elt!=undefined) elt.date = date;
    DateMap_pushDate(elt);

    elt = elt ? elt : root;

    if ((elt.childNodes.length>0 && elt==root)||    // if only one single year
        (elt.childNodes.length>1)||                 // 1 txt elmt and some bloc children
        (elt.tagName.toLowerCase()=="dbl")) {       // if it's a day
        DateMap_updateSelection();
    } else {
        if (date==undefined) date = "";
        albumId = (instance==1) ? document.searchform1.al.value : document.searchform2.al.value;
        var img = document.createElement("img");
        img.src = "skin/orig/loading.gif";
        elt.appendChild(img);
        sendAjax("map="+date+"&alb="+albumId, DateMap_updateWithInfo, img);
        

    }
}

}   // end of datemap bloc
