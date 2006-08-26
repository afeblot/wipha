<h1>WIPHA USAGE</h1>

<ul class="sum">
    <li><a href="#libsel">LIBRARY SELECTION</a></li>
    <li><a href="#search">HOW TO SEARCH</a>
        <ul>
            <li><a href="#searchsimple">Simple Search Bar</a></li>
            <li><a href="#searchadv">Advanced Search Bar</a></li>
            <li><a href="#datemap">Date Map</a></li>
            <li><a href="#keywords">Keywords</a></li>
        </ul></li>
    <li><a href="#browse">BROWSING THE RESULT</a>
        <ul>
            <li><a href="#thumbs">Thumb mode</a></li>
            <li><a href="#slide">Slideshow mode</a></li>
        </ul></li>
    <li><a href="#dlownload">DOWNLOAD SOME PICTURES</a></li>
    <li><a href="#import">IMPORT THE DOWNLOADED PICTURES</a></li>
    <li><a href="#photocast">SUBSCRIBE TO A PHOTOCAST ALBUM</a></li>
</ul>

<div class="warn"> <strong>Opera users: </strong>The Opera cache system will prevent WiPhA to
refresh it's pages. Please configure Opera to always reload the page: Preferences, Advanced tab,
History, Check documents: always. </div>

<a name="libsel"><h2>LIBRARY SELECTION</h2></a>

<p>When you first log in, if more than one library are shared, you will be presented a page to
choose which one you want to look at. It will then be loaded, and you will get the default
simple search bar (if only one library is available, it is automaticaly loaded).</p>

<p>At any time, you can click the "Select Lib" button in the menu bar to choose another
library.</p>

<a name="search"><h2>HOW TO SEARCH</h2></a>

<a name="searchsimple"><h3>Simple Search Bar</h3></a>

<p>Once a library has been chosen, or when you click "New search" in the menu bar, you get a
simple search bar in which you can specify which album you want to look at. By default, the
entire library album is  selected.</p>

<div class="image">
<img src="img/sbsimple.jpg" alt="Simple Search Bar"/>
</div>

<div class="note"> If the library owner loves you, he gave you access to his entire library. Otherwise, this
library album will only contain all photos you can access. </div>

<div class="note"> The <em>Selected for download</em> album is a special one. See the download paragraph.
</div>

<a name="searchadv"><h3>Advanced Search Bar</h3></a>
<p>If you need to refine your search, click the <em>Advanced</em> link to get the full search bar
capability. You can know:</p>

<div class="image">
<img src="img/sbadvanced.jpg" alt="Advanced Search Bar"/>
</div>

<ul>
<li> filter this album by looking for some text in the photo captions and comments. You may search
for all typed words and for at least one of them. The search is not case sensitive. If you need to
perform a more complex search, you can use the regexp option (example to find photos whose caption
or comment end with a number: <tt>[0-9]+$</tt>).<br/>
If you don't want to search for texts, just leave the field empty and you'll get all
pictures.</li>
<li><p>filter on some library keywords.</p></li>
<li><p>specify a period of time to limit the search. Here again, if you leave this field empty, you
won't filter on dates.</p>
    <p>Examples of recognized period formats:</p>
    <ul>
        <li> 12 January 2006 </li>
        <li> 12 Jan 2006 </li>
        <li> Jan 2006 </li>
        <li> 2006 </li>
        <li> 2004 - 2006 </li>
        <li> 17 Oct 2003 to Jan 2006 </li>
        <li>  17 to 23 Oct 2005</li>
        <li> Mar - Jul 2004 </li>
    </ul>
</li>
</ul>

<a name="datemap"><h3>Date Map</h3></a>

<p>Clicking the period "Choose" button will popup the date map window for an easier
period selection.</p>

<div class="image">
<img src="img/datemap.jpg" alt="Date Map"/>
</div>

