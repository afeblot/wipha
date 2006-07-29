{include file="header.tpl"}
<script type="text/javascript">
Behaviour.addLoadEvent( function() {ldelim}
    e=$('warnjs'); e.parentNode.removeChild(e);
    e=$('loginpanel'); Element.show(e);
    {rdelim} );
</script>
<div class="login">
    <h2> Web iPhoto Access (WiPhA) </h2>
    <img src="img/wipha.png" alt="WiPhA logo"/>
    <p>Now providing Photocasts <img src="img/photocast.png"/></p>
</div>
<div class="warn" id="warnjs">
JavaScript must be enabled in order for you to use WiPhA. However, it seems JavaScript
is either disabled or not supported by your browser. Please enable JavaScript
by changing your browser options, and then <a href="{$smarty.server.SCRIPT_NAME}">try again</a>.<br/>
You may have a look on <a href="http://www.google.com/support/bin/answer.py?answer=23852"
target="_blank">this page</a> to find out how to do this on the most common browsers.
</div>
{if #demo#}
<div class="warn">
Demo mode - administrator login/password: <tt>admin/admin</tt>
</div>
{/if}
{if $defaultAdmin && ! #demo#}
<div class="warn">
<p>Default administrator login/password: <tt>admin/admin</tt></p>
<p>Please customize it as soon as possible.</p>
</div>
{/if}
{if $smarty.session.browser.app == 'OPERA'}
<div class="warn">
    The Opera cache system will prevent WiPhA to refresh it's pages. Please configure Opera
    to always reload the page: Preferences, Advanced tab, History, Check documents: always.
</div>
{/if}
<fieldset id="loginpanel" style="display:none;">
    <form action="{$smarty.server.SCRIPT_NAME}" method="post">
    <table border="0">
        {if $error ne ""}
            <tr>
                <td class="error">
                    {if $error eq "user_empty"}You must supply a user.
                    {elseif $error eq "passwd_empty"} You must supply a password.
                    {elseif $error eq "all_empty"} You must supply a user and a password.
                    {elseif $error eq "bad_login"} Bad user or password.
                    {/if}
                </td>
            </tr>
        {/if}
        <tr>
            <td>Login:</td>
            <td><input type="text" name="user" value="{$smarty.post.user}" /></td>
        </tr> <tr>
            <td>Password:</td> <td><input type="password" name="passwd" /></td>
        </tr> <tr>
            <td colspan="2" align="center"><br/><input type="submit" value="Login" />
            <p>If you have no account, you can login as <a href="{$smarty.server.SCRIPT_NAME}?guest" class="perm">Guest</a></p>
            </td>
        </tr>
    </table>
    </form>
</fieldset>
{include file="footer.tpl"}
