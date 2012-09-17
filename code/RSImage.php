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

}
