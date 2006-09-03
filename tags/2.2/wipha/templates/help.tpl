{include file="header.tpl" }
{if isset($template)}
    <p><a href="{$smarty.server.SCRIPT_NAME}?help">Back</a></p>
    <div class="help">{include file="$template"}</div>
    <p><a href="{$smarty.server.SCRIPT_NAME}?help">Back</a></p>
{else}
    {include file="help_index.tpl"}
    <div class="image">
        <p><a href="{$smarty.server.SCRIPT_NAME}?help=intro">Summary</a></p>
        {if $smarty.session.is_admin == true }
            <p><a href="{$smarty.server.SCRIPT_NAME}?help=admin">Administration</a></p>
        {/if}
        <p><a href="{$smarty.server.SCRIPT_NAME}?help=usage">Usage</a></p>
        <p><a href="{$smarty.server.SCRIPT_NAME}?help=credits">Credits</a></p>
    </div>
{/if}
{include file="footer.tpl"}
