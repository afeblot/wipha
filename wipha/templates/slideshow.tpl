{include file="header.tpl" }
{include file="searchbar.tpl"}
<div class="photopage slidepage {$state}" align="center">
    <div id="navSlide">
        <a id="prevSlide" class="paused" href="{$smarty.server.SCRIPT_NAME}?sl={$prevId}&amp;lib={$smarty.session.library.id}"><img src="skin/{#skin#}/prev.gif" alt="previous" title="Previous Slide"/></a>
        <a id="backToThumbs" href="#"><img src="skin/{#skin#}/thumbs.gif" alt="Thumbs" title="Go back to thumbs page"/></a>
        <a id="nextSlide" class="paused" href="{$smarty.server.SCRIPT_NAME}?sl={$nextId}&amp;lib={$smarty.session.library.id}"><img src="skin/{#skin#}/next.gif" alt="next" title="Next Slide"/></a>
    </div>
    <div id="runSlide">
        <input type="text" id="time" value="{$time}"/><label for="time">&nbsp;Sec</label>
        <a class="paused"  href="#" id="play"><img src="skin/{#skin#}/play.png" alt="Play" title="Start the slideshow"/></a>
        <a class="playing" href="#" id="pause"><img src="skin/{#skin#}/pause.png" alt="Pause" title="Pause the slideshow"/></a>
    </div>
    <table border="0" cellspacing="10" cellpadding="0" align="center">
    <tr>
    <td align="center" valign="bottom" {$photoId|is_in_array:$download:'class="selected"'}>
        <div class="centercell"> <div class="ydsf"> <div class="inner">
{if $photo.MediaType eq "Image"}
            <img class="photo" src="{$smarty.server.SCRIPT_NAME}?ph={$photoId}&amp;lib={$smarty.session.library.id}" alt="" id="p{$photoId}" width="{$width}" height="{$height}"/>
{else}
            <img class="photo" src="{$smarty.server.SCRIPT_NAME}?th={$photoId}&amp;lib={$smarty.session.library.id}" alt="" id="p{$photoId}" width="{$width}" height="{$height}"/>
{/if}
        </div> </div> </div>
        <div class="legend">
            {if $photo.Caption ne ""}
                <div class="title">{$photo.Caption|escape}</div>
            {/if}{if $photo.Comment ne ""}
                <div class="comment">{$photo.Comment|escape}</div>
            {/if}
            {if $photo.MediaType eq "Image"}
                <div class="icon"><a href="{$smarty.server.SCRIPT_NAME}?fs={$photoId}&amp;lib={$smarty.session.library.id}" target="_blank"><img src="skin/{#skin#}/zoom.gif" alt="zoom" title="Display the full size photo" /></a></div>
            {else}
                <div class="icon"><a href="{$smarty.server.SCRIPT_NAME}?vd={$photoId}&amp;lib={$smarty.session.library.id}" target="_blank"><img src="skin/{#skin#}/video.png" alt="view video" title="View video" /></a></div>
            {/if}
           <div class="date">{$photo.Timestamp|date_format:"%d %b %Y, %H:%M:%S"}</div>        
        </div>
    </td>
    </tr></table>
    
    {if $photo.MediaType eq "Image"}
        <div id="exif">
            <a class="perm" href="#" id="x{$photoId}">Show EXIF data</a>
        </div>
    {/if}

</div>
{if $state == "playing"}
<script type="text/javascript"> installTimer(); </script>
{/if}
{include file="footer.tpl"}
