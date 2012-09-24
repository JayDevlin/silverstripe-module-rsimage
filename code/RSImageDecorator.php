<?php

/**
 * - RSImageDecorator -
 * 
 * Change ClassName to RSImage after a image has been uploaded
 */
class RSImageDecorator extends DataObjectDecorator {
	function onAfterUpload() {
		DB::query("UPDATE File SET ClassName = 'RSImage' WHERE ClassName = 'Image';");
	}
}
