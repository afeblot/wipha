<?php

/*
Copyright Alexandre Feblot, 2005-2009
http://globs.org

This software is a computer program whose purpose is to let people share
their iPhoto Library on the web, and let their users easily download a
bundle of pictures.

This software is governed by the CeCILL  license under French law and
abiding by the rules of distribution of free software.  You can  use, 
modify and/ or redistribute the software under the terms of the CeCILL
license as circulated by CEA, CNRS and INRIA at the following URL
"http://www.cecill.info". 

As a counterpart to the access to the source code and  rights to copy,
modify and redistribute granted by the license, users are provided only
with a limited warranty  and the software's author,  the holder of the
economic rights,  and the successive licensors  have only  limited
liability. 

In this respect, the user's attention is drawn to the risks associated
with loading,  using,  modifying and/or developing or reproducing the
software by the user in light of its specific status of free software,
that may mean  that it is complicated to manipulate,  and  that  also
therefore means  that it is reserved for developers  and  experienced
professionals having in-depth computer knowledge. Users are therefore
encouraged to load and test the software's suitability as regards their
requirements in conditions enabling the security of their systems and/or 
data to be ensured and,  more generally, to use and operate it in the 
same conditions as regards security. 

The fact that you are presently reading this means that you have had
knowledge of the CeCILL license and that you accept its terms.
*/

require_once('smarty.php');
require_once('iphotoparser.php');
require_once('users.php');
require_once('libraries.php');
require_once('photoszip.php');

require_once('http-conditional.php');

//=============================================================================
class Wipha {

    var $smarty = null;
    var $filterParam = ''; // to communicate with the array_filter callback which has no parameter
    var $availableNbRows = null;
    var $availableSearchTypes = null;
    var $availableKwSearchTypes = null;
    //----------------------------------------------
    function Wipha(&$get) {
        $this->availableNbRows = array(2, 3, 4, 5, 10, 20, 30, 50);
        $this->availableSearchTypes = array('l'=>'All words',
                                            'y'=>'Any word',
                                            'r'=>'<a href="http://www.regular-expressions.info/quickstart.html" target="_blank">Regexp</a> (perl style)'
                                            );
        $this->availableKwSearchTypes = array('l'=>'All', 'y'=>'Any');
    
        $this->smarty = new SmartyApp();

        if ( ! $_COOKIE['searchbarType']) {
            setcookie('searchbarType', 'simple', 0, '/');
        }
        if ( ! $_COOKIE['nbRows']) {
            setcookie('nbRows', $this->availableNbRows[1], time()+3600*24*365, '/');
        }
        if ( ! $_COOKIE['exif']) {
            setcookie('exif', "no", time()+3600*24*365, '/');
        }
        
        // in session because used in header.tpl
        $_SESSION['nblibrary'] = count($this->authorizedLibs());

        // If the $get array contains the lib param, let's load this library right now (if it's not)
        // and suppress the param from the array
        // This is used for direct url to a thumb or slideshow page, or for the W3C validator or RSS reader
        if (isset($get['lib'])) {
            if ($get['lib']!=$_SESSION['library']['id']) {
                $this->selectLib($get['lib'], 'uri');
            }
            unset($get['lib']); // because of the action selection mechanism
            unset($_REQUEST['lib']); // to remove it from the pager
        }
        
        $this->versionCheck();

        $this->contentDisplayed('update');
    }

    //----------------------------------------------
    function versionCheck() {
        if ($_SESSION['is_admin'] && ! isset($_SESSION['versionCheck'])) {
            $this->smarty->config_load('wipha.conf');
            $current = $this->smarty->get_config_vars('version');
            $home = $this->smarty->get_config_vars('home');
            
            // Some systems don't allow file_get_contents
            //$ser = file_get_contents("$home/update.txt");
            $home = preg_replace("/http:\/\//", "", $home);
            $fp = fsockopen($home, 80, $errno, $errstr, 30);
            if (!$fp) {
                echo "$errstr ($errno)<br />\n";
            } else {
                $out = "GET /update.txt HTTP/1.1\r\n";
                $out .= "Host: $home\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($fp, $out);
                while (!feof($fp)) {
                    $ser = fgets($fp);
                }
                fclose($fp);
            }

            $last = unserialize($ser);
            $last = $last['wipha'];
            $_SESSION['versionCheck'] = array('time'=>time());
            if (version_compare($current, $last['ver'], "<")) {
                $_SESSION['versionCheck']['newVer'] = $last;
            }
        }
        $interval = time() - $_SESSION['versionCheck']['time'];
        if (isset($_SESSION['versionCheck']['newVer']) && $interval <20) {
                $this->smarty->assign('newVer', $_SESSION['versionCheck']['newVer']);
                $this->smarty->assign('time', 20-$interval);
        }
    }

    //----------------------------------------------
    function checkForLibrary() {
        if ( ! isset($_SESSION['library'])) {
            $authLibs = $this->authorizedLibs();
            if (count($authLibs)==0) {
                $this->smarty->display('nolib.tpl');
                exit;
            } elseif ((count($authLibs)==1)||($_SESSION['browser']['app']=='W3C_VALIDATOR')) {
                reset($authLibs);
                list($id, $foo) = each($authLibs);
                $this->selectLib($id, 'uri');
            } else {
                $this->selectLib(NULL, 'uri');
                exit;
            }
        }
    }

