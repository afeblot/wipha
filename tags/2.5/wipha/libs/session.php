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

require_once('utils.php');

date_default_timezone_set('UTC');

//=============================================================================
class Session {

    //----------------------------------------------
    function Session($name=NULL) {
        if (isset($name)) {
            // Create a session name which is different for each installed wipha
            session_name(md5(dirname($_SERVER['SCRIPT_FILENAME']))."-".$name);
             session_start();
            if ( ! isset($_SESSION['browser'])) {
                $_SESSION['browser'] = _get_browser();
            }
            
            $_SESSION['cssValid'] = cssValidUrl();
            $_SESSION['xhtmlValid'] = xhtmlValidUrl($_SESSION['library']['id']);
        }

// $fd = fopen("data/session.log", "a");
// fwrite($fd, $_SESSION['browser']['app']."(".session_id().") auth=".$_SERVER['PHP_AUTH_USER'].", usr=".$_SESSION['user']."(".$_SESSION['is_admin']."), url=".$_SERVER['REQUEST_URI']." \n");
// fclose($fd);
    }

    //----------------------------------------------
    function destroy() {
        $_SESSION = array();
        // if (isset($_COOKIE[session_name()])) {
        //     // This should delete the cookie, but it doesn't, and a new one is created
        //     // with the same name and same parameters (how is this possible ???)
        //     setcookie(session_name(), '', 0, '/');
        // }
        session_destroy();
    }
}

?>
