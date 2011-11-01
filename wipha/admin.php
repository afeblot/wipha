<?php

/*
Alexandre Feblot, 2005-2011
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

$login = new Login(true);
$wipha = new Wipha($_GET);

$action = 'unknown';
$get = array_keys($_GET);
if (count($get)>0) {
    $keys = array_unique($get);
    sort($keys);
    switch($keys) {
        case array('eu'):
            $action = 'adminUser';
            break;        
        case array('ea'):
            $action = 'adminAlbum';
            break;        
        case array('elib'):
            $action = 'adminLib';
            break;        
        case array('del'):
            $action = 'deleteUser';
            break;        
        case array('dellib'):
            $action = 'delLib';
            break;        
    }
}
$post = array_keys($_POST);
if (count($post)>0) {
    $keys = array_unique($post);
    sort($keys);
    switch($keys) {
        case array('al'):
        case array('al', 'ap'):
            $action = 'updateAdmin';
            break;        
        case array('aul', 'aup'):
            $action = 'addUser';
            break;        
        case array(       'ul'):
        case array(       'ul', 'up'):
        case array('ual', 'ul'):
        case array('ual', 'ul', 'up'):
        case array('ual'):
            $action = 'updateUser';
            break;        
        case array('alu'):
            $action = 'updateAlbum';
            break;        
        case array('libn', 'libp'):
            $action = 'addLib';
            break;        
        case array('id', 'libn', 'libp'):
            $action = 'updateLib';
            break;        
    }
}

//echo "action=$action<br>"; print_r($_POST); echo "<br>"; print_r($_GET);

$error = '';
switch($action) {
    case 'adminUser':
        $wipha->displayAdminUserForm($_GET['eu']);
        break;
    case 'adminAlbum':
        $wipha->displayAdminAlbumForm($_GET['ea']);
        break;
    case 'adminLib':
        $wipha->displayAdminLibForm($_GET['elib']);
        break;
    case 'updateUser':
        $wipha->updateUser( $_POST['ul'], $_POST['up'], $_POST['ual']);
        break;
    case 'updateAdmin':
        $wipha->updateAdmin($_POST['al'], $_POST['ap']);
        break;
    case 'updateAlbum':
        $wipha->updateAlbum($_POST['alu']);
        break;
    case 'addUser':
        $wipha->addUser($_POST['aul'], $_POST['aup']);
        break;
    case 'deleteUser':
        $wipha->deleteUser($_GET['del']);
        break;
    case 'updateLib':
        $wipha->updateLib($_POST['id'], $_POST['libp'], $_POST['libn']);
        break;
    case 'addLib':
        $wipha->addLib($_POST['libp'], $_POST['libn']);
        break;
    case 'delLib':
        $wipha->deleteLib($_GET['dellib']);
        break;
    default:
        $wipha->displayAdminForm();
        break;
}


?>
