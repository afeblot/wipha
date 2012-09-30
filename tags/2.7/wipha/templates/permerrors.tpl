{include file="header.tpl" }
<div class="warn">
{if $error == "changeperm"}
<p> wipha/<strong>changeperm</strong> has not the correct permission.
This could prevent you from parsing the database or displaying pictures properly.</p>
<p>Please correct this issue to keep going:</p>
<p><tt>chmod <strong>4755</strong> /Users/&lt;user&gt;/Sites/wipha/changeperm</tt></p>
{/if}
{if $error == "reach_albumdata"}
<p> Can't access to the iPhoto library data file. Possible causes:</p>
<ul>
    <li>The absolute path you set in the library configuration is wrong</li>
    <li>OS Permissions settings prevents Apache (www) to reach some of your
        files, although <em>changeperm</em> tried to set them properly</li>
</ul>
<p>Please correct this issue to keep going...</p>
{/if}
{if $error == "read_albumdata"}
<p> Found but can't read to the iPhoto library data file, although
<em>changeperm</em> tried to set it's permission properly</p>
<p>Please correct this issue to keep going...</p>
{/if}
</div>

{include file="footer.tpl"}
