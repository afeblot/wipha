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

// librariesData: array( id => array( 'path'=>path, 'name'=>name), ...)

require_once('persistent.php');
define('LIBRARIES_FILE', 'data/libraries.ser');
define('LIBRARIES_VERSION', 1);

define('LIBNAME_EXISTS', 1);
define('LIBPATH_EXISTS', 2);
define('PATH_NAME_REQ', 3);
define('LIB_ABS_PATH', 4);

class Libraries extends Persistent {

    var $librariesData;

    //----------------------------------------------
    function Libraries() {
        $this->librariesData=array(); // default value
        $this->version = USERS_VERSION;
        $this->Persistent(LIBRARIES_FILE);
        $this->open();
    }

    //----------------------------------------------
    function nb() {
        return count($this->librariesData);
    }

    //----------------------------------------------
    function lib($id) {
        return $this->librariesData[$id];
    }

    //----------------------------------------------
    function firstId() {
        reset($this->librariesData);
        list($id, $foo) = each($this->librariesData);
        return $id;
    }
    
     //----------------------------------------------
    function libList() {
        return $this->librariesData;
    }

    //----------------------------------------------
    function exists($id) {
        return isset($this->librariesData[$id]);
    }

    //----------------------------------------------
    function existsName($name) {
        $name = trim($name);
        foreach($this->librariesData as $lib) {
            if ($lib['name']==$name) return true;
        }
        return false;
    }

    //----------------------------------------------
    function existsPath($path) {
        $path = trim($path);
        foreach($this->librariesData as $lib) {
            if ($lib['path']==$path) return true;
        }
        return false;
    }

    //----------------------------------------------
    // $error: returned error value
    function newLibAllowed($path, $name, &$error) {
        $path = trim($path);
        $name = trim($name);
        if ($this->existsName($name)) {
            $error = LIBNAME_EXISTS;
            return false;
        } elseif ($this->existsPath($path)) {
            $error = LIBPATH_EXISTS;
            return false;
        }
        return true;
    }

    //----------------------------------------------
    // $error: returned error value
    function updateLib($id, $path, $name, &$error) {
        if (! isset($this->librariesData[$id])) return false;

        $path = trim($path);
        $name = trim($name);
        if ( ! empty($path)) {
            if ($this->existsPath($path) && $path != $this->librariesData[$id]['path']) {
                $error = LIBPATH_EXISTS;
                return false;
            } else {
                $this->librariesData[$id]['path'] = $path;
            }
        }
        if ( ! empty($name)) {
            if ($this->existsName($name) && $name != $this->librariesData[$id]['name']) {
                $error = LIBNAME_EXISTS;
                return false;
            } else {
                $this->librariesData[$id]['name'] = $name;
            }
        }
        
        $this->save();
        return true;
    }
    
    //----------------------------------------------
    function addLib($path, $name, &$error) {
        $path = trim($path);
        $name = trim($name);
        if ( empty($path) || empty($name)) {
            $error = PATH_NAME_REQ;
            return false;
        } elseif ( ! $this->newLibAllowed($path, $name, $error)) {
            return false;
        } elseif (substr($path, 0, 1)!='/') {
            $error = LIB_ABS_PATH;
            return false;
        }
        $id = time();   // easy way to get a unique id
        $this->librariesData[$id]['path'] = $path;
        $this->librariesData[$id]['name'] = $name;
        $this->librariesData[$id]['id'] = $id;
        $this->save();
        return $ok;
    }

    //----------------------------------------------
    function deleteLib($id) {
        unset($this->librariesData[$id]);
        $this->save();
        return $ok;
    }
}


?>
