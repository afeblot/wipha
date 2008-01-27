{include file="header.tpl"}
{if #demo#}
<div class="warn">
Demo mode - Please note that in demo mode, many users can login as administrator in the
same time, and perform concurrent modifications on users. Maybe you won't see your change
after a logout / re-login, if someone else overwrote it in the meantime.
</div>
<div class="warn">
Demo mode - Forbidden actions:
    <ul>
        <li> Modify the admin login/password </li> 
        <li> Modify Guest settings </li>
        <li> Modify Libraries settings </li>
    </ul>
</div>
{else}
<div align="center">
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    If you like WiPhA,<br/>you may consider a donation<br/>
    <input type="hidden" name="cmd" value="_xclick"/>
    <input type="hidden" name="business" value="info@globs.org"/>
    <input type="hidden" name="item_name" value="WiPhA"/>
    <input type="hidden" name="no_note" value="1"/>
    <input type="hidden" name="currency_code" value="EUR"/>
    <input type="hidden" name="tax" value="0"/>
    <input type="hidden" name="lc" value="US"/>
    <input type="hidden" name="bn" value="PP-DonationsBF"/>
    <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" name="submit"
           alt="Paypal donate"/>
</form>
</div>
{/if}
            <div>
                <form action="{$smarty.server.SCRIPT_NAME}" method="post" accept-charset="UTF-8">
                    <fieldset>
                        <legend> Administrator </legend>
                        Login <input autocomplete="off" type="text" name="al" value="{$admin}" size="10"/> 
                        Password <input autocomplete="off" type="password" name="ap" size="10"/> 
                        <input type="submit" value="Change"/>
{if $error_adm != ''}                 {include file="users_error.tpl" error=$error_adm}{/if}
                    </fieldset>
                </form>
                <fieldset>
                    <legend> Libraries </legend>
                    <h4>Add a new library </h4>
                    <form action="{$smarty.server.SCRIPT_NAME}" method="post" accept-charset="UTF-8">
                        Name <input autocomplete="off" type="text" name="libn" size="20"/> 
                        Path <input autocomplete="off" type="text" name="libp" size="50" title="Absolute library path. Example: /Users/john/Pictures/iPhoto Library"/> 
                        <input type="submit" value="Add"/>
{if $error_lib != ""}                {include file="libs_error.tpl" error=$error_lib}{/if}
                    </form>
                    <h4>Manage existing libraries </h4>
                    <ul>
{foreach from=$libs item="lib" key="id"}
                    <li>
                        <a href="{$smarty.server.SCRIPT_NAME}?dellib={$id}"><img src="skin/{#skin#}/delete.gif" title="Delete library {$lib.name}" alt="Delete"/></a>
                        &nbsp;<a href="{$smarty.server.SCRIPT_NAME}?elib={$id}" title="Edit library {$lib.name}">{$lib.name}</a>
                        {if $smarty.session.library.id == $id} <img src="skin/{#skin#}/checked.gif" alt="Selected"/>
                        {else}
                        <a href="main.php?sellib={$id}&amp;src=ref"><img src="skin/{#skin#}/select.gif" title="Load library {$lib.name}" alt="Load"/></a>
                        {/if}
                    </li>
{/foreach}
                    </ul>
                </fieldset>
                <fieldset>
                    <legend> Users </legend>
                    <h4>Add a new user </h4>
                    <form action="{$smarty.server.SCRIPT_NAME}" method="post" accept-charset="UTF-8">
                        Login <input autocomplete="off" type="text" name="aul" size="10"/> 
                        Password <input autocomplete="off" type="password" name="aup" size="10"/> 
                        <input type="submit" value="Add"/>
{if $error_user != ""}                {include file="users_error.tpl" error=$error_user}{/if}
                    </form>
                    <h4>Manage existing users </h4>
                    <ul>
                    {foreach from=$logins item="login"}
                    <li>
                        {if $login != 'guest'}
                        <a href="{$smarty.server.SCRIPT_NAME}?del={$login}"><img src="skin/{#skin#}/delete.gif" title="Delete user {$login}" alt="Delete {$login}"/></a>
                        {else}
                        &nbsp; &nbsp;
                        {/if}
                        &nbsp;<a href="{$smarty.server.SCRIPT_NAME}?eu={$login}">{$login|capitalize}</a>
                    </li>
                    {/foreach}
                    </ul>
                </fieldset>
                <fieldset>
{if isset($smarty.session.library)}
                    <legend> <em>{$smarty.session.library.name}</em> Library Albums</legend>
                    <ul>
{foreach from=$albums item="album" key="albumId"}
                    <li><a href="{$smarty.server.SCRIPT_NAME}?ea={$albumId}">{$album}</a> </li>
{/foreach}
                    </ul>
{else}
                    <legend> Library Albums</legend>
                    <em>No library currently loaded</em>
{/if}
                </fieldset>
            </div>
{include file="footer.tpl"}
