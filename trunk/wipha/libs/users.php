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

require_once('persistent.php');
define('USERS_FILE', 'data/users.ser');
define('DEFAULT_ADMIN', 'admin');
define('USERS_VERSION', 3);

define('NO_SUCH_USER', 1);
define('USER_EXISTS', 2);
define('BAD_USER_NAME', 3);
define('LOGIN_PASSWD_REQ', 4);

class Users extends Persistent {

    var $usersData;
    var $admin;

    //----------------------------------------------
    function Users($forceReadFile=false) {
        $this->usersData=array(DEFAULT_ADMIN =>array('passwd'=>crypt(DEFAULT_ADMIN), 'albums'=>array(), 'albumsTS'=>array())
                              ,'guest' =>array('passwd'=>crypt('guest'), 'albums'=>array(), 'albumsTS'=>array())
        );   // default value
        $this->admin = DEFAULT_ADMIN;
        $this->version = USERS_VERSION;
        $this->Persistent(USERS_FILE);
        $this->open($forceReadFile);
        
        # Update from v2 to v3
        if ($this->version<3) {
            $this->version = USERS_VERSION;
            foreach ($this->usersData as $login=>$foo) {
                $this->usersData[$login]['albumsTS'] = array();
            }
            $this->save();
        }
    }

    //----------------------------------------------
    function exists($login) {
        $login = trim(strtolower($login));
        return isset($this->usersData[$login]);
    }
    
    //----------------------------------------------
    function authentificate($login, $passwd) {
        $login = trim(strtolower($login));
        if ( ! $this->exists($login)) return false;
        $crpyp_passwd = $this->usersData[$login]['passwd'];
        return  $crpyp_passwd == crypt($passwd, $crpyp_passwd);
    }
    
    //----------------------------------------------
    function admin() {
        return $this->admin;
    }
    
    //----------------------------------------------
    function isDefaultAdmin() {
        return $this->authentificate(DEFAULT_ADMIN, DEFAULT_ADMIN);
    }
    
    //----------------------------------------------
    function hasFullAccess($libid, $login) {
        $albums = $this->usersData[$login]['albums'][$libid];
        if (isset($albums)) {
            return in_array('full', $this->usersData[$login]['albums'][$libid]);
        } else return false;
    }
    
    //----------------------------------------------
    function userList() {
        $users = array_keys($this->usersData);
        // remove admin
        unset($users[array_search($this->admin, $users)]);
        return $users;
    }
    
   //----------------------------------------------
   // Albums settings last modification date for this user
    function userAlbumsTS($libid, $login) {
        $login = trim(strtolower($login));
        return $this->usersData[$login]['albumsTS'][$libid];
    }

    //----------------------------------------------
    // albums authorized for this user
    function userAlbums($libid, $login) {
        $login = trim(strtolower($login));
        $albums = $this->usersData[$login]['albums'][$libid];
        if (isset($albums)) {
            $fullPpos = array_search('full', $albums);
            if ($fullPos!==false) unset($albums[$fullPos]);
            return $albums;
        } else {
            return array();
        }
    }

    //----------------------------------------------
    // users authorized for this album
    function albumUsers($libid, $album) {
        $logins = array();
        foreach ($this->usersData as $login=>$data) {
            if (isset($data['albums'][$libid])) {
                if (in_array($album, $data['albums'][$libid])) {
                    array_push($logins, $login);
                }
            }
        }
        return $logins;
    }

    //----------------------------------------------
    // $error: returned error value
    function newLoginAllowed($newLogin, &$error) {
        $newLogin = trim(strtolower($newLogin));
        if ($this->exists($newLogin)) {
            $error = USER_EXISTS;
            return false;
        } elseif ( ! preg_match('/^[[:alnum:]]*$/', $newLogin)) {
            $error = BAD_USER_NAME;
            return false;
        }
        return true;
    }

    //----------------------------------------------
    // $error: returned error value
    function updateUser($libid, $login, $newLogin, $newPasswd, $albums, &$error) {
        $login = trim(strtolower($login));
        $newLogin = trim(strtolower($newLogin));
        if ( ! $this->exists($login)) {
            $error = NO_SUCH_USER;
            return false;
        }
        if ( ! empty($newPasswd)) {
            $this->usersData[$login]['passwd'] = crypt($newPasswd);
        }
        if (isset($albums) && ! empty($libid)) {
            // eliminate the empty element added by the hidden field of the form :-(
            $key = array_search('', $albums);
            if ($key!==false) {
                unset($albums[$key]);
            }
            $libs = new Libraries();
            if ( ! $libs->exists($libid)) {
                $error = NO_SUCH_LIB;
                return false;
            }
            $this->usersData[$login]['albums'][$libid] = $albums;
            $this->usersData[$login]['albumsTS'][$libid] = time();
        }
        $ok = true;
        if ( ! empty($newLogin)) {
            if ($newLogin!=$login) {
                if ($this->newLoginAllowed($newLogin, $error)) {
                    $this->usersData[$newLogin] = $this->usersData[$login];
                    unset($this->usersData[$login]);
                    ksort($this->usersData);
                } else $ok = false;
            }
        }
        
        $this->save();
        return $ok;
    }
    
    //----------------------------------------------
    function updateAdmin($newLogin, $newPasswd, &$error) {
        $newLogin = trim(strtolower($newLogin));
        $ok = $this->updateUser(NULL, $this->admin, $newLogin, $newPasswd, NULL, $error);
        if ($ok) $this->admin = $newLogin;
        $this->save();
        return $ok;
    }

    //----------------------------------------------
    function updateAlbum($libid, $album, $logins) {
        foreach ($this->usersData as $login=>$data) {
            if ($this->admin != $login) {
                if ( ! isset($this->usersData[$login]['albums'][$libid])) {
                    $this->usersData[$login]['albums'][$libid] = array();
                }
                $albums    =& $this->usersData[$login]['albums'][$libid];
                $timestamp =& $this->usersData[$login]['albumsTS'][$libid];
                if (in_array($login, $logins)) {
                    if ( ! in_array($album, $albums)) {
                        array_push($albums, $album);
                        $timestamp = time();
                    }
                } else {
                    $key = array_search($album, $albums);
                    if ($key!==FALSE) {
                        unset($albums[$key]);
                        $timestamp = time();
                    }
                }
            }
            $this->save();
        }
        return $ok;
    }

    //----------------------------------------------
    function addUser($login, $passwd, &$error) {
        $login = trim(strtolower($login));
        if ( ! $this->newLoginAllowed($login, $error)) {
            return false;
        }
        if ( empty($login) || empty($passwd)) {
            $error = LOGIN_PASSWD_REQ;
            return false;
        }
        $this->usersData[$login]['passwd'] = crypt($passwd);
        $this->usersData[$login]['albums'] = array();
        $this->usersData[$login]['albumsTS'] = array();
        ksort($this->usersData);
        $this->save();
        return $ok;
    }

    //----------------------------------------------
    function deleteUser($login) {
        $login = trim(strtolower($login));
        if ($login==$this->admin || $login=='guest') {
            return false;
        }
        unset($this->usersData[$login]);
        $this->save();
        return $ok;
    }
}


?>