    //----------------------------------------------
   function loadLibrary($libId, $srcUrl=NULL) {
        $_SESSION['photos'] = array();
        $_SESSION['albums'] = array();
        $_SESSION['keywords'] = array();
        $_SESSION['library'] = NULL;

        $libs = new Libraries();
        $lib = $libs->lib($libId);
        $libFile = $lib['path'].'/AlbumData.xml';

        // Permission pre-checks
        $this->smarty->config_load('wipha.conf');
        $demo = $this->smarty->get_config_vars('demo');

        // don't do this test on a Linux web server
        if ( ! isOnLinux()) {
            // Check that changeperm is chmod u+s
            $stat = stat('./changeperm');
            if (($stat['mode']&04555)!=04555) {
                $this->smarty->assign('error', 'changeperm');
                $this->smarty->display('permerrors.tpl');
                return false;
            } else {
                // This bloody iPhoto always sets it's AlbumData.xml to rw-------
                $this->correctAlbumDataPermissions($libFile);
            }
        }
        if ( ! @is_file($libFile)) {
            $this->smarty->assign('error', 'reach_albumdata');
            $this->smarty->display('permerrors.tpl');
            return false;
        }
        if ( ! @is_readable($libFile)) {
            $this->smarty->assign('error', 'read_albumdata');
            $this->smarty->display('permerrors.tpl');
            return false;
        }

        set_time_limit(0); // No time limit during loading. Large lib may be loooonnnnng


        // ****************************************************************
        // User INdependant part: process iPhoto file or get it from cache
        // ****************************************************************

        $dataFilecache = "data/cache/iphoto_$libId.ser";
        $libWasCached=false;
        if ( (file_exists($dataFilecache))&&(filemtime($libFile)<filemtime($dataFilecache))) {
            list(
                $_SESSION['photos'],
                $_SESSION['albums'],
                $_SESSION['keywords']
                ) = retrieve($dataFilecache);
                $libWasCached = true;
        } else {
            if ( ! $this->parseDataFile($libFile, $srcUrl)) {
                return false;
            }

            if ( ! $demo) {
                $this->correctImagesPermissions($libFile, $libId);
            }

            // Compute the size of the displayed Thumbs
            // max size = 240 for iPhoto 5 (this is fine) but iPhoto 6 thumbs are too big.
            foreach ($_SESSION['photos'] as $id=>$photo) {
                $r = $photo['Aspect Ratio'];
                if ($r>1) {
                    $w = 240;
                    $h = round($w/$r);
                } else {
                    $h = 240;
                    $w = round($h*$r);
                }
                $_SESSION['photos'][$id]['width'] = $w;
                $_SESSION['photos'][$id]['height'] = $h;
            }

            // Add a special album containing all photos the user wants to download
            $this->createAlbum('download', '* Selected for download', array());
            
            store(array(
                        $_SESSION['photos'],
                        $_SESSION['albums'],
                        $_SESSION['keywords']
                        ), $dataFilecache);
        }


        // ********************************************************************
        // User dependant part: create restricted master album, filter keywords
        // ********************************************************************

        $users = new Users();
        $userFilecache = "data/cache/user_".$_SESSION['user']."_$libId.ser";
        if ( (file_exists($userFilecache)) &&
             (filemtime($libFile)<filemtime($userFilecache)) &&
             // cache must be older than the last albums settings modification
             ($users->userAlbumsTS($libId, $_SESSION['user'])<filemtime($userFilecache))
            ) {
            list(
                $_SESSION['master'],
                $masterName,
                $photoIds,
                $_SESSION['keywords']
                ) = retrieve($userFilecache);
                if ($masterName) {
                    $this->createAlbum('all', ' '.$masterName, $photoIds);
                }
        } else {
            // For non admin members, if the master album is not authorized,
            // add an album named as the master album and containing all authorized photos

            // Get the Master album id
            foreach ($_SESSION['albums'] as $id=>$album) {
                if ($album['Master']=='TRUE') {
                    $_SESSION['master'] = $id;
                    break;
                }
            }
            if ( ! $_SESSION['is_admin'] && ! $users->hasFullAccess($libId, $_SESSION['user'])) {
                // If the master is not authorized for this user
                $userAlbums = $users->userAlbums($libId, $_SESSION['user']);
                if ( ! in_array($_SESSION['master'], $userAlbums)) {
                    $photoIds = array();
                    foreach ($userAlbums as $albumId=>$album) {
                        if (is_numeric($albumId)) {
                            foreach ($_SESSION['albums'][$album]['PhotoIds'] as $id) {
                                if ( ! in_array($id, $photoIds)) {
                                    $photoIds[] = $id;
                                }
                            }
                        }
                    }
                    $masterName = isset($_SESSION['master']) ? $_SESSION['albums'][$_SESSION['master']]['AlbumName'] : "Library";
                    $this->createAlbum('all', ' '.$masterName, $photoIds);
                    $_SESSION['master'] = 'all';
                }
            }

            // Compute the nb of (authorized) photos for each keywords. Remove useless keywords
            foreach ($_SESSION['albums'][$_SESSION['master']]['PhotoIds'] as $photoId) {
                if (is_array($_SESSION['photos'][$photoId]['Keywords'])) {
                    foreach ($_SESSION['photos'][$photoId]['Keywords'] as $idkw) {
                        $foundkw[$idkw]++;
                    }
                }
            }
            foreach ($_SESSION['keywords'] as $idkw=>$kw) {
                if ($foundkw[$idkw]) {
                    $_SESSION['keywords'][$idkw] .= " (".$foundkw[$idkw].")";
                } else {
                    unset($_SESSION['keywords'][$idkw]);
                }
            }
            
            store(array(
                        $_SESSION['master'],
                        $masterName,
                        $photoIds,
                        $_SESSION['keywords']
                        ), $userFilecache); 
        }

        // All is ok, memorize the loaded  lib
        $_SESSION['library'] = $lib;
        $_SESSION['library']['path'] .= '/';

        set_time_limit(30); // Restore standard value for the end of the script

        return $libWasCached ? "cached" : true;
    }

    //----------------------------------------------
    function silentLoad() {
        return ( isset($_SESSION['browser']['no_interaction']) || isset($_SERVER['PHP_AUTH_USER']) );
    }

    //----------------------------------------------
    function parseDataFile($libFile, $srcUrl) {
        $parser = new IphotoParser($libFile);
        $silent = $this->silentLoad() || empty($srcUrl);
        $versionOk = true;
        $version = "";
        if ( ! $silent) {
            $this->smarty->display('loading.tpl');
            $this->smarty->assign('versionOk', $versionOk);
            $this->smarty->assign('percent', 0);
            $this->smarty->assign('nexturl', $srcUrl);
            $this->smarty->display('progress.tpl');
            while (ob_get_level()) { ob_end_flush(); }
            if (ob_get_length() === false) { ob_start(); }
            ob_flush(); flush();
        }

        $oldPercent = 0;
        do {
            $percent = $parser->parseLittle();
            $version = $parser->getVersion();
            if (!empty($version) and ($version<"4.0.0")) {
                $versionOk = false;
            }

            if ( ! $silent) {
                if ($percent-$oldPercent>0.1 || $percent==1.0 || ! $versionOk) {
                    $this->smarty->assign('versionOk', $versionOk);
                    $this->smarty->assign('version', $version);
                    $this->smarty->assign('percent', ceil($percent*100));
                    $this->smarty->display('progress.tpl');
                    $oldPercent = $percent;
                    ob_flush(); flush();
                }
            }
        } while (($percent<1.0) and $versionOk);
        ob_end_flush();

        $parser->getResults($_SESSION['photos'], $_SESSION['albums'], $_SESSION['keywords']);
        $parser->free();

        return $versionOk;
    }

    //----------------------------------------------
    function createAlbum($id, $name, $photoIds) {
        $_SESSION['albums'][$id]['AlbumId'] = $id;
        $_SESSION['albums'][$id]['AlbumName'] = $name;
        $_SESSION['albums'][$id]['PhotoIds'] = $photoIds;
    }

    //----------------------------------------------
    function correctAlbumDataPermissions($libFile) {
        $all = array();
        $list = explode('/', $libFile);
        for($i=1;$i<count($list); $i++) {
            array_push($all, implode('/', array_slice($list, 1, $i)));
        }
        $pipe = popen("./changeperm", "w");
        foreach($all as $path) fwrite($pipe, "/$path\n");
        pclose($pipe);
    }

