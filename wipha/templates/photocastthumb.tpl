{config_load file="wipha.conf" scope="global"}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>Web iPhoto Access</title>
        <link rel="icon" type="image/png" href="skin/{#skin#}/favicon.png" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <link rel="stylesheet" type="text/css" href="skin/{#skin#}/ydsf.css" />
        <link rel="stylesheet" type="text/css" href="skin/{#skin#}/wipha.css" />
{if $smarty.session.browser.app == 'OPERA' }
        <link rel="stylesheet" type="text/css" href="skin/{#skin#}/opera.css" />
{/if}
    </head>
    <body>
        <div class="photopage" align="center">
            <table border="0" cellspacing="10" cellpadding="0" align="center">
            <tr>
            <td align="center" valign="bottom">
                <div class="centercell"> <div class="ydsf"> <div class="inner">
                    <img class="photo" src="photocast?img={$path}&amp;lib={$libId}" alt="Thumb"/>
                </div> </div> </div>
            </td>
            </tr>
            </table>
        </div>
    </body>
</html>
