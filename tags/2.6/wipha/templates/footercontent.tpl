{*
 This has been extracted of the footer.tpl because of the tricky progress.tpl
whch needs to display the footer *without* closing the page div and the body.
*}
<div class="footer">
    <img src="img/wipha.png" height="25px" alt="WiPhA logo"/>
    <div>Web iPhoto Access (<a href="{#home#}/?lng=en" target="_blank">WiPhA</a>) v{#version#}<br/>
    Released under the <a href="http://www.cecill.info/index.en.html" target="_blank">CeCILL Free License</a> </div>
    <a href="http://smarty.php.net" target="_blank"><img src="skin/{#skin#}/smarty-80x15.png" alt="Developped with Smarty" /></a>
    <a href="http://www.apple.com/macmini/" target="_blank"><img src="skin/{#skin#}/button_apple.gif" alt="Made on a Mac (mini)" /></a>
    <a href="http://www.apple.com/macosx/" target="_blank"><img src="skin/{#skin#}/appleop.gif" alt="Running on a Mac" /></a>
    <a href="http://httpd.apache.org/" target="_blank"><img src="skin/{#skin#}/apachepower.png" alt="Powered by Apache" /></a>
    <a href="{$smarty.session.cssValid}" target="_blank"><img src="skin/{#skin#}/valid-css2.png" alt="Valid CSS2" title="This site is valid CSS2 with intentional exceptions" /></a>
    <a href="{$smarty.session.xhtmlValid}" target="_blank"><img src="skin/{#skin#}/valid-xhtml.png" alt="Valid XHTML 1.0 Transitional" title="This site is valid XHTML 1.0 with intentional exceptions"/></a>
</div>
