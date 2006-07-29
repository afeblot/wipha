<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
  <channel>
    <title>{$albumName} - {$library.name}</title>
    <link>{$site}</link>
    <language>en-us</language>
    <description>{$albumName} - {$library.name}</description>
{foreach from=$photoIds item="photoId"}
    <item>
      <title>{$photos[$photoId].Caption}</title>
      <description>{$photos[$photoId].Caption}&lt;br/&gt;{$photos[$photoId].Comment}&lt;br/&gt;&lt;img src="{$site}/photocast.php?lib={$library.id}&amp;img={$photos[$photoId].encThumbPath}{if $smarty.session.user=='guest'}&amp;guest{/if}"/&gt;</description>
      <pubDate>{$photos[$photoId].Timestamp|date_format:"%a, %d %b %Y %T %z"}</pubDate>
      <guid>{$site}/photocast.php?lib={$library.id}&amp;sl={$photos[$photoId].encThumbPath}{if $smarty.session.user=='guest'}&amp;guest{/if}</guid>
      <link>{$site}/photocast.php?lib={$library.id}&amp;sl={$photos[$photoId].encThumbPath}{if $smarty.session.user=='guest'}&amp;guest{/if}</link>
      <enclosure {if $photos[$photoId].size}length="{$photos[$photoId].size}" {/if}type="image/jpeg" url="{$site}/photocast.php?lib={$library.id}&amp;cpr={$photos[$photoId].encImgPath}{if $smarty.session.user=='guest'}&amp;guest{/if}"/>
    </item>
{/foreach}
  </channel>
</rss>