    //----------------------------------------------
    function correctImagesPermissions($libFile, $libId) {
        // Do this only if the Library has been modified since the last correction
        $permFile = "data/lastPermCheck_$libId.dat";
        if (file_exists($permFile)) {
            if (@filemtime($libFile) < @filemtime($permFile)) {
                return;
            }
        }
        $um = umask(2);
        touch($permFile);
        umask($um);
        
        $dirs = array();
        $files = array();
        foreach ($_SESSION['photos'] as $photo) {
            $thumb = $photo['ThumbPath'];
            $file = $photo['ImagePath'];
            $thumbdir = dirname($thumb);
            $filedir = dirname($file);
            if ( ! in_array($thumbdir, $dirs)) array_push($dirs, $thumbdir);
            if ( ! in_array($filedir,  $dirs)) array_push($dirs, $filedir);
            array_push($files, $thumb);
            array_push($files, $file);
        }

        $alldirs = array();
        foreach($dirs as $dir) {
            $list = explode('/', $dir);
            for($i=1;$i<=count($list); $i++) {
                array_push($alldirs, implode('/', array_slice($list, 0, $i)));
            }
        }
        $alldirs = array_unique($alldirs);
        sort($alldirs);

        $root = dirname($libFile);
        $pipe = popen("./changeperm", "w");
        foreach($alldirs as $path) fwrite($pipe, "$root/$path\n");
        foreach($files as $path) fwrite($pipe, "$root/$path\n");
        pclose($pipe);
    }

    //----------------------------------------------
    function assignSearchFormInfos($album=NULL, $pattern=NULL, $searchType=NULL, $nbRows=NULL,
                                   $period=NULL, $keywords=NULL, $kwSearchType=NULL) {

        if ( ! empty($nbRows)) {
            setcookie('nbRows', $nbRows, time()+3600*24*365, '/');
        }

        // Get a list of all authorized albums
        $users = new Users();
        if ($_SESSION['is_admin'] || $users->hasFullAccess($_SESSION['library']['id'], $_SESSION['user'])) {
            $albumIds = array_keys($_SESSION['albums']);
        } else {
            $albumIds = $users->userAlbums($_SESSION['library']['id'], $_SESSION['user']);
            $albumIds[] = 'download';
            if (isset($_SESSION['albums']['all'])) {
                array_unshift($albumIds, 'all');
            }
        }

        // Group albums according to their parent name (if they have one). Anyway, each album without parent
        // is added to a 'fake' parent of it's own name.
        foreach ($albumIds as $albumId) {
            $nbPhotos = count($_SESSION['albums'][$albumId]['PhotoIds']);
            if ($nbPhotos>0) {
                $parentId = $_SESSION['albums'][$albumId]['Parent'];
                $parentName = $_SESSION['albums'][$parentId]['AlbumName'];
                $label = $_SESSION['albums'][$albumId]['AlbumName'] . ' (' . $nbPhotos . ')';
                if (isset($parentName)) {
                    $albums[$parentName][$albumId] = $label;
                }  else {
                    $name = $_SESSION['albums'][$albumId]['AlbumName'];
                    $albums[$name][$albumId] = $label;
                }
            }
        }

        // Clean the album groups so that parent with a single child having the same name becomes this child back.
        $albumsTmp = $albums;
        unset($albums);
        foreach ($albumsTmp as $name=>$childAlbums) {
            if (count($childAlbums)==1) {
                list($albumId, $label) = each($childAlbums);
                if ( !empty($label) and !empty($name)) {    # Hide the strstr warning which appears in some 'strange' occasions
                    if (strstr($label, $name)!==false) {
                        $albums[$albumId] = $label;
                    } else {
                        $albums[$name] = $childAlbums;
                    }
                }
            } else {
                $albums[$name] = $childAlbums;
            }
        }

        $nbCols = $this->nbThumbCols();
        $availablePageSizes = array();
        foreach($this->availableNbRows as $rn) {
            array_push($availablePageSizes, $rn*$nbCols);
        }

        $this->smarty->assign_by_ref('albums', $albums);
        $this->smarty->assign('selectedAlbum', $album ? $album : 'all');    // 'all' doesn't exist for admin -> fallback to 1st = Phototheque
        $this->smarty->assign('nbRows', $this->availableNbRows);
        $this->smarty->assign('pageSizes', $availablePageSizes);
        $this->smarty->assign('selectedNbRows', $nbRows ? $nbRows : $_COOKIE['nbRows']);
        $this->smarty->assign('searchTypes', $this->availableSearchTypes);
        $this->smarty->assign('selectedSearchType', $searchType ? $searchType : 'l');
        $this->smarty->assign('pattern', $pattern);
        $error = $this->normalizePeriod($period, $foo);
        $this->smarty->assign('selectedPeriod', $period);
        $this->smarty->assign('periodError', $error);
        
        if (count($_SESSION['keywords'])) {
            switch(count($keywords)) {
                case 0:  $btKw = "Keywords"; break;
                case 1:  $btKw = "1 Keyword"; break;
                default: $btKw = count($keywords)." Keywords"; break;
            }
        } else {
            $btKw = "No Keyword";
        }
        
        $btKwTitle = array();
        if (is_array($keywords)) {
            foreach($keywords as $kw) {
                array_push($btKwTitle, preg_replace('/\\s+\\(\\d+\\)/', '', $_SESSION['keywords'][$kw]));
            }
        }
        $btKwTitle = implode(', ', $btKwTitle);

        $this->smarty->assign('btKw', $btKw);
        $this->smarty->assign('btKwTitle', $btKwTitle);
        $this->smarty->assign('keywords', $_SESSION['keywords']);
        $this->smarty->assign('selectedKeywords', $keywords);
        $this->smarty->assign('kwSearchTypes', $this->availableKwSearchTypes);
        $this->smarty->assign('selectedKwSearchType', $kwSearchType ? $kwSearchType : 'l');
        
        if ( ! empty($album) && $album != "download") {
            $url = urlWithLib($_SESSION['library']['id']);
            $urlPhotoCast = absUrl('photocast.php?lib='.$_SESSION['library']['id'].'&al='.$album);
            $this->smarty->assign('url', $url);
            $this->smarty->assign('photocastUrl', $urlPhotoCast);
            $albumName = $_SESSION['albums'][$album]['AlbumName'];
            $body = $this->smarty->fetch('messagebody.tpl');
            $this->smarty->assign('mailurl', "mailto:?subject=".rawurlencode($albumName." Photos")."&amp;body=".rawurlencode($body));
            $this->smarty->assign('albumDisplayed', $album);    // for photocast in header.tpl
        }
    }

    //----------------------------------------------
    function displaySearchForm() {
        $this->checkForLibrary();
        $this->assignSearchFormInfos();
        $this->smarty->assign_by_ref('download', $_SESSION['albums']['download']['PhotoIds']);
        $this->smarty->display('search.tpl');
    }

    //----------------------------------------------
    function authorizedLibs() {
        $libs = new Libraries();
        if ($_SESSION['is_admin']) {
            return $libs->libList();
        }

        $users = new Users();
        $authLibs = array();
        foreach ($libs->libList() as $libId=>$lib) {
            if (count($users->userAlbums(   $libId, $_SESSION['user']))>0 ||
                      $users->hasFullAccess($libid, $_SESSION['user'])) {
                $authLibs[$libId] = $lib;
            }
        }
        return $authLibs;
    }

     //----------------------------------------------
    // Protect against a direct url to access forbidden album
    function albumAuthorized($albumId) {
        if ($this->isAuthorizedAlbum($albumId)) {
            return $_SESSION['albums'][$albumId]['PhotoIds'];
        } else {
            return array();
        }
    }

