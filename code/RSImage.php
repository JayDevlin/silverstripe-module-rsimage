<?php

/**
 * Overload Image->cacheFilename() to change the cache location
 * 
 * @see Image->cacheFilename()
 * @see Image->deleteFormattedImages()
 */
class RSImage extends Image {
	
	/**
	 * Return the filename for the cached image, given it's format name and arguments.
	 * @param string $format The format name.
	 * @return string
	 */
	public function cacheFilename($format) {
		$args = func_get_args();
		array_shift($args);
		$folder = $this->ParentID ? $this->Parent()->Filename : ASSETS_DIR . "/";
		
		$format = $format.implode('', $args);
		
		return $folder . '_' . $format . '/' . $this->Name;
	}
	
	function onBeforeWrite() {
		parent::onBeforeWrite();

		$changedFields = $this->getChangedFields();
		if( !empty($changedFields['Name']['before']) ) {
			$this->deleteFormattedRSImages($changedFields['Name']['before']);
		}
	}
	
	function onBeforeDelete() {
		parent::onBeforeDelete();
		
		$this->deleteFormattedRSImages();
	}

	function onAfterUpload() {
		parent::onAfterUpload();
		
		$this->deleteFormattedRSImages();
	}
	
	/**
	 * Remove all of the formatted cached images for this image.
	 * 
	 * @param string $name The file name
	 * @return array Paths of deleted formatted images
	 */
	function deleteFormattedRSImages($name='') {
		if(!$this->Filename) return array();
		if(empty($name)) $name = $this->Name;
		$filesDeleted = array();
		
		// get parent folder
		$folderName = $this->ParentID ? $this->Parent()->Filename : ASSETS_DIR . '/';
		$folder = Director::getAbsFile($folderName);
		
		// get all child folders with an underscore as the first character
		$cacheFolders = array();
		if(is_dir($folder)) {
			$handle = opendir($folder);
			if($handle) {
				while(($cacheFolder = readdir($handle)) !== false) {
					if(substr($cacheFolder, 0, 1) == '_') {
						$cacheFolders[] = $cacheFolder;
					}
				}
				closedir($handle);
			}
		}
		
		// get all image generate methods
		$methodNames = $this->allMethodNames();
		foreach($methodNames as $methodName) {
			if(substr($methodName, 0, 8) == 'generate') {
				$format = substr($methodName, 8);
				$generateFuncs[] = preg_quote($format);
			}
		}
		$generateFuncs[] = 'resampled';
		
		// All generate functions may appear any number of times in the image cache name.
		$generateFuncs = implode('|', $generateFuncs);
		$pattern = "/^(\_({$generateFuncs})\d*)$/i";
		
		// unlink image in each cache folder
		foreach($cacheFolders as $cacheFolder) {
			if(preg_match($pattern, $cacheFolder)) {
				$cacheFolder = Director::getAbsFile($folderName . $cacheFolder . '/');
				if(Director::fileExists($cacheFolder . $name)) {
					unlink($cacheFolder . $name);
					$filesDeleted[] = $cacheFolder . $name;
				}
				
			}
		}
		
		return $filesDeleted;
	}

}