<p>If you don't know exactly the period of what you are looking for, use the datemap. It will
display the years in which the selected album contains pictures. The bigger the block, the
higher the number of pictures is. If you let your mouse still on a block, it will display a
tooltip with the number of pictures in that block.</p>

<p>Clicking on a year will then open it and display the month level information. At the same
time, the block will turn to blue, meaning that you have selected it. The current period is
always displayed in the yellow field at the bottom left corner of the window. Now you may
click a month and get the day level the same way.</p>

<p>If you want to select an interval of dates, just click an other block. The entire interval
will turn to blue. In fact, the data map always chooses the interval between your last click
and the previous one. Unless this is not logic. For example, if you choose Mar 2005, then
2005, it will just select 2005.</p>

<p>Clicking in the white zone of the datamap will just select nothing a first time, and a
second click will of course clean your selection.</p>

<p><strong>Example</strong>: You want to select the period Aug 2001 to 23rd Apr 2004:
<ul>
    <li>Click <strong>2001</strong> (all 2001 is selected)</li>
    <li>Click <strong>Aug</strong> (precise the choice: only Aug 2001 is selected)</li>
    <li>Click <strong>2004</strong> (you set an interval: from Aug 2001 to all 2004)</li>
    <li>Click <strong>Apr</strong> (precise the end date of the interval to Apr 2004)</li>
    <li>click <strong>23</strong> (precise a little bit more...)</li>
</ul>
</p>

<p>When you have chosen your period, click OK. If you want to give up and not update the
search bar, just click somewhere else in the page.</p>

<a name="keywords"><h3>Keywords</h3></a>

<p>Clicking the Keywords button will popup a list of all used keywords in the current library.
Select as many of them as you like (<kbd>Cmd</kbd>+Click), and choose if you want to display
photos tagged with any of them, or only photos tagged with all of them. The button also updates
to let you know how many keywords are selected.</p> <p>If no keyword has been used in this
library, the button is disabled.</p>

<div class="image">
<img src="img/keywords.jpg" alt="Keywords"/>
</div>


<a name="browse"><h2>BROWSING THE RESULT</h2></a>

<a name="thumbs"><h3>Thumb mode</h3></a>

<p>If you found more pictures than you accepted on one page, a pager lets you go through all
pictures. The number of pictures found is displayed in the bottom of the search bar. At any time,
you can change your search parameters which remain in the search bar, unless you click "New
search" again.</p>

<p><img src="img/mail.gif" alt="Mail"/>&nbsp;This envelope let you send a mail with a link to the
current displayed page.</p>

<p><img src="img/zoom.gif" alt="Full Size"/>You can get the full size picture by clicking the
small magnifying glass in the bottom left corner of the thumb image.</p>

<p><img src="img/video.png" alt="Video"/> In case of a movie, you'll get the camera icon
to download and watch it in your browser.</p>

<p><img src="img/slideshow.png" alt="Slide"/> and the slide icon brings you into the slideshow
mode.</p>

<div class="image">
<img src="img/photopage.jpg" alt="Photos Page"/>
</div>

<div class="note"> If you enlarge your browser window, the thumb page will use that extra space
<em>(you need to refresh the page)</em>. For instance on a 24" display:
<div class="image">
<img src="img/sixcol.jpg" alt="Six columns thumb page"/>
</div>
</div>

<a name="slide"><h3>Slideshow mode</h3></a>

<p>In this mode, you can display the photos you found from your search parameters at a larger size
and go forward and backward among them.</p>

<p><img src="img/thumbs.gif" alt="Thumbs"/> If you want to return to the thumb page, click the grid
icon between the arrows.</p>

<p><img src="img/play.png" alt="Play"/> The start button under the photo allows you to enter the
automatic slideshow mode. You can set how long you want each photo to be displayed, and you can
even change this time during the slideshow. 5 secondes is the minimum allowed. To stop the
slidewhow, click the pause button (replaces the start button when playing).</p>

<div class="image">
<img src="img/slide.jpg" alt="Slide Page"/>
</div>