   //----------------------------------------------
     function isAuthorizedAlbum($albumId, $libId=NULL) {
        $users = new Users();
        $libId = isset($libId) ? $libId : $_SESSION['library']['id'];
        return ($_SESSION['is_admin'] ||
            $users->hasFullAccess($libId, $_SESSION['user']) ||
            in_array($albumId, $users->userAlbums($libId, $_SESSION['user'])) ||
            ( ! is_numeric($albumId)));
    }

   //----------------------------------------------
    function isAuthorizedPhoto($photoId) {
        return in_array($photoId, $_SESSION['albums'][$_SESSION['master']]['PhotoIds']);
    }

    //----------------------------------------------
    function filterPhotos($album, $pattern, $searchType, $period, $keywords, $kwSearchType) {
        set_time_limit(0); // No time limit during loading. Large lib may be loooonnnnng

        $photoIdsSelected = $this->albumAuthorized($album);
        // Then apply the text grep
        $this->photosFilterOnComments($photoIdsSelected, $pattern, $searchType);
        // Then apply the date period filter
        $this->photosFilterOnPeriod($photoIdsSelected, $period);
        // Then apply the date period filter
        $this->photosFilterOnKeywords($photoIdsSelected, $keywords, $kwSearchType);
        // and sort by time
        usort($photoIdsSelected, array(&$this, "cmpPhotoTimestamp"));
        
        set_time_limit(30); // Restore standard value for the end of the script
        return $photoIdsSelected;
    }

    //----------------------------------------------
    function nbThumbCols() {
        // thumb width:240, table cell: 270, 10 between 2 cells, 10 at the beginning an at the enc
        return max(3, floor(($_COOKIE['browserwidth']-10)/280));
    }

    //----------------------------------------------
    function displaySelectedPhotos($album, $pattern, $searchType, $pos, $nbRows, $period, $keywords, $kwSearchType) {
        $this->checkForLibrary();
        $pattern = stripslashes($pattern);
        $_SESSION['lastSearch'] = array('album'=>$album, 'pattern'=>$pattern,
                                        'searchType'=>$searchType, 'pos'=>$pos,
                                        'nbRows'=>$nbRows, 'period'=>$period,
                                        'keywords'=>$keywords, 'kwSearchType'=>$kwSearchType);

        $photoIdsSelected = $this->filterPhotos($album, $pattern, $searchType, $period, $keywords, $kwSearchType);
        $this->smarty->assign('nbPhotos', count($photoIdsSelected));
        
        $nbRows = min($nbRows, max($this->availableNbRows));
        $nbRows = max($nbRows, min($this->availableNbRows));

        $nbCols = $this->nbThumbCols();
        $pageSize = $nbCols*$nbRows;

        // $pos<0: then -pos is a photoId from the slidehsow mode -> Let's find the right page
        if (empty($pos)) {
            $pos = 0;
        } elseif ($pos<0) {
                $pos = $pageSize*floor(array_search(-$pos, $photoIdsSelected)/$pageSize);
                // turnaround a little bug in the pager to display the proper current page number
                $_REQUEST['pos'] = $pos;
        }

        $this->smarty->assign('posStart', $pos+1);
        $this->smarty->assign('posEnd', min($pos+$pageSize, count($photoIdsSelected)));
        $this->smarty->assign('nbSelected', count($photoIdsSelected));

        // Only pass a page of photos
        $photoIdsSelected = array_slice($photoIdsSelected, $pos, $pageSize);
        
        $this->assignSearchFormInfos($album, $pattern, $searchType, $nbRows, $period, $keywords, $kwSearchType);
        $this->smarty->assign_by_ref('photos', $_SESSION['photos']);
        $this->smarty->assign_by_ref('photoIds', $photoIdsSelected);
        $this->smarty->assign_by_ref('download', $_SESSION['albums']['download']['PhotoIds']);
        $this->smarty->assign('nbCols', $nbCols);
        $this->smarty->assign('selectedPageSize', $pageSize);   // for the pager
        $this->smarty->assign('need2ndSeachBar', (count($photoIdsSelected)/$nbCols)>2);
        $this->smarty->display('results.tpl');
        
        $this->contentDisplayed('set');
    }

    //----------------------------------------------
    function sendPhoto($photoId, $maxWidth=NULL, $maxHeight=NULL) {
        $this->checkForLibrary();

        // security check
        if ( ! $this->isAuthorizedPhoto($photoId)) {
            die ("Not authorized to view this photo.");
        }

        $path = $_SESSION['library']['path'].$_SESSION['photos'][$photoId]['ImagePath'];
        $ratio = $_SESSION['photos'][$photoId]['Aspect Ratio'];

        if ( isset($maxWidth) || isset($maxHeight)) {
            $transfoParams = array('maxWidth'=>$maxWidth, 'maxHeight'=>$maxHeight, 'ratio'=>$ratio, 'quality'=>'50');
        }
        $this->smarty->config_load('wipha.conf');
        $transfoParams['cachesize'] = $this->smarty->get_config_vars('cachesize');

        sendImage($path, 21600, $transfoParams);
    }

     //----------------------------------------------
    function sendVideo($photoId) {
        $this->checkForLibrary();

        // security check
        if ( ! $this->isAuthorizedPhoto($photoId)) {
            die ("Not authorized to view this video.");
        }

        $path = $_SESSION['library']['path'].$_SESSION['photos'][$photoId]['ImagePath'];
        sendVideo($path, 21600);
    }

   //----------------------------------------------
    function sendThumb($photoId) {
        $this->checkForLibrary();

        // security check
        if ( ! $this->isAuthorizedPhoto($photoId)) {
            die ("Not authorized to view this photo.");
        }

        $path = $_SESSION['library']['path'].$_SESSION['photos'][$photoId]['ThumbPath'];
        sendImage($path, 21600);
    }

