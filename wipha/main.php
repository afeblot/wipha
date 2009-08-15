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

error_reporting(E_ALL & ~E_NOTICE);

session_cache_limiter('');

require_once('libs/session.php');
new Session('wipha');

require_once('libs/login.php');
require_once('libs/wipha.php');


require_once('libs/http-conditional.php');
httpConditional(time(), 0, 0, false, false, false);


$login = new Login(false);
if (isset($_GET['logout'])) {
    $login->logout();
    exit;
}


// $fd = fopen("data/session.log", "a");
// fwrite($fd, "* auth=".$_SERVER['PHP_AUTH_USER'].", usr=".$_SESSION['user']."(".$_SESSION['is_admin'].")\n");
// fclose($fd);


// Requests to th, ph, and fs also pass a lib parameter to avoid browser caching when
// the lib has changed.
// Other search requests may contain a lib param (when called from a mail for instance)
// So, wipha loads the requested lib (if needed) and unset $GET['lib'].
$wipha = new Wipha($_GET);

$action = 'unknown';
$get = array_keys($_GET);
if (count($get)>0) {
    sort($get);
    switch($get) {
        case array('sellib', 'src'):
            $action = 'selLib';
            break;        
        case array('dld'):
            $action = 'download';
            break;        
        case array('logout'):
            $action = 'logout';
            break;        
        case array('al', 'kw', 'kwst', 'per',        'row', 'sp', 'st'):
        case array('al', 'kw', 'kwst', 'per', 'pos', 'row', 'sp', 'st'):
        case array('al',       'kwst', 'per',        'row', 'sp', 'st'):
        case array('al',       'kwst', 'per', 'pos', 'row', 'sp', 'st'):
            $action = 'search';
            break;        
        case array('fs'):
            $action = 'fullsize';
            break;        
        case array('vd'):
            $action = 'video';
            break;        
        case array('sl'):
        case array('sl', 'time'):
            $action = 'slideshow';
            break;        
        case array('th'):
            $action = 'thumb';
            break;        
        case array('ph'):
            $action = 'photo';
            break;        
        case array('help'):
            $action = 'help';
            break;        
    }
}
$post = array_keys($_POST);
if (count($post)>0) {
    sort($post);
    switch($post) {
        case array('pipo'):
            $action = 'pipo';
            break;        
    }
}

//echo "action=$action<br>"; print_r($_GET);

switch($action) {
    case 'selLib':
        $wipha->selectLib($_GET['sellib'], $_GET['src']);
        break;
    case 'search':
        $wipha->displaySelectedPhotos($_GET['al'], $_GET['sp'], $_GET['st'], $_GET['pos'],
                                      $_GET['row'], $_GET['per'], $_GET['kw'], $_GET['kwst']);
        break;
    case 'download':
        $wipha->downloadPhotos($_GET['dld']);
        break;
    case 'thumb':
        $wipha->sendThumb($_GET['th']);
        break;
    case 'photo':
        $wipha->sendPhoto($_GET['ph'], 640, 640);
        break;
    case 'fullsize':
        $wipha->sendPhoto($_GET['fs']);
        break;
    case 'video':
        $wipha->sendVideo($_GET['vd']);
        break;
    case 'slideshow':
        $wipha->displaySlideshow($_GET['sl'], $_GET['time']);
        break;
    case 'help':
        $wipha->help($_GET['help']);
        break;
    default:
        // echo "Logged as ".$clearadm->getUser();
        $wipha->displaySearchForm();
        break;
}


?>
