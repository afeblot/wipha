{include file="header.tpl"}
        <h2>User <em>{$login}</em> administration</h2>
            <div>
                <form action="{$smarty.server.SCRIPT_NAME}" method="post" accept-charset="UTF-8">
                    {if $login != 'guest'}
                    <fieldset>
                        <legend> User login </legend>
                        Login <input autocomplete="off" type="text" name="ul" value="{$login}" size="10"/> 
                        Password <input autocomplete="off" type="password" name="up" size="10"/> 
{if $error_user != ''}                 {include file="users_error.tpl" error=$error_user}{/if}
                    </fieldset>
                    {/if}
                    <fieldset>
                        {if isset($smarty.session.library)}
                        <legend> <em>{$smarty.session.library.name}</em> library authorized albums </legend>
                        {html_checkboxes name="ual" options=$albums selected=$userAlbums separator="<br />"}
                        {else}
                        <legend> Library authorized albums </legend>
                        <em>No library currently loaded</em>
                        {/if}
                        {* input hidden to always send the 'ual' request even if no box is checked *}
                        <input type="hidden" name="ual[]" value=""/>
                    </fieldset>
{if $error_user != '' && $login == 'guest'} {include file="users_error.tpl" error=$error_user}{/if}
                    <input type="submit" value="Change"/>
                    <input type="button" value="Cancel" onclick="document.location.href='admin.php'"/>
                </form>
            </div>
{include file="footer.tpl"}
