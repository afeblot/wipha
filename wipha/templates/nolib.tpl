{include file="header.tpl" }
<div class="note">
{if $smarty.session.is_admin}
<p> You have not added any library yet.</p>
<p>Please select one in the admin pages</p>
{else}
<p> You have not been granted any library access by the Administrator yet.</p>
{/if}
{include file="footer.tpl"}
