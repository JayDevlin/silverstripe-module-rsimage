<?php

/**
 * - RSImageTask -
 *
 * Convert all images to RSImages
 */
class RSImageTask extends BuildTask {

	protected $enabled = true;
	protected $title = "Convert all images to RSImages";
	
	function run($request) {
		DB::query("UPDATE File SET ClassName = 'RSImage' WHERE ClassName = 'Image';");
		DB::alteration_message('RSImage task done', 'created');
	}

}
