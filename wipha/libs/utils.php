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

require_once('libs/http-conditional.php');

//----------------------------------------------
function _get_browser() {
    $browser = array ( //reversed array
    "CURL",
    "WGET",
    "W3C_VALIDATOR",
    "OPERA",
    "MSIE",            // parent
    "NETSCAPE",
    "FIREFOX",
    "IPHOTO",
    "THUNDERBIRD",
    "SHIIRA",
    "SAFARI",
    "WEBKIT",
    "CAMINO",
    "KONQUEROR",
    "MOZILLA"        // parent
    );

    $info['app'] = "OTHER";

    foreach ($browser as $parent) {
        if ( ($s = strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $parent)) !== FALSE ) {
            preg_match("%\\b\\w*($parent)\\w*(?:/|\\s)?([^\\s;]*)%i", $_SERVER['HTTP_USER_AGENT'], $matches);
            $info['app'] = strtoupper($matches[1]);
            $info['version'] = $matches[2];
            break; // first match wins
        }
    }
    if ($info['app']=='W3C_VALIDATOR' ||
        $info['app']=='CURL' ||
        $info['app']=='WGET') {
        $info['no_interaction'] = TRUE;
    }

    return $info;
}

//----------------------------------------------
// Supposed to be 2x faster and eats less memory
function _readfile($file) {
    $f = fopen($file, 'rb');
    if ($f) {
        while(!feof($f)) {
            $buffer = fread($f, 4096);
            print $buffer;
        }
        fclose($f);
    }
}

//----------------------------------------------
function reloadUrl($url) {
    session_write_close();
    header('Location: '.absUrl($url));
}

//----------------------------------------------
function absUrl($url) {
    if (strpos($url, 'http://')!==false) {
        return $url;
    }
    $absUrl = "http://" . $_SERVER['HTTP_HOST'];
    if (strpos($url, '/')===false) {
        $absUrl .= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'). "/" . $url;
    } else {
        $absUrl .= $url;
    }
    return $absUrl;
}

//----------------------------------------------
function urlWithLib($libId) {
    $query = $_SERVER['QUERY_STRING'];
    if ( ! empty($libId)) {
        $lib = "lib=$libId";
        $query = (empty($query)) ? $lib : "$query&$lib";
    }

    $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
    if ( ! empty($query)) {
        $url .= "?$query";
    }
    return $url;
}

//----------------------------------------------
function validUrl($validator, $libId=NULL) {
    $url = urlWithLib($libId);
    return $validator.urlencode($url);
}

//----------------------------------------------
function cssValidUrl() {
    $val = "http://jigsaw.w3.org/css-validator/validator?uri=";
    return validUrl($val);
}

//----------------------------------------------
function xhtmlValidUrl($libId) {
    $val = "http://validator.w3.org/check?uri=";
    return validUrl($val, $libId);
}

// //----------------------------------------------
// // Like chr() but also for unicode values
// function unichr($dec) {
//     if ($dec < 128) {
//         $utf = chr($dec);
//     } else if ($dec < 2048) {
//         $utf = chr(192 + (($dec - ($dec % 64)) / 64));
//         $utf .= chr(128 + ($dec % 64));
//     } else {
//         $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
//         $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
//         $utf .= chr(128 + ($dec % 64));
//     }
//     return $utf;
// }

//----------------------------------------------
// Returns a literal usable in a apple script for a unicode string
function applecriptUnicode($unicodeText) {
//     $left = unichr(171); // LEFT-POINTING DOUBLE ANGLE QUOTATION MARK
//     $right = unichr(187); // RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK
    $left = "Ç"; // LEFT-POINTING DOUBLE ANGLE QUOTATION MARK
    $right = "È"; // RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK
    return $left."data utxt".bin2hex(mb_convert_encoding($unicodeText, 'UTF-16BE', 'UTF-8')).$right;
}

//----------------------------------------------
// (Tries to) return the original file of a Mac alias
function getMacAliasOriginal($alias, &$error) {
    if (isOnLinux()) {
        return $alias;
    }
    $error = NULL;
    $size = @filesize($alias);
    if ($size===false) {
        $error = "Cannot access the file '$alias'";
        return NULL;
    } elseif ($size>0) {
        return $alias;
    }
    $original = shell_exec('./getTrueName "'.$alias.'"');
    if (empty($original)) {
        $error = "I can't resolve this Mac alias: '$alias'";
        return NULL;
    }
    return $original;
}