    //----------------------------------------------
    function displaySlideshow($photoId, $time) {
        $this->checkForLibrary();
        // security check
        if ( ! $this->isAuthorizedPhoto($photoId)) {
            $this->displaySearchForm();
            exit;
        }
        // if direct access from url, let's slideshow his master album
        if ( ! isset($_SESSION['lastSearch'])) {
            $_SESSION['lastSearch']['album'] = $_SESSION['master'];
        }

        $ls =& $_SESSION['lastSearch'];
        $this->assignSearchFormInfos($ls['album'], $ls['pattern'], $ls['searchType'], $ls['nbRows'], $ls['period'], $ls['keywords'], $ls['kwSearchType']);
        $photoIdsSelected = $this->filterPhotos($ls['album'], $ls['pattern'], $ls['searchType'], $ls['period'], $ls['keywords'], $ls['kwSearchType']);
        $nbSelected = count($photoIdsSelected);
        $pos = array_search($photoId, $photoIdsSelected);
        
        $nextId = ($pos==$nbSelected-1) ? $photoIdsSelected[0] : $photoIdsSelected[$pos+1];
        $prevId = ($pos==0) ? $photoIdsSelected[$nbSelected-1] : $photoIdsSelected[$pos-1];
        
        $r = $_SESSION['photos'][$photoId]['Aspect Ratio'];
        if ($r>1) {
            $w = 640; $h = round($w/$r);
        } else {
            $h = 640; $w = round($h*$r);
        }

        $this->smarty->assign('posStart', $pos+1);
        $this->smarty->assign('nbSelected', $nbSelected);
        $this->smarty->assign_by_ref('photo', $_SESSION['photos'][$photoId]);
        $this->smarty->assign('photoId', $photoId);
        $this->smarty->assign('nextId', $nextId);
        $this->smarty->assign('prevId', $prevId);
        $this->smarty->assign('width', $w);
        $this->smarty->assign('height', $h);
        $this->smarty->assign('time', max((isset($time) ? $time : 10),5));    // minimum 5: Security
        $this->smarty->assign('state', ($time>0) ? "playing" : "paused");
        $this->smarty->assign_by_ref('download', $_SESSION['albums']['download']['PhotoIds']);
        $this->smarty->assign('prefetch', $_SERVER['SCRIPT_NAME']."?ph=".$nextId."&amp;lib=".$_SESSION['library']['id']);
        if ($_COOKIE['exif']=="yes") {
            $this->assignExif($photoId);
        }
        $this->smarty->display('slideshow.tpl');
        
        $this->contentDisplayed('set');
    }

    //----------------------------------------------
    function assignExif($photoId) {
        if  ($photoId != "none") {
            $path = $_SESSION['library']['path'].$_SESSION['photos'][$photoId]['ImagePath'];
            $path = getMacAliasOriginal($path, $error);
            if (isset($path)) {
                $exifraw = @exif_read_data($path, 0, true);
                foreach ($exifraw as $key => $section) {
	                foreach ($section as $name => $val) {
		                if ($name != "MakerNote") {
			                $exif[$key][]=$name;
			                $exif[$key][]=preg_replace('/([^[:print:]]+)/', '', $val);
                        }
	                }
                }
                $this->smarty->assign('exif', $exif);
            } else {
                die($error);
            }
        }
    }

    //----------------------------------------------
    function exif($photoId) {
        $this->assignExif($photoId);
        return $this->smarty->fetch('exif.tpl');
    }

    //----------------------------------------------
    function displayAdminForm() {
        $users = new Users();
        if (is_array($_SESSION['albums'])) {
            foreach($_SESSION['albums'] as $id=>$data) {
                if (is_numeric($id)) {
                    $albums[$id]=$data['AlbumName'];
                }
            }
        }
        $libs = new Libraries();

        $this->smarty->assign('logins', $users->userList());
        $this->smarty->assign('admin', $users->admin());
        $this->smarty->assign('albums', $albums);
        $this->smarty->assign('libs', $libs->libList());
        $this->smarty->display('admin.tpl');
    }

    //----------------------------------------------
    // Goal: Was the previous displayed page showing some pictures (thumbs, or slide)
    //---> call this function with $action =
    // - set: in the display functions
    // - update: in the constructor
    // - test: to get the state
    function contentDisplayed($action) {
        switch($action) {
            case 'set':
                $_SESSION['contentDisplayed'] = 2;
                break;
            case 'update':
                $_SESSION['contentDisplayed']--;
                break;
            case 'test':
                return ($_SESSION['contentDisplayed']==1);
                break;
        }
    }

    //----------------------------------------------
    // $id: libid to select. if null, display a selection page
    // $src: this is the url we come from, which we want to get back to
    // after the lib is loaded: 
    //  - ses: the url has been stored in $_SESSION['srcUrl']
    //  - ref: HTTP_REFERER
    //  - uri: REQUEST_URI
    function selectLib($id, $src) {
        if (empty($id)) {
            switch($src) {
                case 'uri':
                    $src = 'ref';
                    break;
                case 'ref':
                    $src = 'ses';
                    // It is already set if an error occured in a previous try
                    // -> don't overwrite it
                    if ( ! isset($_SESSION['srcUrl'])) {
                        $_SESSION['srcUrl'] = $this->contentDisplayed('test') ?
                             $_SERVER['SCRIPT_NAME'] :  // Don't re-display a search with old params in a new lib
                             $_SERVER['HTTP_REFERER'];
                    }
                    break;
            }
            $this->displaySelectLibForm($src);
        } else {
            switch($src) {
                case 'uri': $srcUrl = $_SERVER['REQUEST_URI'];  break;
                case 'ref': $srcUrl = $_SERVER['HTTP_REFERER']; break;
                case 'ses': $srcUrl = $_SESSION['srcUrl'];      break;
            }
            $ret = $this->loadLibrary($id, $srcUrl);
            if ($ret) {
                unset($_SESSION['srcUrl']);
                if ($ret==="cached") {
                    // The lib was read from cache files, no GUI/javascript -> go to srcUrl
                    if ($src != 'uri') {    # otherwise, just go on loading the page
                        reloadUrl($srcUrl);
                    }
                } elseif ( ! $this->silentLoad()) {
                    exit;   // loading.tpl will require the URL again by itself
                }
            } else {
                // The library has NOT been loaded succesfully, memorize the srcUrl
                $_SESSION['srcUrl'] = $srcUrl;
                exit;
            }
        }
    }

    //----------------------------------------------
    function displaySelectLibForm($src) 
    {
        $this->smarty->assign('libs', $this->authorizedLibs());
        $this->smarty->assign('src', $src);
        $this->smarty->assign('nbdownload', count($_SESSION['albums']['download']['PhotoIds']));
        $this->smarty->display('selectlib.tpl');
    }

    //----------------------------------------------
    function displayAdminUserForm($login) {
        $users = new Users();
        if ($login==$users->admin()) {
            die("Admin have all albums access...");
        }
        $albums['full'] = '<em>Allow all albums in this library</em>';
        if (is_array($_SESSION['albums'])) {
            foreach($_SESSION['albums'] as $id=>$data) {
                if (is_numeric($id)) {
                    $albums[$id]=$data['AlbumName'];
                }
            }
        }
        $_SESSION['editedUser'] = $login; // Memorize the edited user
        
        $userAlbums = $users->userAlbums($_SESSION['library']['id'], $login);
        if ($users->hasFullAccess($_SESSION['library']['id'], $_SESSION['user'])) {
            array_push($userAlbums, 'full');
        }
        $this->smarty->assign('userAlbums', $userAlbums);
        $this->smarty->assign('login', $login);
        $this->smarty->assign('albums', $albums);
        $this->smarty->display('adminuser.tpl');
    }

    //----------------------------------------------
    function displayAdminAlbumForm($album) {
        $users = new Users();
        $_SESSION['editedAlbum'] = $album; // Memorize the edited album
        $this->smarty->assign('album', $_SESSION['albums'][$album]['AlbumName']);
        $this->smarty->assign('albumUsers', $users->albumUsers($_SESSION['library']['id'], $album));
        foreach ($users->userList() as $login) { $loginList[$login] = $login; }
        $this->smarty->assign('logins', $loginList);
        $this->smarty->display('adminalbum.tpl');
    }

