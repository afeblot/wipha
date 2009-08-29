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
require_once('users.php');
require_once('libs/session.php');
require_once('libs/smarty.php');

//=============================================================================
class Login {

    var $smarty = null;

    //----------------------------------------------
    function Login($requestAdmin) {
        $this->smarty =& new SmartyApp();

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $this->doHTTPLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
        } elseif ( ! isset($_SESSION['user'])) {
            if (isset($_POST['user']) || isset($_POST['passwd'])) {
                $this->doGUILogin($_POST['user'], $_POST['passwd']);
            } elseif (isset($_GET['guest']) || isset($_SESSION['browser']['no_interaction'])) {
                unset($_GET['guest']); unset($_REQUEST['guest']);
                $_SESSION['loginUrl'] = $_SERVER['REQUEST_URI'];
                $_SESSION['loginUrl'] = preg_replace('/(\\?|&)guest/', '', $_SESSION['loginUrl']);
                $this->doGUILogin('guest', 'guest');
            } else {
                $_SESSION['loginUrl'] = $_SERVER['REQUEST_URI'];
                $this->displayLoginForm();
            }
        } elseif (isset($_GET['guest'])) {
            unset($_GET['guest']); unset($_REQUEST['guest']);
        }
        if ($requestAdmin && !$_SESSION['is_admin']) {
            reloadUrl('main.php');
        }
    }
    
    //----------------------------------------------
    function displayLoginForm() {
        $users =& new Users;
        $this->smarty->assign('defaultAdmin', $users->isDefaultAdmin());
        $this->smarty->display('login.tpl');
        exit;
    }
    
    //----------------------------------------------
    function doHTTPLogin($user, $passwd) {
        if (! $this->authentificate($user, $passwd)) {
            header('WWW-Authenticate: Basic realm="WiPhA photocast"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Incorrect login / password: '.$_SERVER['PHP_AUTH_USER']." / ".$_SERVER['PHP_AUTH_PW'];
            exit;
        } else {
            // Login ok
            $_SESSION['user']=$user;
            $users =& new Users;
            $_SESSION['is_admin'] = ($users->admin()==$user);
        }
    }
    //----------------------------------------------
    function doGUILogin($user, $passwd) {
        if ($user=='' && $passwd=='') { $error = 'all_empty';    }
        elseif ($user=='')            { $error = 'user_empty';   }
        elseif ($passwd=='')          { $error = 'passwd_empty'; }

        if (isset($error)) {
            $this->smarty->assign('error', $error);
            $this->displayLoginForm();
        } else {
            if (! $this->authentificate($user, $passwd)) {
                // Login refused
                $this->smarty->assign('error', 'bad_login');
                $this->displayLoginForm();            
            } else {
                // Login ok
                $_SESSION['user']=$user;
                $users =& new Users;
                $_SESSION['is_admin'] = ($users->admin()==$user);
                $loginUrl = $_SESSION['loginUrl'];
                unset($_SESSION['loginUrl']);
                if ($loginUrl!=$_SERVER['REQUEST_URI']) {
                    reloadUrl($loginUrl);
                }
            }
        }
    }
    
    //----------------------------------------------
    // Do whatever you want here to authentificate your user
    //----------------------------------------------
    function authentificate($user, $passwd) {
        $users =& new Users;
        return $users->authentificate($user, $passwd);
    }

    //----------------------------------------------
    function logout() {
        $session =& new Session();
        $session->destroy();
        reloadUrl("main.php");
    }
}

?>
