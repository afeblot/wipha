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

BASED ON

 +--------------------------------------------------
 | TAR/GZIP/BZIP2/ZIP ARCHIVE CLASSES 2.1
 | By Devin Doucette
 | Copyright (c) 2005 Devin Doucette
 | Email: darksnoopy@shaw.ca
 +--------------------------------------------------
 | Email bugs/suggestions to darksnoopy@shaw.ca
 +--------------------------------------------------
 | This script has been created and released under
 | the GNU GPL and is free to use and redistribute
 | only if this copyright statement is not removed
 +--------------------------------------------------
*/

class zipper {
        var $name;
		var $files;
		var $offset;
		var $central;
		var $level;
		var $comment;
        var $archive;
        var $doStream;  // Don't bufferize data. Immediately send zipped data to the browser.

    //---------------------------------------
    function zipper($name, $compressionLevel=6, $doStream=false) {
        $this->clean();
        $this->name = $name;
		if($compressionLevel<0) $compressionLevel=0;
		if($compressionLevel>9) $compressionLevel=9;
        $this->level = $compressionLevel;
        $this->doStream = $doStream;
        if ($doStream) {
            $this->sendHeaders();
        }
    }

    //---------------------------------------
    function setComment($comment) {
        $this->comment = $comment;
    }

    //---------------------------------------
    function addData(&$data, $destFile, $flagCompress=true) {
		$timedate = explode(" ", date("Y n j G i s", time()));
		$timedate = ($timedate[0] - 1980 << 25) |
                    ($timedate[1] << 21) |
                    ($timedate[2] << 16) |
			        ($timedate[3] << 11) |
                    ($timedate[4] << 5) |
                    ($timedate[5]);

		$stream = pack("VvvvV", 0x04034b50, 0x000A, 0x0000, $flagCompress ? 0x0008 : 0x0000, $timedate);
        $size = strlen($data);

		$crc32 = crc32($data);
		if ($flagCompress) {
			$data = gzcompress($data, $this->level);
			$compressedSize = strlen($data) - 6;
			$data = substr($data, 2, $compressedSize);
		} else {
			$compressedSize = strlen($data);
        }
		$stream .= pack("VVVvv", $crc32, $compressedSize, $size, strlen($destFile), 0x0000);
		$stream .= $destFile;
		$stream .= $data;
		$this->central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $flagCompress ? 0x000A : 0x0000, 0x0000,
			$flagCompress ? 0x0008 : 0x0000, $timedate,
			$crc32, $compressedSize, $size, strlen($destFile), 0x0000, 0x0000, 0x0000, 0x0000, 0x00000000, $this->offset);
		$this->central .= $destFile;
		$this->files++;
		$this->offset += (30 + strlen($destFile) + $compressedSize);
		$this->flush($stream);
    }

    //---------------------------------------
    function addFile($srcFile, $destFile="", $flagCompress=true) {
        if ($fp = @fopen($srcFile, "rb")) {
            $data = fread($fp, filesize($srcFile));
		    if(!trim($destFile)) {
                $destFile = $srcFile;
            }
            fclose($fp);
            $this->addData($data, $destFile, $flagCompress);
        }
    }

    //---------------------------------------
    function close() {
        $this->finishArchive();
		if ( ! $this->doStream) {
            $this->sendHeaders();
            print $this->archive;
        }
        $this->clean();
    }

    //---------------------------------------
    function sendHeaders() {
        flush();    // necessary for headers_sent()
        // This is to enable a multipart send.
        if (headers_sent()) {
            print "Content-Type: application/zip\n";
		    print "Content-Disposition: attachment; filename=\"$this->name\"\n";
		    if ( ! $this->doStream) {
                print "Content-Length: " . strlen($this->archive) ."\n";
            }
		    print "Content-Transfer-Encoding: binary\n";
		    print "Cache-Control: no-cache, must-revalidate, max-age=60\n";
		    print "Expires: Sat, 01 Jan 2000 12:00:00 GMT\n";
		    print "\n";
        } else {
            header("Content-Type: application/zip");
		    header("Content-Disposition: attachment; filename=\"$this->name\"");
		    if ( ! $this->doStream) {
                header("Content-Length: " . strlen($this->archive));
            }
		    header("Content-Transfer-Encoding: binary");
		    header("Cache-Control: no-cache, must-revalidate, max-age=60");
		    header("Expires: Sat, 01 Jan 2000 12:00:00 GMT");
        }
    }

    //---------------------------------------
    function finishArchive() {
		$stream = $this->central;
		$stream .= pack("VvvvvVVv", 0x06054b50, 0x0000, 0x0000, $this->files, $this->files, strlen($this->central), $this->offset,
			!empty ($this->comment) ? strlen($this->comment) : 0x0000);

		if (!empty ($this->comment)) {
			$stream .= $this->comment;
        }
		$this->flush($stream);
    }

    //---------------------------------------
    function clean() {
		$this->name = "";
		$this->files = 0;
		$this->offset = 0;
		$this->central = "";
        $this->name = "";
		$this->level = 6;
		$this->comment = "";
		$this->archive = "";
		$this->doStream = false;
    }

    //---------------------------------------
    function flush(&$data) {
		if ($this->doStream) {
            print $data;
        } else {
            $this->archive .= $data;
        }
    }
}

?>
