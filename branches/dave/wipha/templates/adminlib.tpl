{include file="header.tpl"}
        <h2>Library <em>{$lib.name}</em> administration</h2>
            <div>
                <form action="{$smarty.server.SCRIPT_NAME}?" method="post" accept-charset="UTF-8">
                    <fieldset>
                        Name <input autocomplete="off" type="text" name="libn" size="20" value="{$lib.name}"/> 
                        Path <input autocomplete="off" type="text" name="libp" size="50" value="{$lib.path}" title="Absolute library path. Example: /Users/john/Pictures/iPhoto Library"/> 
{if $error_lib != ''}                 {include file="libs_error.tpl" error=$error_lib}{/if}
                        <input type="hidden" name="id" value="{$id}"/>
                    </fieldset>
                    <input type="submit" value="Change"/>
                    <input type="button" value="Cancel" onclick="document.location.href='admin.php'"/>
                </form>
            </div>
{include file="footer.tpl"}
