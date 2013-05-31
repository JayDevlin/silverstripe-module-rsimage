<?php

/**
 * Convert all existing images to RSImage
 */
class RSImageTask extends BuildTask {

	protected $enabled = true;
	
	function run($request) {
		DB::query("UPDATE File SET ClassName = 'RSImage' WHERE ClassName = 'Image';");
		DB::alteration_message('RSImage task done', 'created');
	}

}