{counter name="num_searchbar" assign="cpt"}
{if $cpt == 1}
<script type="text/javascript">
    <!--
    DateMap_init("datemappopdiv", "period");
    -->
</script>
{/if}
<script type="text/javascript">
    <!--
    winkw{$cpt} = new PopupWindow("kwpopdiv{$cpt}");
    winkw{$cpt}.autoHide();
    -->
</script>

<form class="search {$smarty.session.searchbarType}" action="main.php" id="searchform{$cpt}" name="searchform{$cpt}" method="get" accept-charset="UTF-8">
{if $posStart != '' && $nbSelected > 0}
<div class="searchresults"><div><a href="{$mailurl}"><img src="skin/{#skin#}/mail.gif" title="Mail this page" alt="Mail"/></a> Photo{if $posEnd && ($nbSelected>1)}s{/if} <strong> {$posStart} </strong> {if $posEnd}- <strong> {$posEnd} </strong> {/if}of <strong> {$nbSelected} </strong></div></div>
{/if}
    <img class="logo" src="img/wipha.png" alt="WiPhA logo"/>
    {html_options name="al" options=$albums selected=$selectedAlbum}
    {html_options name="row" values=$nbRows output=$pageSizes|cat:' photos' selected=$selectedNbRows}
    &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="submitsearch" value="Display"/>
    &nbsp;&nbsp;<a class="simple perm"href="#">Advanced</a><a class="advanced perm"href="#">Simple</a>
    &nbsp;&nbsp;&nbsp;&nbsp;<span class="nbDownload">{if ($download|@count) > 0 }<strong>{$download|@count}</strong> photo{if ($download|@count) > 1 }s{/if} to <a class="perm" href="main.php?dld">download</a>{/if}</span>
    <div class="advanced">
        <input type="text" name="sp" id="sp{$cpt}" size="40" value="{$pattern}"/>
        {html_radios  name="st" options=$searchTypes selected=$selectedSearchType}
        <br/>
        <label for="period{$cpt}">Period </label><input {if $periodError}class="error"{/if} type="text" name="per" id="period{$cpt}" value="{$selectedPeriod}"/>
        <input type="button" value="Choose" id="chperiod{$cpt}" class="chperiod"/>
&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="{$btKw}" id="chkeywords{$cpt}" class="chkeywords" title="{$btKwTitle}" {if $keywords|@count == 0}disabled="true"{/if}/>
        <div id="kwpopdiv{$cpt}">
            {html_options name="kw[]" options=$keywords selected=$selectedKeywords multiple="multiple" id=keywords$cpt class=keywords}
        </div>
        {html_radios  name="kwst" options=$kwSearchTypes selected=$selectedKwSearchType title="All or Any selected keywords"}
    </div>
</form>

{if $cpt == 1}
<div id="datemappopdiv"></div>
{/if}
