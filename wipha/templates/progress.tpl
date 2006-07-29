{math equation="ceil(275*p/100)" p=$percent assign="width"}
<div class="percents">{$percent}%&nbsp;complete</div><div class="blocks" style="width:{$width}px">&nbsp;</div>
{if $percent == 100}
    <script type="text/javascript">
    Behaviour.addLoadEvent( function() {ldelim}window.location="{$nexturl}";{rdelim} );
    </script>
    </body>
    </html>
{/if}
{php}
    flush();
{/php}
