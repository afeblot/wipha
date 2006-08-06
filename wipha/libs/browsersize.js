//var oldBrowserWidth is directly included in the web page with the server known value

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
function sendBrowserSize(){
    var browserWidth = getBrowserWidth();
    if (oldBrowserWidth!=browserWidth) {
        sendAjax("brw="+browserWidth, undefined);
        oldBrowserWidth = browserWidth;
    }
}

// These can't be registered with Behaviour
window.onresize = sendBrowserSize;
window.onfocus = sendBrowserSize;