// //----------------------------------------------
// // (Tries to) return the original file of a Mac alias
// function getMacAliasOriginal($alias, &$error) {
//     $error = NULL;
//     $size = @filesize($alias);
//     if ($size===false) {
//         $error = "Can't access to the file";
//         return NULL;
//     } elseif ($size>0) {
//         return $alias;
//     }
//     $lines = explode(chr(10), shell_exec('strings -a "'.$alias.'/rsrc"'));
//     $nbLines = count($lines);
//     if ($lines[$nbLines-2]!="alis" &&$lines[$nbLines-3]!="alis") {
//         $error = "Seems not to be a mac alias (missing 'alis' as last info):".$lines[$nbLines-3];
//         return NULL;
//     }
//     switch ($nbLines) {
//         case 7:
//             $original = $lines[4];
//             $original = preg_replace('/^.?Users/', '/Users', $original);
//             break;
//         case 8:
//             $original = $lines[5].$lines[4];
//             break;
//         default:
//             $error = "Unknown Mac alias type with $nbLines infos";
//             foreach ($lines as $line) {
//                 if (preg_match('/^.?Users/', $line)) {
//                     $try = preg_replace('/^.?Users/', '/Users', $line);
//                     if (file_exists($try)) {
//                         $original = $try;
//                         $error = '';
//                         break;
//                     }
//                 }
//             }
//     }
//     return $original;
// }

//----------------------------------------------
// Debug function
function d(&$var) {
    echo '<pre>'; print_r($var); echo '</pre>';
}

//----------------------------------------------
function isOnLinux() {
    return (php_uname("s")=="Linux");
}

//----------------------------------------------
function controlCacheSize($maxCacheSize) {
    if ($maxCacheSize==0 || ! is_numeric($maxCacheSize)) { return; } // Security
    
    $cacheSize = exec("du -ks data/cache | cut -f 1")*1024;
    if ($cacheSize>$maxCacheSize) {
        $listFileInfos = array();
        exec("\\ls -ltr data/cache| awk '/jpg$/ {print $9 \",\" $5}'", $listFileInfos);
        while ((list ($key, $fileInfo) = each($listFileInfos)) && ($cacheSize>$maxCacheSize)) {
            list($file, $size) = explode(",", $fileInfo);
            unlink ("data/cache/$file");
            $cacheSize -= $size;
        }
    }
}

//----------------------------------------------
// $transfoParams = array('maxWidth'=>xx, ...)
//     maxWidth
//     maxHeight
//     ratio: necessary if both maxWidth and maxHeight are specified
//     quality ( 0=worst -> 100=best)
function sendImage($path, $cacheTime, $transfoParams=NULL) {
    $path = getMacAliasOriginal($path, $error);
    if ( ! isset($path)) {
        die($error);
    }
    if ( ! file_exists($path)) {
        die("Sorry, Can't find file '$path' (or is it a higher directory permission issue?)");
    }
    if ( ! is_readable($path)) {
        die("Sorry, file '$path' is not readable (permission issue)");
    }
    
    if (php_uname("s")=="Darwin") {
        $fnctArgs = sipsArgs;
        $fnctCmd = sipsCmd;
    } else {
        $fnctArgs = imgMagickArgs;
        $fnctCmd = imgMagickCmd;
    }

    $args = $fnctArgs($transfoParams);
    if ($args) {
        $cached = "data/cache/".md5("$path $args").".jpg";
        if ( (! file_exists($cached))||(filemtime($path)>filemtime($cached))) {
            $cmd = $fnctCmd($path, $args, $cached);
            @exec($cmd);
            if ( ! file_exists($cached)) { // cmd failed, but file may exist, so we will have to check again below
                die("Scale command failed ($cmd)");
            }
            chmod($cached, 0664);
            controlCacheSize($transfoParams['cachesize']);
        }
        if (file_exists($cached)) {
        $path = $cached;   
    }
        else {
            die("File was removed by cache control. You should setup a larger value in wipha.conf");
        }
    }

    session_write_close();
    
    header("Content-Type: image/jpeg");
    if ( ! httpConditional(filemtime($path), $cacheTime, 0, false, false, false)) {
        header("Content-Length: " . filesize($path));
        _readfile($path);
    }
    exit;
}

