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

// define('BASE_DIR', dirname(__FILE__).'/../');
define('BASE_DIR', implode("/", array_slice(explode("/", __FILE__), 0, -2)).'/');
define('APP_NAME', implode("/", array_slice(explode("/", $_SERVER['REQUEST_URI']), -2, -1)));

// put full path to Smarty.class.php
require_once(BASE_DIR.'libs/smarty/Smarty.class.php');


class SmartyApp extends Smarty {
    function SmartyApp() {        
        $this->compile_dir  = '/tmp/smarty/'.APP_NAME.'/templates_c';
        $this->cache_dir    = '/tmp/smarty/'.APP_NAME.'/cache';
        $this->template_dir = BASE_DIR.'/templates';
        $this->config_dir   = BASE_DIR.'/configs';

        if ( ! file_exists('/tmp/smarty')) {
                     mkdir('/tmp/smarty');
        }
        if ( ! file_exists('/tmp/smarty/'.APP_NAME)) {
                     mkdir('/tmp/smarty/'.APP_NAME);
        }
        if ( ! file_exists($this->compile_dir)) {
                     mkdir($this->compile_dir);
        }
        if ( ! file_exists($this->cache_dir)) {
                     mkdir($this->cache_dir);
        }
    }
}

?>
