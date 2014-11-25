<?php
/**
 * Author: beautifultemplates
 * Author URI: http://www.beautiful-templates.com/
 * Classname: ST_File
 */
class ST_File extends ZipArchive {
	
	
      /** Add a Dir with Files and Subdirs to the archive;;;;; @param string $location Real Location;;;;  @param string $name Name in Archive;;;  @access private  **/
      public function addDir($location, $name) {
          $this->addEmptyDir($name);
          $this->addDirDo($location, $name);
       } // EO addDir;

       
      /**  Add Files & Dirs to archive;;;; @param string $location Real Location;  @param string $name Name in Archive;;;;;; 
       * @access private   **/
      private function addDirDo($location, $name) {
          $name .= '/';
          $location .= '/';
          // Read all Files in Dir
          $dir = opendir ($location);
          while ($file = readdir($dir))
          {
              if ($file == '.' || $file == '..') continue;
              // Rekursiv, If dir: FlxZipArchive::addDir(), else ::File();
              $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
              $this->$do($location . $file, $name . $file);
          }
      } // EO addDirDo();
      
      
	  /**
	   * Unzip @param $file, extract to @param $extractPath, delete $file after unzip, @return folder name
	   */
		public function st_unzip($file, $extractPath, $unlink) {
			$res = $this->open($file);
		    if ($res === TRUE) {
		        $this->extractTo($extractPath);
				$dir = trim($this->getNameIndex(0), '/');
				$this->close();
				if ($unlink == true){
					unlink($file);
				}
		        return $dir;
		    } else {
		        return FALSE;
		    }
		} 
	
	
	/**
	 * Copy all file and sub-folder from @param $src to @param $dst
	 */
	public function st_copy_all($src,$dst) { 
	    $dir = opendir($src); 
	    @mkdir($dst); 
	    while(false !== ( $file = readdir($dir)) ) { 
	        if (( $file != '.' ) && ( $file != '..' )) { 
	            if ( is_dir($src . '/' . $file) ) { 
	                $this->st_copy_all($src . '/' . $file,$dst . '/' . $file); 
	            } 
	            else { 
	                copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	        } 
	    } 
	    closedir($dir); 
	} 
	
	/**
	 * Delete all files and sub-folder in @param $folder
	 */
	 public function st_unlink($folder){
		 if (is_dir($folder)){
		 	$dir_handle = opendir($folder);
		 }
		 if (!$dir_handle){
		 	return false;
		 }
		      
		 while($file = readdir($dir_handle)) {
		       if ($file != "." && $file != "..") {
		            if (!is_dir($folder."/".$file)){
		            	unlink($folder."/".$file);
		            }else{
		            	$this->st_unlink($folder.'/'.$file);
					}
		       }
		 }
		 closedir($dir_handle);
		 rmdir($folder);
		 return true;
	 }
}