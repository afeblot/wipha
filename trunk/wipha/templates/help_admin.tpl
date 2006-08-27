<h1>WIPHA ADMINISTRATION</h1>

<ul class="sum">
    <li><a href="#first">FIRST LOGIN</a></li>
    <li><a href="#deflog">CHANGING THE DEFAULT LOGIN</a></li>
    <li><a href="#lib">ADDING LIBRARIES TO SHARE</a></li>
    <li><a href="#newacc">CREATING NEW ACCESS</a></li>
    <li><a href="#confacc">CONFIGURING ACCESS</a></li>
    <li><a href="#alb">ALBUM POINT OF VIEW</a></li>
    <li><a href="#upd">UPDATE NOTIFICATION </a></li>
    <li><a href="#adv">ADVANCED CONFIGURATION</a></li>
</ul>

<a name="first"><h2>FIRST LOGIN</h2></a>

<p>You have installed WiPhA properly and just reached the login page.</p>

<div class="image">
<img src="img/login.jpg" alt="Login Page"/>
</div>

<p>You must declare yourself as the WiPhA administrator, so log in with the adminstrator
password. The default access is:</p>

<table class="indent">
<tr><td> Login:    </td><td><tt>admin</tt></td></tr>
<tr><td> Password: </td><td><tt>admin</tt></td></tr>
</table>

<p>Enter this login/password and click "Login"; then click the "Admin" item at
the right on the WiPhA menu bar to reach the main admin page.</p>

<div class="image">
<img src="img/menubar.jpg" alt="Menu Bar"/>
</div>


<a name="deflog"><h2>CHANGING THE DEFAULT LOGIN</h2></a>

<div class="image">
<img src="img/admadmin.jpg" alt="Admin Login"/>
</div>

<p>The first thing you should do is to change the default admin login and password. You do this
in the first frame, named <em>Administrator</em>. Replace <tt>admin </tt>by what you want,
change the password too, and click "Change" to apply the modification.</p>


<a name="lib"><h2>ADDING LIBRARIES TO SHARE</h2></a>

<div class="image">
<img src="img/admliblist.jpg" alt="Admin Libraries List"/>
</div>

<p>You must now add at least one iPhoto library to share. Enter a name, then
enter the <strong>absolute</strong> path to the library folder. For example, here's the
default location, with <tt>&lt;user&gt;</tt> being as always your short user
name:</p>
<p><tt>/Users/&lt;user&gt;/Pictures/iPhoto&nbsp;Library</tt></p>
<p>Each added library may be edited later to change its name or path; just click
its name in the list to get the library modification (rather simple) page.</p>

<p><img src="img/select.gif" alt="Select"/>&nbsp;You can directly load a library without going
through the user library selection page with this arrow.</p>

<p><img src="img/checked.gif" alt="Checked"/>&nbsp;The check icon shows the currently loaded
library,</p>

<p><img src="img/delete.gif" alt="Delete"/>&nbsp;and the red X deletes the corresponding
library (without asking any confirmation).</p>


<a name="newacc"><h2>CREATING NEW ACCESS</h2></a>

<div class="image">
<img src="img/admuserlist.jpg" alt="Admin Users List"/>
</div>

<p>You may create as many users as you want. For each user you will define
which iPhoto albums he/she will be allowed to access.<br/>
To add a user, just type a login and a password in the third frame named <em>Users</em>.
It will be added in the users list shown below. You will have to provide your user
with this login and password.</p>

<div class="note">There is also a "default" user named Guest which does not require
any login. This is the public access so you may decide not to allow any album for
Guest, or limit it to certain albums.</div>

<div class="warn">If you (wiPhA admin <strong>using Safari</strong>) applied a trick like
<a href="http://www.macosxhints.com/article.php?story=20041012233334948" target="_blank">
this one</a> to prevent the use of the <tt>autocomplete="off"</tt> option in
login/password fields, you will let Safari overwrite WiPhA default values; as a
result, you probably won't understand the behaviour of these admin pages and
you're likely to damage your own previous settings.</div>

<a name="confacc"><h2>CONFIGURING ACCESS</h2></a>

<p>Let's edit John's rights. A click on the John link gets his admin page:</p>

<div class="image">
<img src="img/useradm.jpg" alt="User Admin Page"/>
</div>

<p>You can change John's login and password the same way you did for the administrator before.
Note that you can modify just the login, or just the password, or both. If you don't want to
change the password, leave the field empty.</p>

<p>And at last, here's the interesting part: Select which albums <strong>of the
currently loaded library</strong> John will be allowed to see. When you're done,
don't forget to click the "Change" button. If you share more than one library,
you have to load each of them, and define the rights for each. This is where the
<img src="img/select.gif" alt="Select"/> arrow becomes useful. So, the fatest
recommended way is to choose the first library, set the rights for all users,
load the second library, set the rights, and so on...</p>

<div class="note">The first album item "<i>Allow all albums in this library</i>" lets
you grant full access to this library, including all future albums.</div>

<a name="alb"><h2>ALBUM POINT OF VIEW</h2></a>

<p>Back in the main admin page, the last frame named <em>Albums</em> lets you view and set which users
have access to an album.</p>

<div class="image">
<img src="img/admalbumlist.jpg" alt="Admin Albums List"/>
</div>

<p>In the same way, click on an iPhoto album and directly set who is allowed to access it </p>

<div class="image">
<img src="img/albumadm.jpg" alt="Album Admin Page"/>
</div>

<div class="note">For your information, all of these parameters are stored in a
configuration file in the directory <tt>$HOME/Sites/wipha/data</tt>.</div>

<a name="upd"><h2>UPDATE NOTIFICATION</h2></a>

<p>If a new version of WiPhA becomes available, you'll be noticed about it when
you log in (as administrator only, of course).</p>

<a name="adv"><h2>ADVANCED CONFIGURATION</h2></a>

<p>Some additional options may be set in the wipha config file <tt>configs/wipha.conf</tt>.
<ul>
    <li><tt>cachesize = 100000000 </tt>: Cache maximum size, in bytes (default: 100Mo).
    Oldest images will be removed to keep the cache under the specified limit. Set to 0
    to have an infinite cache size.</li>
</ul>
