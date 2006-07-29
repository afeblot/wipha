{include file="header.tpl"}
        <h2>Album <em>{$album}</em> (<em>{$smarty.session.library.name}</em> library)</h2>
            <div>
                <form action="{$smarty.server.SCRIPT_NAME}?" method="post" accept-charset="UTF-8">
                    <fieldset>
                        <legend> Authorized users </legend>
                        {html_checkboxes name="alu" options=$logins|capitalize selected=$albumUsers separator="<br />"}
                        {* input hidden to always send the 'alu' request even if no box is checked *}
                        <input type="hidden" name="alu[]" value=""/>
                    </fieldset>
{if $error_user != ''}                 {include file="users_error.tpl" error=$error_user}{/if}
                    <input type="submit" value="Change"/>
                    <input type="button" value="Cancel" onclick="document.location.href='admin.php'"/>
                </form>
            </div>
{include file="footer.tpl"}
