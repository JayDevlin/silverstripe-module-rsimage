<?php

/**
 * - RSImage -
 *
 * Overload Image->cacheFilename() to change the cache folder
 */
class RSImage extends Image {

	/**
	 * Return the filename for the cached image, given it's format name and arguments.
	 * @param string $format The format name.
	 * @param string $arg1 The first argument passed to the generate function.
	 * @param string $arg2 The second argument passed to the generate function.
	 * @return string
	 */
	function cacheFilename($format, $arg1 = null, $arg2 = null) {
		$folder = $this->ParentID ? $this->Parent()->Filename : ASSETS_DIR . "/";

		$format = $format . $arg1 . $arg2;

		return $folder . "_" . $format . "/" . $this->Name;
		//return $folder . "_resampled/$format-" . $this->Name;
	}
	
	protected function onBeforeDelete() {
		parent::onBeforeDelete();
		
		$this->deleteFormattedRSImages();
	}
	
	/**
	 * Remove all of the formatted cached images for this image.
	 * @return array - paths of deleted formatted images
	 */
	function deleteFormattedRSImages() {
		if(!$this->Filename) return array();
		
		$filesDeleted = array();
		$methodNames = array_merge($this->allMethodNames(), get_class_methods('RSImage'));
		$cacheFolders = array();
		
		$folderName = $this->ParentID ? $this->Parent()->Filename : ASSETS_DIR . '/';
		$folder = Director::getAbsFile($folderName);
		
		if(is_dir($folder)) {
			if($handle = opendir($folder)) {
				while(($file = readdir($handle)) !== false) {
					if(substr($file, 0, 1) == '_') {
						$cacheFolders[] = $file;
					}
				}
				closedir($handle);
			}
		}
		
		foreach($methodNames as $methodName) {
			if(substr($methodName, 0, 8) == 'generate') {
				$format = substr($methodName, 8);
				foreach($cacheFolders as $cacheFolder) {
					if(strstr($cacheFolder, '_'.$format)) {
						$cacheFolder = Director::getAbsFile($folderName . $cacheFolder . '/');
						if(Director::fileExists($cacheFolder . $this->Name)) {
							unlink($cacheFolder . $this->Name);
							$filesDeleted[] = $cacheFolder . $this->Name;
						}
					}
				}
			}
		}
		
		return $filesDeleted;
	}

}
