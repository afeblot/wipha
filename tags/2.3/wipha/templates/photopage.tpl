<div class="photopage thumbpage" align="center">
<a href="#" id="selectAll" class="perm">Select all</a>,
<a href="#" id="unselectAll" class="perm">Unselect all</a><br/>
{include file="pager.tpl" }

{table_foreach from=$photoIds item="photoId" cols=$nbCols trailpad=""
               table_attr='border="0" cellspacing="10" cellpadding="0" align="center"'
               td_attr=$photoIds|is_in_array:$download:'class="selected"'|cat:'align="center" valign="bottom"'
}
<div class="centercell">
    <div class="ydsf"> <div class="inner">
        <img src="{$smarty.server.SCRIPT_NAME}?th={$photoId}&amp;lib={$smarty.session.library.id}" width="{$photos[$photoId].width}" height="{$photos[$photoId].height}" alt="" id="p{$photoId}" class="photo" title="Click to toggle download selection" />
    </div> </div>
</div>
<div class="legend">
{if $photos[$photoId].Caption ne ""}
    <div class="title">{$photos[$photoId].Caption|escape}</div>
{/if}{if $photos[$photoId].Comment ne ""}
    <div class="comment">{$photos[$photoId].Comment|escape}</div>
{/if}
{if $photos[$photoId].MediaType eq "Image"}
    <div class="icon"><a href="{$smarty.server.SCRIPT_NAME}?fs={$photoId}&amp;lib={$smarty.session.library.id}" target="_blank"><img src="skin/{#skin#}/zoom.gif" alt="zoom" title="Display the full size photo" /></a></div>
    <div class="icon"><a href="{$smarty.server.SCRIPT_NAME}?sl={$photoId}"><img src="skin/{#skin#}/slideshow.png" alt="slideshow" title="Slideshow mode" /></a></div>
{else}
    <div class="icon"><a href="{$smarty.server.SCRIPT_NAME}?vd={$photoId}&amp;lib={$smarty.session.library.id}" target="_blank"><img src="skin/{#skin#}/video.png" alt="view video" title="View video" /></a></div>
{/if}
    <div class="date">{$photos[$photoId].Timestamp|date_format:"%d %b %Y, %H:%M:%S"}</div>        
</div>
{/table_foreach}

{if $selectedNbRows > 2}
    {include file="pager.tpl" }
{/if}

</div>
