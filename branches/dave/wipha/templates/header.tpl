{config_load file="wipha.conf" scope="global"}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>Web iPhoto Access</title>
        <link rel="icon" type="image/png" href="skin/{#skin#}/favicon.png"/>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <link rel="stylesheet" type="text/css" href="skin/{#skin#}/ydsf.css"/>
        <link rel="stylesheet" type="text/css" href="skin/{#skin#}/wipha.css"/>
        <link rel="stylesheet" type="text/css" href="skin/{#skin#}/wiphacommon.css"/>
{if $smarty.session.browser.app == 'OPERA' }
        <link rel="stylesheet" type="text/css" href="skin/{#skin#}/opera.css"/>
{/if}
        <script type="text/javascript" src="3rdParty/prototype-1.4.0/dist/prototype.js"></script>
        <script type="text/javascript" src="3rdParty/CalendarPopup.js"></script>
        <script type="text/javascript" src="3rdParty/behaviour.js"></script>
        <script type="text/javascript" src="3rdParty/PHP_Serializer.js"></script>
        <script type="text/javascript" src="libs/wipha.js"></script>
        <script type="text/javascript" src="libs/datemap.js"></script>
{if ! empty($photocastUrl)}
        <link rel="alternate" title="WiPhA {$smarty.session.albums[$albumDisplayed].AlbumName}" href="{$photocastUrl|escape}{if $smarty.session.user=='guest'}&amp;guest{/if}" type="application/rss+xml"/>
{/if}
{if ! empty($prefetch)}
        <link rel="prefetch" href="{$prefetch}"/>   {* for next slideshow image *}
{/if}
    </head>
    <body>
        <div class="menu">
{if $smarty.session.user == ""}
            <span class="left">Please login</span>
{else}
{if $smarty.session.is_admin == true }
            <a href="admin.php" class="right">Admin</a>{/if}
            <a href="main.php?logout" class="right">Logout</a>
            <span class="left">Logged as <strong>{$smarty.session.user|capitalize}</strong>
            {if isset($smarty.session.library)} in <strong>{$smarty.session.library.name}</strong>{/if}</span>
{if $smarty.session.nblibrary > 1}
            <a href="main.php?sellib&amp;src=ref">Select Lib</a>
{/if}
            <a href="main.php">New search</a>
            <a href="main.php?help" target="_blank">Help</a>
{if ! empty($photocastUrl)}
            <a href="{$photocastUrl|escape}{if $smarty.session.user=='guest'}&amp;guest{/if}" class="photocast" title="{$smarty.session.albums[$albumDisplayed].AlbumName} album Photocast" target="_blank">&nbsp;&nbsp;&nbsp;&nbsp;</a>
{/if}
{/if}
        </div>

{include file="ie6nomore.tpl"}

{if isset($newVer)}
<div class="note">
    WiPhA version <strong>{$newVer.ver}</strong>
    is available since <strong>{$newVer.dat}</strong> (<a href="{#home#}/articles.php?lng=en&pg=332">What's new</a>).<br/>
    You can download it <a href="{#home#}/download.php?lng=en" target="_blank">here</a>.<br/>
    This note will still be displayed for {$time} seconds until your next log in. 
</div>
{/if}
        <div class="page">