    //----------------------------------------------
    function displayAdminLibForm($id) {
        $libs = new Libraries();
        $lib = $libs->lib($id);
        $this->smarty->config_load('wipha.conf');
        $demo = $this->smarty->get_config_vars('demo');
        if ($demo) {
            $lib['path'] = '/Users/alexandre/Pictures/'.basename($lib['path']);
        }
        $this->smarty->assign('lib', $lib);
        $this->smarty->assign('id', $id);
        $this->smarty->display('adminlib.tpl');
    }

    //----------------------------------------------
    function updateAdmin($login, $passwd) {
        $this->smarty->config_load('wipha.conf');
        $demo = $this->smarty->get_config_vars('demo');
        if ( ! $demo) {
            $users = new Users();
            $users->updateAdmin($login, $passwd, $error);
            $_SESSION['user'] = $users->admin();
        } else {
            $error = DEMO_ADMIN;
        }
        $this->smarty->assign('error_adm', $error);
        $this->displayAdminForm();
    }
    
    //----------------------------------------------
    function updateUser($login, $passwd, $albums) {
        $this->smarty->config_load('wipha.conf');
        $demo = $this->smarty->get_config_vars('demo');
        if ($_SESSION['editedUser']=='guest' && $demo) {
            $error = DEMO_GUEST;
        } else {
            $users = new Users();
            $users->updateUser($_SESSION['library']['id'], $_SESSION['editedUser'], $login, $passwd, $albums, $error);
        }
        if (isset($error)) {
            $this->smarty->assign('error_user', $error);
            $this->displayAdminUserForm($_SESSION['editedUser']);      
        } else {
            unset($_SESSION['editedUser']);
            $this->displayAdminForm();
        }
    }
    
     //----------------------------------------------
    function updateAlbum($logins) {
        $this->smarty->config_load('wipha.conf');
        $demo = $this->smarty->get_config_vars('demo');
        if ($demo) {
            // remove guest of the user list
            $key = array_search('guest', $logins);
            if ($key!==false) {
                unset($logins[$key]);
                $error = DEMO_GUEST;
            }
        }

        $users = new Users();
        $users->updateAlbum($_SESSION['library']['id'], $_SESSION['editedAlbum'], $logins);

        if (isset($error)) {
            $this->smarty->assign('error_user', $error);
            $this->displayAdminAlbumForm($_SESSION['editedAlbum']);      
        } else {
            unset($_SESSION['editedAlbum']);
            $this->displayAdminForm();
        }
    }
    
   //----------------------------------------------
    function addUser($login, $passwd) {
        $users = new Users();
        $ret = $users->addUser($login, $passwd, $error);
        $this->smarty->assign('error_user', $error);
        $this->displayAdminForm();
    }
    
    //----------------------------------------------
    function deleteUser($login) {
        $users = new Users();
        $ret = $users->deleteUser($login);
        $this->displayAdminForm();
    }
    
     //----------------------------------------------
    function updateLib($id, $path, $name) {
        $this->smarty->config_load('wipha.conf');
        $demo = $this->smarty->get_config_vars('demo');
        if ($demo) {
            $error = DEMO_LIB;
        } else {
            $libs = new Libraries();
            $libs->updateLib($id, $path, $name, $error);
        }

        if (isset($error)) {
            $this->smarty->assign('error_lib', $error);
            $this->displayAdminLibForm($id);      
        } else {
            if ($_SESSION['library']['id']==$id) {
                $_SESSION['library']['name'] = trim($name);
            }
            $this->displayAdminForm();
        }
    }

   //----------------------------------------------
    function addLib($path, $name) {
        $this->smarty->config_load('wipha.conf');
        $demo = $this->smarty->get_config_vars('demo');
        if ($demo) {
            $error = DEMO_LIB;
        } else {
            $libs = new Libraries();
            $libs->addLib($path, $name, $error);
            $_SESSION['nblibrary'] = $libs->nb();
        }
        $this->smarty->assign('error_lib', $error);
        $this->displayAdminForm();
    }

   //----------------------------------------------
    function deleteLib($id) {
        $this->smarty->config_load('wipha.conf');
        $demo = $this->smarty->get_config_vars('demo');
        if ($demo) {
            $error = DEMO_LIB;
        } else {
            $libs = new Libraries();
            $libs->deleteLib($id);
            $_SESSION['nblibrary'] = $libs->nb();
        }

        $this->smarty->assign('error_lib', $error);
        $this->displayAdminForm();
    }

    //----------------------------------------------
    function addDowloadPhotos($photoIds) {
        $downloadIds =& $_SESSION['albums']['download']['PhotoIds'];
        foreach($photoIds as $photoId) {
            if (array_key_exists($photoId, $_SESSION['photos'])&&!in_array($photoId, $downloadIds)) {
                array_push($downloadIds, $photoId);
            }
        }
    }

    //----------------------------------------------
    function removeDowloadPhotos($photoIds) {
        $downloadIds =& $_SESSION['albums']['download']['PhotoIds'];
        if (is_array($photoIds)) {
            foreach($photoIds as $photoId) {
                $key = array_search($photoId, $downloadIds);
                if ($key!==false) {
                    unset($downloadIds[$key]);
                }
            }
        } elseif ($photoIds="all") {
            $downloadIds = array();
        }
    }
    
    //----------------------------------------------
    function nbDowloadPhoto() {
        return count($_SESSION['albums']['download']['PhotoIds']);
    }
    
    //----------------------------------------------
    // $period may be: "", "yyyy" (2006) , "Mmm yyyy" (Jan 2006)
    function getDateInfo($period, $albumId) {
        $photoIds = $this->albumAuthorized($albumId);
        if ($this->normalizePeriod($period, $interval) !=0) {
            return "";
        }
        if (isset($interval)) {
            $this->photosFilterOnDates($photoIds, $interval[0], $interval[1]);
        }

        // and sort by time, for the elements returned to be sorted too
        usort($photoIds, array(&$this, "cmpPhotoTimestamp"));

        list( , $m, $y) = explode(' ', $interval[0]);
        list( , $me)    = explode(' ', $interval[1]);
        if ($m!=$me) unset($m);

        $result = array();
        foreach ($photoIds as $photoId) {
            $datep = strftime("%d %b %Y", $_SESSION['photos'][$photoId]['Timestamp']);
            list($dp, $mp, $yp) = explode(' ', $datep);
            if ($yp==$y) {
                if (isset($m) && $mp==$m) {
                    $result[$dp]++;
                } else {
                    $result[$mp]++;
                }
            } else {
                $result[$yp]++;
            }
        }

        return serialize($result);
    }

