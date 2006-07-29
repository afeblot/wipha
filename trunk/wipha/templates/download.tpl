{include file="header.tpl" }
{if $nbSel > 0}
    {if $multipart == false}
        <script type="text/javascript">
        Behaviour.addLoadEvent( function() {ldelim}window.location="{$smarty.server.REQUEST_URI}";{rdelim} );
        </script>
    {/if}
    <p>Your download will begin automatically.</p>
    <p>Estimated size: {$size} Mo for {$nbDl} photo{if $nbDl > 1}s{/if}.</p>
    {if $nbForbidden != 0}
        <div class="warn">
            {$nbForbidden} photo{if $nbForbidden > 1}s have{else} has{/if} not been added to your
            download due to insufficient permission. This is to be solved by the WiPhA administrator.
        </div>
    {/if}
{else}
    <p>You have no photo selected for download.</p>
{/if}
{include file="footer.tpl"}
