{include file="header.tpl"}
{if $nbdownload > 0}
        <div class="warn">
            You have {$nbdownload} photo{if ($nbdownload) > 1 }s{/if} selected for
            download in the current library. This selection will be lost in you select
            an other library.
        </div>
{/if}
        <h4> Please select a Library</h4>
            <ul>
{foreach from=$libs item="lib" key="id"}
            <li>
            {if $smarty.session.library.id == $id}
                {$lib.name} <img src="skin/{#skin#}/checked.gif" alt="Selected"/>
            {else}
                <a class="perm" href="{$smarty.server.SCRIPT_NAME}?sellib={$id}&amp;src={$src}">{$lib.name}</a>
            {/if}
            </li>
{/foreach}
            </ul>
{include file="footer.tpl"}
