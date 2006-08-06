<?php

/*
Copyright Alexandre Feblot, 2005-2006
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

require_once('libs/session.php');
new Session('wipha');

require_once('libs/login.php');
require_once('libs/wipha.php');

$login =& new Login(false);
$wipha =& new Wipha($foo);

$action = 'unknown';
$get = array_keys($_GET);
if (count($get)>0) {
    sort($get);
    switch($get) {
        case array( '_', 'act', 'id'):
            $action = 'changePhotoToDownload';
            break;
        case array( '_', 'alb', 'map'):
            $action = 'getDateInfo';
            break;
        case array( '_', 'sbt'):
            $action = 'searchbarType';
            break;
        case array( '_', 'exif'):
            $action = 'exif';
            break;
        case array( '_', 'brw'):
            $action = 'browserSize';
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

switch($action) {
    case 'changePhotoToDownload':
        if ($_GET['act']=='add') {
            $wipha->addDowloadPhotos($_GET['id']);
        } elseif ($_GET['act']=='del') {
            $wipha->removeDowloadPhotos($_GET['id']);
        }
        echo $wipha->nbDowloadPhoto();
        break;
    case 'getDateInfo':
        echo $wipha->getDateInfo($_GET['map'], $_GET['alb']);
        break;
    case 'searchbarType':
        $wipha->setSearchbarType($_GET['sbt']);
        break;
    case 'exif':
        echo $wipha->exif($_GET['exif']);
        break;
    case 'browserSize':
        echo $wipha->storeBrowserSize($_GET['brw']);
        break;
    default:
        echo "what ?";print_r($_GET);
        break;
}


?>
