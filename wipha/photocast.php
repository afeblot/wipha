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
require_once('libs/users.php');
require_once('libs/smarty.php');
new Session('wipha');

require_once('libs/wipha.php');

if (empty($_SESSION['user'])) {   
    // Authentification patch to work with apache in cgi mode, with a htaccess mod_rewrite rule
    if((!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) && isset($_SERVER['REMOTE_USER'])
    && preg_match('/Basic\s+(.*)$/i', $_SERVER['REMOTE_USER'], $matches)) {
	    list($name, $password) = explode(':', base64_decode($matches[1]), 2);
	    $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
	    $_SERVER['PHP_AUTH_PW']	= strip_tags($password);
    }

    // Authentification
    $users =& new Users();
    $user = isset($_GET['guest']) ? 'guest' : $_SERVER['PHP_AUTH_USER'];
    if (!isset($user)) {
        header('WWW-Authenticate: Basic realm="WiPhA photocast"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'You may use the public guest/guest login';
        exit;
    } elseif ($user!='guest' && !$users->authentificate($user,$_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="WiPhA photocast"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Incorrect username and password. You may use the public guest/guest login';
        exit;
    }

    $_SESSION['user'] = $user;
    $_SESSION['is_admin'] = ($users->admin()==$user);
}

if (isset($_GET['guest'])) {
    unset($_GET['guest']); unset($_REQUEST['guest']);    
}

// $fd = fopen("data/session.log", "a");
// fwrite($fd, "* auth=".$_SERVER['PHP_AUTH_USER'].", usr=".$_SESSION['user']."(".$_SESSION['is_admin'].")\n");
// fclose($fd);

$wipha =& new Wipha($foo);

$action = 'unknown';
$get = array_keys($_GET);
if (count($get)>0) {
    sort($get);
    switch($get) {
        case array('al', 'lib'):
            $action = 'photocast';
            break;
        case array('img', 'lib'):
            $action = 'sendImage';
            break;
        case array('cpr', 'lib'):
            $action = 'sendImageCompressed';
            break;
        case array('lib', 'sl'):
            $action = 'slide';
            break;
    }
}

//echo "action=$action<br>"; print_r($_GET);

switch($action) {
    case 'photocast':
        $wipha->photocast($_GET['lib'], $_GET['al']);
        break;
    case 'sendImage':
        $wipha->sendPhotocastImage($_GET['lib'], $_GET['img'], false);
        break;
    case 'sendImageCompressed':
        $wipha->sendPhotocastImage($_GET['lib'], $_GET['cpr'], true);
        break;
    case 'slide':
        $wipha->displayPhotocastSlide($_GET['lib'], $_GET['sl']);
        break;
    default:
        echo 'stop';
        break;
}


?>
