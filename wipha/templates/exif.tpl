{foreach from=$exif item="section" key=secname}
    {table_foreach from=$section item="data" cols=2
                   table_attr='cellpadding="0" cellspacing="1"'
                   th="$secname" th_attr="colspan=\"2\""
                   tr_attr='class="odd", class="even"'}{$data}{/table_foreach}
{/foreach}
