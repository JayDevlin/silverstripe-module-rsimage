<?php
/**
 * Remove all cached/generated images that have been created as the result of a manipulation method being called on a
 * {@link RSImage} object
 * 
 * @see FlushGeneratedImagesTask
 */
class FlushGeneratedRSImagesTask extends BuildTask {
  
	protected $title = 'Flush Generated RSImages Task';
	
	/**
	 * Check that the user has appropriate permissions to execute this task
	 */
	public function init() {
		if(!Director::is_cli() && !Director::isDev() && !Permission::check('ADMIN')) {
			return Security::permissionFailure();
		}
		
		parent::init();
	}
	
	/**
	 * Actually clear out all the images
	 */
	public function run($request) {
		$processedImages = 0;
		$removedItems    = array();
		
		if($images = DataObject::get('RSImage')) {
			foreach($images as $image) {
				$removedItems = array_merge(
						$removedItems,
						$image->deleteFormattedRSImages()
				);

				$processedImages++;
			}
		}
		
		$removedItemsCount = count($removedItems);
		
		DB::alteration_message("Removed $removedItemsCount generated images from $processedImages Image objects stored in the Database.", 'created');
	}
	
}
