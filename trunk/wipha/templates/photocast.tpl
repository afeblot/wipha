<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
  <channel>
    <title>{$albumName|escape:'html'} - {$library.name|escape:'html'}</title>
    <link>{$site}</link>
    <language>en-us</language>
    <description>{$albumName|escape:'html'} - {$library.name|escape:'html'}</description>
{foreach from=$photoIds item="photoId"}
{if $photos[$photoId].MediaType eq "Image"}
    <item>
      <title>{$photos[$photoId].Caption|escape:'html'}</title>
      <description>{$photos[$photoId].Caption|escape:'html'}&lt;br/&gt;{$photos[$photoId].Comment|escape:'html'}&lt;br/&gt;&lt;img src="{$site}/photocast.php?lib={$library.id}&amp;img={$photos[$photoId].encThumbPath}{if $smarty.session.user=='guest'}&amp;guest{/if}"/&gt;</description>
      <pubDate>{$photos[$photoId].Timestamp|date_format:"%a, %d %b %Y %T %z"}</pubDate>
      <guid>{$site}/photocast.php?lib={$library.id}&amp;sl={$photos[$photoId].encThumbPath}{if $smarty.session.user=='guest'}&amp;guest{/if}</guid>
      <link>{$site}/photocast.php?lib={$library.id}&amp;sl={$photos[$photoId].encThumbPath}{if $smarty.session.user=='guest'}&amp;guest{/if}</link>
      <enclosure {if $photos[$photoId].size}length="{$photos[$photoId].size}" {/if}type="image/jpeg" url="{$site}/photocast.php?lib={$library.id}&amp;cpr={$photos[$photoId].encImgPath}{if $smarty.session.user=='guest'}&amp;guest{/if}"/>
    </item>
{/if}
{/foreach}
  </channel>
</rss>