<p>You may click the <a href="#">Show Photo data</a> link to display all EXIF values:</p>

<div class="image">
<img src="img/exif.png" alt="Exif Tables"/>
</div>


<a name="dlownload"><h2>DOWNLOAD SOME PICTURES</h2></a>

<p>Click on an image to select it for download. It's background will turn to yellow. The number of
selected pictures is dislayed in the search bar. You can easily select or unselect all
pictures of the current page using the 2 links. If you selected pictures from multiple
searches or pages, you can use the special <em>Selected for download</em> album to display them
all, and maybe remove some of them.</p>
<p>Of course, you can also select photos during the slidehsow. This is a simple and easy way to
view the photos with enough details and directly select those you like.</p>

<div class="image">
<img src="img/selectdld.jpg" alt="Download Selection"/>
</div>

<p>The number of photos selected for download is always displayed in the search bar: "<i><b>2</b>
photos to <a href="#">download</a></i>". To finally get these pictures, just click the download
link and WiPhA will generate a zip with these pictures and the extra stuff described just below.
The download will begin immediately.</p>

<div class="note">
If WiPhA can't access some pictures for permission reasons, it will notice you on the download
page. This might happen for iPhoto 06 owners which use the new feature "Let my images where they
are instead of copying them in my iPhoto Library". Anyway, should you have this kind  of problem,
please contact the Library owner and send him the index file from the zip. This file contains
a listing of your selected pictures, with error messages on unaccessible ones.
</div>


<a name="import"><h2>IMPORT THE DOWNLOADED PICTURES</h2></a>

<p>Once you have downloaded and uncompressed your zip, you must have 2 files and a Photos
folder. If you are a poor PC or Linux user (well you're not so poor on Linux), you're on
your own to do what you want whith the pictures, in the Photos folder. But if you have a
mac, here's an extra for you, to import the photos in your iPhoto and set their captions and
comments. </p>

<div class="image">
<img src="img/zipcontent.jpg" alt="Zip Content"/>
</div>

<p>Double click on the <tt>import.command</tt> file. iPhoto will start here if it's not already
open. This can take some time, I know... A new album will be created in iPhoto, and the pictures
will get imported in this album, and tagged. A popup will notice you about the end of the
import. When you have checked everything is ok, you can trash the script and the pictures.</p>

<div class="image">
<img src="img/importdone.jpg" alt="Import done"/>
</div>

<div class="note">Running the import script opens the Terminal application (if not already),
and a new window. You have to close this window by yourself after the import.
<div class="image">
<img src="img/termwin.jpg" alt="Terminal window"/>
</div>
</div>



<a name="photocast"><h2>SUBSCRIBE TO A PHOTOCAST ALBUM</h2></a>

<p>If you don't know what photocast means, Apple <a
href="http://www.apple.com/ilife/iphoto/features/photocasting.html"
target="_blank">explains it</a> clearly. WiPha automatically photocasts every album of
every shared library.</p>

<p><img src="img/photocast.png" alt="Photocast"/> When you are displaying some pictures,
either in a thumbnails page or during a slideshow, the photocast icon appears in the menu
bar, and according to your browser, a RSS icon can switch on the in the address
bar.</p>

<div class="image">
<img src="img/barrss.jpg" alt="Menu bar with Photocast available"/>
</div>

<p>Right-click on the photocast icon (which is a link) or <kbd>Cmd</kbd>-Click if you have
a one button mac mouse, and "Copy link location". Then use this url to subscribe to the
photocast in your RSS agregator. To subscribe in iPhoto 6: menu File, Subscribe a
photocast (<kbd>Cmd</kbd>-<kbd>U</kbd>) and copy the URL. iPhoto will then begin downloading
all fullsize photos of the album (this may be long), and will keep the album up to date.</p>

<div class="note">Photocast full size pictures are compressed to reduce bandwidth
usage and improve download speed.</div>