//----------------------------------------------
function videoMimetype($file) {
    $mimes = array(
        'quicktime' => 'mov,qt',
        'x-msvideo' => 'avi,vfw',
        'mp4'       => 'mp4,mpg4',
        'x-mpeg'    => 'mpeg,mpg,m1s,m1v,m1a,m75,m15,mp2,mpm,mpv,mpa',
        'divx'      => 'divx,div',
        'x-ms-asf'  => 'asf',
        'x-ms-wmv'  => 'wmv',
        'x-ms-wm'   => 'wm',
        'x-ms-wmp'  => 'wmp',
        'flc'       => 'flc,fli',
        '3gpp'      => '3gp,3gpp',
        '3gpp2'     => '3g2,3gp2',
        'sd-video'  => 'sdv',
        'x-m4v'     => 'm4v'
    );
    
    if (strrchr($file, ".")!==FALSE) {
        $extension = strtolower(array_pop(explode(".", $file)));
        foreach ($mimes as $mime=>$exts) {
            if (preg_match("/\\b$extension\\b/", $exts)) {
                return "video/$mime";
            }
        }
    }
    return "video/unknown";
}

//----------------------------------------------
function sendVideo($path, $cacheTime) {
    $path = getMacAliasOriginal($path, $error);
    if ( ! isset($path)) {
        die($error);
    }
    if ( ! file_exists($path)) {
        die("Sorry, Can't find file '$path' (or is it a higher directory permission issue?)");
    }
    if ( ! is_readable($path)) {
        die("Sorry, file '$path' is not readable (permission issue)");
    }
    
    session_write_close();
    
    $mimetype = videoMimetype($path);
    header("Content-Type: $mimetype");
    if ( ! httpConditional(filemtime($path), $cacheTime, 0, false, false, false)) {
        header("Content-Length: " . filesize($path));
        //header('Content-Disposition: attachment; filename="' . basename($path) .'"');
        _readfile($path);
    }
    exit;
}

//----------------------------------------------
function sipsArgs($transfoParams) {
    if (isset($transfoParams)) {
        $maxWidth  = $transfoParams['maxWidth'];
        $maxHeight = $transfoParams['maxHeight'];
        $ratio     = $transfoParams['ratio'];
        $quality   = $transfoParams['quality'];

        if ($maxWidth && ! $maxHeight) {
            $args = ' --resampleWidth '.intval($maxWidth);
        } elseif ( ! $maxWidth && $maxHeight) {
            $args = ' --resampleHeight '.intval($maxHeight);
        } elseif ($maxWidth && $maxHeight) {
            if ($maxHeight*$ratio<$maxWidth) {
                $args = ' --resampleHeight '.intval($maxHeight);
            } else {
                $args = ' --resampleWidth '.intval($maxWidth);
            }
        }

        if ($quality) {
            $args .= " -s formatOptions $quality%";
        }
        
        if ($args) {
            $args .= " -s format jpeg";
            return $args;
        }
    }
    return NULL;
}

//----------------------------------------------
function sipsCmd($path, $args, $cached) {
    return "/usr/bin/sips $args \"$path\" --out \"$cached\"";
}

//----------------------------------------------
function imgMagickArgs($transfoParams) {
    if (isset($transfoParams)) {
        $maxWidth  = $transfoParams['maxWidth'];
        $maxHeight = $transfoParams['maxHeight'];
        $quality   = $transfoParams['quality'];

        if ($maxWidth && $maxHeight) {
        $args = "-scale \"$maxWidth"."x".$maxHeight.">\"";
        if ($quality) {
            $args .= " -quality $quality";
        }
    return $args;
}
    }
    return "";
}

//----------------------------------------------
function imgMagickCmd($path, $args, $cached) {
    return "convert \"$path\" $args \"$cached\"";
    // If convert isn't in your path, explicit it here.
    // You may also set LD_LIBRARY_PATH if needed.
    // Exemple (bash style):
    //     return "LD_LIBRARY_PATH=/usr/local/ImageMagick-6.2.6/lib  /usr/local/ImageMagick-6.2.6/bin/convert \"$path\" $args \"$cached\"";
    // Exemple (csh style):
    //     return "setenv LD_LIBRARY_PATH=/usr/local/ImageMagick-6.2.6/lib; /usr/local/ImageMagick-6.2.6/bin/convert \"$path\" $args \"$cached\"";
    //
    // Exemple on MacOS, bash style
    //    return "DYLD_LIBRARY_PATH=/usr/local/ImageMagick-6.2.6/lib  /usr/local/ImageMagick-6.2.6/bin/convert \"$path\" $args \"$cached\"";
}

//----------------------------------------------
function store($var, $file) {
    $serialized = serialize($var);
    $um = umask(2);
    if ($f = @fopen("$file","w")) {
        if (@fwrite($f, $serialized)) {
            @fclose($f);
        } else die("Could not write to file $file");
    } else die("Could not open file $file");
    umask($um);
}

//----------------------------------------------
function retrieve($file) {
    return unserialize(file_get_contents($file));
}

?>
