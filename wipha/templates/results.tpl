{include file="header.tpl" }

{include file="searchbar.tpl"}
{if $photoIds|@count > 0}
    {include file="photopage.tpl" }
    {if $need2ndSeachBar}
        {include file="searchbar.tpl"}
    {/if}
{else}
    <p>Your search did not match any photo.</p>
{/if}
{include file="footer.tpl"}
