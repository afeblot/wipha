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

/*
Base class to implement persistence
Datas are stored on a file, and also cached in the session.
The first attempt to get the variable will read the file.
Following attemps will just use the session data.

Usage:

    class foo extends Persistent
    {
       var $counter;
       function inc()
       {
           $this->counter++;
       }
    }

    $fooObj = new $foo;
    $foo->open();
    print $foo->counter; // displays incrementing integer as page reloads
    $foo->inc();
    $foo->save();

*/

class Persistent {
    var $filename;

    /**********************/
    function Persistent($filename) {
        $this->filename = $filename;
        if( ! file_exists($this->filename)) {
            $um = umask(2);
            $this->save();
            umask($um);
        }
    }

    /**********************/
    function save() {
        $serialized = serialize(get_object_vars($this));
        $_SESSION[$this->filename] = $serialized;
        if($f = @fopen($this->filename,"w")) {
            if(@fwrite($f, $serialized)) {
                @fclose($f);
            } else die("Could not write to file ".$this->filename." at Persistent::save");
        } else die("Could not open file ".$this->filename." for writing, at Persistent::save");
    }
    /**********************/
    function open($forceReadFile=false) {
        if (isset($_SESSION[$this->filename]) && ! $forceReadFile) {
            $serialized = $_SESSION[$this->filename];
        } else {
            $serialized = file_get_contents($this->filename);
            $_SESSION[$this->filename] = $serialized;
        }
        $vars = unserialize($serialized);
        foreach($vars as $key=>$val) {           
            eval("$"."this->$key = $"."vars['"."$key'];");
        }
    }
}

?>
