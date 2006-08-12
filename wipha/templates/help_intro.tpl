<h1>SUMMARY</h1>

<ul class="sum">
    <li><a href="#goal">GOAL: Share your iPhoto Libraries</a></li>
    <li><a href="#mean">MEAN</a></li>
    <li><a href="#key">KEY FEATURES</a>
        <ul>
            <li><a href="#web">Full featured yet easy web access to your iPhoto Libraries</a></li>
            <li><a href="#phcast">Photocast your albums for free</a></li>
            <li><a href="#exp">Export pictures</a></li>
            <li><a href="#priv">Enforce your privacy</a></li>
            <li><a href="#std">Standard compliant</a></li>
            <li><a href="#light">Light and fast</a></li>
        </ul></li>
    <li><a href="#req">REQUIREMENTS</a>
        <ul>
            <li><a href="#reqsrv"> For you, on the server side</a></li>
            <li><a href="#reqcli"> For your users (and you as a user)</a></li>
        </ul></li>
</ul>

<a name="goal"><h2>GOAL: Share your iPhoto Libraries</h2></a>

Share your iPhoto Libraries over the internet. As soon as you have uploaded pictures from
your camera to your library, you want these pictures to get available to your friends,
family or whoever. WiPhA provides a powerful, beautiful and easy to use <strong>web
interface</strong> to your libraries as well as <strong>automatic photocast</strong> of
every single album of yours. Moreover, people can easily <strong>download a bunch of
pictures</strong> and import them in their own iPhoto library with your captions and
comments (for your lucky friends also owning a mac).<br/>

WiPhA intends to be light for your server in terms of cpu and memory usage, and to provide
extensive search methods to easily browse your pictures, even in a big library.


<a name="mean"><h2>MEAN</h2></a>

Your MacOSX standard installation includes a web server which will be used to let others access
to your pictures through their web browser. After having installed WiPhA, all you have to do is
configure your friends' rights and give them the URL to connect to. You don't even need to be
logged in, as long as your computer is on, connected to the internet, and it's web server is
still running.

<a name="key"><h2>KEY FEATURES</h2></a>

<div class="image">
<img src="img/photopage.jpg" alt="photopage"/>
</div>

<a name="web"><h3>Full featured yet easy web access to your iPhoto Libraries</h3></a>
<ul>
    <li> Extensive search methods (albums, captions, comments, dates, keywords) </li>
    <li> Unique "date map" feature to get a quick overview of the entire library and select the period to browse</li>
    <li> Manual or automatic slideshow mode </li>
    <li> Manage multiple libraries </li>
    <li> Send by mail links to the photos you have displayed and to the associated photocast album</li>
    <li> Display the full size pictures and EXIF data </li>
    <li> Warning on available updates </li>
</ul>

<a name="phcast"><h3>Photocast your albums for free</h3></a>
<ul>
    <li>Every Album is automatically photocast (rss feed) without requiring a .mac account</li>
    <li>Photocast may be used with iPhoto 6 or any RSS reader for Windows users</li>
</ul>

<a name="exp"><h3>Export pictures</h3></a>
<ul>
    <li> Select pictures and download them as a single zip </li>
    <li> Import them in an other iPhoto library and keep their captions and comments with the provided applescript </li>
</ul>

<a name="priv"><h3>Enforce your privacy</h3></a>
<ul>
    <li> Set different logins with different right access </li>
    <li> Web access and photocast albums are both protected (with the same logins)</li>
    <li> No direct access to files (all data are filtered before being delivered)</li>
</ul>

<a name="std"><h3>Standard compliant</h3></a>
<ul>
    <li> XHTML 1.0 </li>
    <li> CSS2 </li>
    <li> RSS 2.0 </li>
    <li> Web 2.0 - Ajax </li>
</ul>

<a name="light"><h3>Light and fast</h3></a>
<ul>
    <li> Ajax technology used to limit bandwidth usage and get faster responses</li>
    <li> No need to have iPhoto running, or even to be logged in </li>
    <li> No background process will be installed in your account </li>
    <li> WiPhA relies on the iPhoto data (but doesn't modify them) as much as possible to avoid generating new thumb images </li>
    <li> Generated data are cached and reused. </li>
</ul>


<div class="image">
<img src="img/datemap.jpg" alt="Date Map"/>
<p>Date map of an entire library</p>
</div>

<a name="req"><h2>REQUIREMENTS</h2></a>

<a name="reqsrv"><h3> For you, on the server side</h3></a>
<ul>
    <li> iPhoto Library version 4, 5 or 6 </li>
    <li> Mac OSX 10.4.x (tested) or 10.3.x (not tested) </li>
    <li> Mac OSX default PHP4 or Entropy PHP5 package (tested)</li>
</ul>

<a name="reqcli"><h3> For your users (and you as a user)</h3></a>
<ul>
    <li> A W3C compliant web browser with Javascript support enabled (tested on 
         Safari, Shiira, Camino, Firefox, Opera). IE roughly works but the result is
         ugly, and a few features are missing). </li>
         
    <li> and/or a RSS aggregator supporting basic HTTP authentication to
         subscribe to photocasts. Tested with iPhoto&nbsp;6, NetNewsWire,
         Safari, Thunderbird. </li> 
</ul>

<div class="warn"> As of iPhoto 06, you can have a library which does not contain your
pictures, but just owns aliases to the pictures which may be stored anywhere you want.
WiPhA follows these aliases to provide the pictures to your users, but you may encounter
some permission issues. Please remember that WiPhA runs as the "Web server" user, and that
<strong>you</strong> must ensure it can access your files.</div>
