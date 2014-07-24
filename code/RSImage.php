<?php

/**
 * Overload Image->cacheFilename() to change the cache location
 * 
 * @see Image->cacheFilename()
 * @see Image->deleteFormattedImages()
 */
class RSImage extends Image {
	/**
	 * Applied formats of a generated image
	 * @var array
	 */
	protected $formats = array();
	
	/**
	 * Return an image object representing the image in the given format.
	 * This image will be generated using generateFormattedImage().
	 * The generated image is cached, to flush the cache append ?flush=1 to your URL.
	 * 
	 * Just pass the correct number of parameters expected by the working function
	 * 
	 * @param string $format The name of the format.
	 * @return Image_Cached
	 */
	public function getFormattedImage($format) {
		$args = func_get_args();
		
		if($this->ID && $this->Filename && Director::fileExists($this->Filename)) {
			$cacheFile = call_user_func_array(array($this, "cacheFilename"), $args);
			
			if(!file_exists(Director::baseFolder()."/".$cacheFile) || isset($_GET['flush'])) {
				call_user_func_array(array($this, "generateFormattedImage"), $args);
			}
			
			$cached = new RSImage_Cached($cacheFile);
			// Pass through the title so the templates can use it
			$cached->Title = $this->Title;
			// Pass through the parent, to store cached images in correct folder.
			$cached->ParentID = $this->ParentID;
			$cached->formats = $this->formats;
			$cached->formats[] = implode('', $args);
			
			return $cached;
		}
	}

	/**
	 * Return the filename for the cached image, given it's format name and arguments.
	 * @param string $format The format name.
	 * @return string
	 */
	public function cacheFilename($format) {
		$args = func_get_args();
		$folder = $this->ParentID ? $this->Parent()->Filename : ASSETS_DIR . "/";

		$formats = $this->formats;
		$formats[] = implode('', $args);
		
		return $folder . '_' . implode('_', $formats) . '/' . $this->Name;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();

		$changedFields = $this->getChangedFields();
		if (!empty($changedFields['Name']['before'])) {
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
	function deleteFormattedRSImages($name = '') {
		$filesDeleted = array();
		if (!$this->Filename) {
			return $filesDeleted;
		}
		if (empty($name)) {
			$name = $this->Name;
		}

		// get parent folder
		$folderName = $this->ParentID ? $this->Parent()->Filename : ASSETS_DIR . '/';
		$folder = Director::getAbsFile($folderName);

		// get all child folders with an underscore as the first character
		$cacheFolders = array();
		if (is_dir($folder)) {
			$handle = opendir($folder);
			if ($handle) {
				while (($cacheFolder = readdir($handle)) !== false) {
					if (substr($cacheFolder, 0, 1) == '_') {
						$cacheFolders[] = $cacheFolder;
					}
				}
				closedir($handle);
			}
		}

		// get all image generate methods
		$methodNames = $this->allMethodNames();
		foreach ($methodNames as $methodName) {
			if (substr($methodName, 0, 8) == 'generate') {
				$format = substr($methodName, 8);
				$generateFuncs[] = preg_quote($format);
			}
		}
		$generateFuncs[] = 'resampled';

		// All generate functions may appear any number of times in the image cache name.
		$generateFuncs = implode('|', $generateFuncs);
		$pattern = "/^(\_({$generateFuncs}).*)$/i";

		// unlink image in each cache folder
		foreach ($cacheFolders as $cacheFolder) {
			if (preg_match($pattern, $cacheFolder)) {
				$cacheFolder = Director::getAbsFile($folderName . $cacheFolder . '/');
				if (Director::fileExists($cacheFolder . $name)) {
					unlink($cacheFolder . $name);
					$filesDeleted[] = $cacheFolder . $name;
				}
			}
		}

		return $filesDeleted;
	}

}

/**
 * A resized / processed {@link Image} object.
 * When Image object are processed or resized, a suitable Image_Cached object is returned, pointing to the
 * cached copy of the processed image.
 *
 * @package framework
 * @subpackage filesystem
 */
class RSImage_Cached extends RSImage {
	
	/**
	 * Create a new cached image.
	 * @param string $filename The filename of the image.
	 * @param boolean $isSingleton This this to true if this is a singleton() object, a stub for calling methods.
	 *                             Singletons don't have their defaults set.
	 */
	public function __construct($filename = null, $isSingleton = false) {
		parent::__construct(array(), $isSingleton);
		$this->ID = -1;
		$this->Filename = $filename;
	}
	
	public function getRelativePath() {
		return $this->getField('Filename');
	}
	
	/**
	 * Prevent creating new tables for the cached record
	 *
	 * @return false
	 */
	public function requireTable() {
		return false;
	}	
	
	/**
	 * Prevent writing the cached image to the database
	 *
	 * @throws Exception
	 */
	public function write($showDebug = false, $forceInsert = false, $forceWrite = false, $writeComponents = false) {
		throw new Exception("{$this->ClassName} can not be written back to the database.");
	}
}