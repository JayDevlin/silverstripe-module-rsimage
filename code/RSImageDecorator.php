<?php

/**
 * - RSImageDecorator -
 * 
 * Change ClassName to RSImage after a image has been uploaded
 * 
 * @see Image->onAfterUpload()
 * @see AssetAdmin->doUpload()
 */
class RSImageDecorator extends DataObjectDecorator {
	/**
	 * Should be called after the file was uploaded
	 */
	function onAfterUpload() {
		$imageObj = $this->owner;
		if( !empty($imageObj) ) {
			DB::query("UPDATE File SET ClassName = 'RSImage' WHERE ID = '".(int)$imageObj->ID."'");
			
			$rsImageObj = DataObject::get_by_id('RSImage', (int)$imageObj->ID);
			$rsImageObj->deleteFormattedRSImages();
		}
	}
}