    //----------------------------------------------
    function normalizePeriod(&$period, &$interval) {
        $monthInfo = array('Jan'=>31, 'Feb'=>28, 'Mar'=>31, 'Apr'=>30, 'May'=>31, 'Jun'=>30,
                            'Jul'=>31, 'Aug'=>31, 'Sep'=>30, 'Oct'=>31, 'Nov'=>30, 'Dec'=>31);

        $period = preg_replace('/\\s+/', ' ', trim($period));
        if (empty($period)) return 0;
        $dates = preg_split('/\\s*(\\bto\\b|[\/-])\\s*/i', $period);  // splits on 'to' or '-' or '/'
        $pa = array();  // period array [0=1st period, 1=optional 2nde period][1=day, 2=month, 3=year]
        foreach ($dates as $i=>$date) {
            // Replace "february" by "Feb"
            $date = $dates[$i] = preg_replace('/([[:alpha:]]+)/ie', "substr(ucfirst(strtolower('\\1')),0,3)", $date);
            // date format: dd Mmm YYYY . Example: 8 Feb 2005
            $ret = preg_match('/^(\\b\\d{1,2}\\b)?\\s*(\\b[[:alpha:]]{3}\\b)?\\s*(\\b\\d{4}\\b)?$/', $date, $pa[$i]);
            unset($pa[$i][0]);
            $pa[$i] = array_filter($pa[$i]);    // suppress empty elements
            if ($ret == 0) return 1;
            if ( ! empty($pa[$i][2]) && ! in_array($pa[$i][2], array_keys($monthInfo))) return 2;
        }
        
        // Rewrite the period properly formated
        $period = implode(' - ', $dates);
        $period = preg_replace('/\\s+/', ' ', $period);

        // Test for stupid interval such as "2 - Fev 2005", or "2 Jan - 2005",
        // or "2 Jan" without a 2nde date
        $prec0 = max(array_keys($pa[0]));
        $prec1 = empty($pa[1]) ? 3: min(array_keys($pa[1]));
        if ($prec0<$prec1) return 3;

        // Fill the missing month and year of date1 (if necessary)
        if (empty($pa[0][2])) $pa[0][2] = $pa[1][2];   // month
        if (empty($pa[0][3])) $pa[0][3] = $pa[1][3];   // year

        // Test for wrong interval such as "Fev 2005 - 5 Fev 2005", or "2 Jan 2005 - 2005"
        if (count($pa)==2) {
            $fd0 = implode(' ', $pa[0]);
            $fd1 = implode(' ', $pa[1]);
            if (preg_match('/'.$fd0.'/i', $fd1) || preg_match('/'.$fd1.'/i', $fd0)) return 4;
        }

        // Now I've 1 or 2 dates such as "2 Fev 2005" or "Fev 2005" or "2005"

        // Beginning date
        $dat =& $pa[0];
        $db = isset($dat[1]) ? intval($dat[1]) : 1;
        $mb = isset($dat[2]) ? $dat[2] : 'Jan';
        $yb = isset($dat[3]) ? $dat[3] : 1970;

        // Ending date
        if (isset($pa[1])) { $dat =& $pa[1]; }
        if (($dat[2]=='Feb')&&($dat[3]%4==0)) { $monthInfo['Feb'] = 29; }
        $de = isset($dat[1]) ? intval($dat[1]) : (isset($dat[2]) ? $monthInfo[$dat[2]] : $monthInfo['Dec']);
        $me = isset($dat[2]) ? $dat[2] : 'Dec';
        $ye = isset($dat[3]) ? $dat[3] : 2200;   // We should all be dead here, and this prog not used anymore :-)

        $begin = "$db $mb $yb";
        $end = "$de $me $ye";

        // Check that beginning date is earlier than ending date
        if (strtotime($begin)>strtotime($end)) return 5;


        $interval = array("$db $mb $yb", "$de $me $ye");
        return 0;
    }

    //----------------------------------------------
    // Streams the zip to the client browser while it's being generated.
    function downloadPhotos() {
        $this->checkForLibrary();
        $nbSel = count($_SESSION['albums']['download']['PhotoIds']);
        $this->smarty->assign('nbSel', $nbSel);
        if ($nbSel==0) {
            $this->smarty->display('download.tpl');
            return;
        }
        
        // For browsers which know how to do this, let's display the page and
        // send the zip in the same request, with multipart content.
        // Otherwise, store a tag in the session and let the page relaunch itself.
        $doMultipart = in_array($_SESSION['browser'][app], array("FIREFOX", "CAMINO"));
        $this->smarty->assign('multipart', $doMultipart);

        if ($doMultipart || ( ! isset($_SESSION['sendZip']))) {
            // Estimate the size of the download....
            $size = 0;
            $nbForbidden = 0;
            foreach ($_SESSION['albums']['download']['PhotoIds'] as $photoId) {
                $file = getMacAliasOriginal($_SESSION['library']['path'].$_SESSION['photos'][$photoId]['ImagePath'], $error);
                if ( ! empty($file)) {
                    if (is_readable($file)) {
                        $size += filesize($file);
                    } else {
                        $nbForbidden++;
                    }
                } else {
                    $nbForbidden++;
                }
            }
            $this->smarty->assign('nbDl', $nbSel-$nbForbidden);
            $this->smarty->assign('nbForbidden', $nbForbidden);
            $this->smarty->assign('size', round($size/1024/1024, 1)+0.1);
        }
        
        if ($doMultipart) {
            $boundary = "+-+-+WiPhADownloadZip+-+-+";
            header("Content-type: multipart/mixed;boundary=$boundary");
            print "--$boundary\n";
            print "Content-type: text/html\nPragma: No-cache\n\n";
            $this->smarty->display('download.tpl');
            print "\n";
            print "--$boundary\n";
            while (ob_get_level()) { ob_end_flush(); }
            if (ob_get_length() === false) { ob_start(); }
            ob_flush(); flush();
            ob_end_flush();
        } else {
            if (isset($_SESSION['sendZip'])) {
                unset($_SESSION['sendZip']);
            } else {
                $this->smarty->display('download.tpl');
                // the page will relaunch itself to trigger the download
                $_SESSION['sendZip'] = true;
                return;
            }
        }
        
        $zip = new PhotosZip('WiPhA_Export.zip', true, $doMultipart,
                            $_SESSION['albums']['download']['PhotoIds'],
                            $_SESSION['photos'], $_SESSION['library']['path']);
        $zip->addAllPhotos();
        $zip->save();
        
        if ($doMultipart) {
 		    print "\n\n--$boundary--\n";
        }
    }
    
    //----------------------------------------------
    function photosFilterOnComments(&$photos, $pattern, $searchType) {

        $pattern = trim($pattern);
        if (empty($pattern)) return;

        switch ($searchType) {
            case 'r': // Regexp
                $this->filterParam = '/' . $pattern . '/i';    // to be used by commentsMachRegexp
                $photos = array_filter($photos, array(&$this, "commentsMatchRegexp"));
                break;
            case 'y': // Any word
                $this->filterParam = '/' . preg_replace('/\\s+/', '|', preg_quote($pattern)) .'/i';
                $photos = array_filter($photos, array(&$this, "commentsMatchRegexp"));
                break;
            case 'l': // All words
                $patterns = preg_split('/\\s+/', preg_quote($pattern));
                foreach ($patterns as $pattern) {
                    $this->filterParam = "/$pattern/i";
                    $photos = array_filter($photos, array(&$this, "commentsMatchRegexp"));
                }
                break;
        }
    }
    
    //----------------------------------------------
    function photosFilterOnPeriod(&$photos, $period) {
        $this->normalizePeriod($period, $interval);
        if (isset($interval)) {
            $this->photosFilterOnDates($photos, $interval[0], $interval[1]);
        }
    }
    
