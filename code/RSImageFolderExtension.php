<?php

class RSImageFolderExtension extends DataExtension {

	public function onBeforeDelete() {
		$folder = $this->owner;

		// remove all children from database
		$children = $folder->AllChildren();
		foreach ($children as $child) {
			$child->delete();
		}

		Filesystem::removeFolder($folder->getFullPath());
	}

}