    //----------------------------------------------
    function photosFilterOnDates(&$photos, $fromdate, $todate) {
        $fromTimestamp = $this->calendarDateToTimestamp($fromdate, "00:00:00");
        if ( ! empty($fromTimestamp)) {   
            $this->filterParam = $fromTimestamp;    // to be used by photoOlderThanTimestamp
            $photos = array_filter($photos, array(&$this, "photoOlderThanTimestamp"));
        }
        $toTimestamp = $this->calendarDateToTimestamp($todate, "23:59:59");
        if ( ! empty($toTimestamp)) {   
            $this->filterParam = $toTimestamp;    // to be used by photoYoungerThanTimestamp
            $photos = array_filter($photos, array(&$this, "photoYoungerThanTimestamp"));
        }
    }
    
    //----------------------------------------------
    function photosFilterOnKeywords(&$photos, $keywords, $kwSearchType) {
        if ( ! is_array($keywords)) return;
        if (count($keywords)==0)    return;

        $this->filterParam = $keywords;
        switch ($kwSearchType) {
            case 'y': // Any keyword
                $photos = array_filter($photos, array(&$this, "keywordsMatchAny"));
                break;
            case 'l': // All keywords
                $photos = array_filter($photos, array(&$this, "keywordsMatchAll"));
                break;
        }
    }
    
    //----------------------------------------------
    function calendarDateToTimestamp($date, $time) {
        if (preg_match("/^\\s*$/", $date)) return NULL;
        $ts = strtotime("$date $time");
        return ($ts!=-1) ? $ts : NULL;
    }
    
    //----------------------------------------------
    function commentsMatchRegexp($photo) {
        return preg_match($this->filterParam, $_SESSION['photos'][$photo]['Caption']) or
               preg_match($this->filterParam, $_SESSION['photos'][$photo]['Comment']);
    }
    
    //----------------------------------------------
    function keywordsMatchAll($photo) {
        if (is_array($_SESSION['photos'][$photo]['Keywords'])) {
            foreach ($this->filterParam as $skw) {
                $ok = false;
                foreach ($_SESSION['photos'][$photo]['Keywords'] as $kw) {
                    if ($skw==$kw) {
                        $ok= true;
                        break;
                    }
                }
                if ($ok==false) return false;
            }
            return true;
        }
    return false;
    }
    
    //----------------------------------------------
    function keywordsMatchAny($photo) {
        if (is_array($_SESSION['photos'][$photo]['Keywords'])) {
            foreach ($this->filterParam as $skw) {
                foreach ($_SESSION['photos'][$photo]['Keywords'] as $kw) {
                    if ($skw==$kw) return true;
                }
            }
        }
        return false;
    }
    
    //----------------------------------------------
    function photoOlderThanTimestamp($photo) {
        return $_SESSION['photos'][$photo]['Timestamp'] >= $this->filterParam;
    }
    
    //----------------------------------------------
    function photoYoungerThanTimestamp($photo) {
        return $_SESSION['photos'][$photo]['Timestamp'] <= $this->filterParam;
    }
    
    //----------------------------------------------
    function cmpPhotoTimestamp($a, $b) {
        $ta = $_SESSION['photos'][$a]['Timestamp'];
        $tb = $_SESSION['photos'][$b]['Timestamp'];
        if ($ta==$tb) return 0;
        else return ($ta<$tb) ? -1 : 1;
    }

    //----------------------------------------------
    function help($section) {
        $section = preg_replace('/\\W/', '', $section);
        $template = 'help_'.$section.'.tpl';
        if ($this->smarty->template_exists($template)) {
            $this->smarty->assign('template', $template);
        }
        $this->smarty->display('help.tpl');
    }
//=================== PHOTOCAST SECTION ===========================
// These functions can be used without a loaded lib

    //----------------------------------------------
    function photocast($libId, $albumId) {
        new Users(true);    // just to force re-read the file
        if ( ! $this->isAuthorizedAlbum($albumId, $libId)) {
            die("No access to this album");
        }

        $libs = new Libraries();
        $lib = $libs->lib($libId);
        $libPath = $lib['path'].'/';
        $libFile = $libPath.'AlbumData.xml';

        // Special for guest: links contains "&guest" to allow connection without login dialog
        if (is_numeric($albumId) && $_SESSION['user']!='guest') {
            $cached = "data/cache/".$libId."_".$albumId.".xml";
        } else {
            $cached = "data/cache/".$libId."_".$albumId."_".$_SESSION['user'].".xml";
        }

        if ( (! file_exists($cached))||(filemtime($libFile)>filemtime($cached))) {
            // Need to re-create the RSS file
            if ($libId!=$_SESSION['library']['id']) {
                $this->loadLibrary($libId);
            }
            $photoIds = $this->albumAuthorized($albumId);
            // sort by time
            usort($photoIds, array(&$this, "cmpPhotoTimestamp"));
            
            foreach($photoIds as $photoId) {
                $photo =& $_SESSION['photos'][$photoId];
                // as the photocast will be compressed, the size is not known yet
                //$photo['size'] = filesize($libPath.$_SESSION['photos'][$photoId]['ImagePath']);
                $photo['encImgPath']   = base64_encode($_SESSION['photos'][$photoId]['ImagePath']);
                $photo['encThumbPath'] = base64_encode($_SESSION['photos'][$photoId]['ThumbPath']);
            }
            $this->smarty->assign_by_ref('photos', $_SESSION['photos']);
            $this->smarty->assign_by_ref('photoIds', $photoIds);
            $this->smarty->assign('albumName', $_SESSION['albums'][$albumId]['AlbumName']);
            $this->smarty->assign('site', "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
            $this->smarty->assign('library', $lib);

            if($f = @fopen($cached,"w")) {
                if(@fwrite($f, $this->smarty->fetch('photocast.tpl'))) {
                } else die("Could not write to file $cached at Wipha::photocast");
                @fclose($f);
                chmod($cached, 0664);
            } else die("Could not open file $cached for writing, at Wipha::photocast");
        }
        header("Content-Type: text/xml;charset=UTF-8");
        if ( ! httpConditional(filemtime($cached), 3600, 0, false, false, false)) {
            header("Content-Length: " . filesize($cached));
            _readfile($cached);
        }
    }

    //----------------------------------------------
    function sendPhotocastImage($libId, $path, $compress) {
        $libs = new Libraries();
        $lib = $libs->lib($libId);
        if (! is_array($lib)) die("No such lib");

        $path = $lib['path'].'/'.base64_decode($path);
        
        $this->smarty->config_load('wipha.conf');
        $cacheSize = $this->smarty->get_config_vars('cachesize');
        sendImage($path, 43200, $compress ? array('quality'=>'40',
                                                  'cachesize'=>$cacheSize) : NULL);
    }

    //----------------------------------------------
    function displayPhotocastSlide($libId, $path) {
        $smarty = new SmartyApp();
        $smarty->assign('path', $path);
        $smarty->assign('libId', $libId);
        $smarty->display('photocastthumb.tpl');
    }


}   // end of whipha class

?>
