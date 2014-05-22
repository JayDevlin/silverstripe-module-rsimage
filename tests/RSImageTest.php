<?php

class RSImageTest extends FunctionalTest {

	public function testClassForFileExtensionOnUpload() {
		$origConfig = File::config()->class_for_file_extension;

		// test default
		File::config()->class_for_file_extension = array('png' => 'Image');
		$file1 = $this->mockFileUpload('RSImageTest-testUpload.png');
		$this->assertFileExists(
			$file1->getFullPath(),
			'File upload to standard directory in /assets'
		);
		$this->assertTrue(
			$file1->class === 'Image',
			'File is an ' . $file1->class
		);
		$file1->delete();

		// test RSImage
		File::config()->class_for_file_extension = array('png' => 'RSImage');
		$file2 = $this->mockFileUpload('RSImageTest-testUpload.png');
		$this->assertFileExists(
			$file2->getFullPath(),
			'File upload to standard directory in /assets'
		);
		$this->assertTrue(
			$file2->class === 'RSImage',
			'File is a RSImage'
		);
		$file2->delete();

		File::config()->class_for_file_extension = $origConfig;
	}

	public function testGenerateMethods() {
		$imageClass = new Image();
		$image = $this->mockFileUpload('RSImageTest-testUpload.png');

		// get all image generate methods
		$generateFuncs = array();
		$methodNames = $imageClass->allMethodNames(true);
		foreach ($methodNames as $methodName) {
			if (substr($methodName, 0, 8) == 'generate' && $methodName != 'generateformattedimage') {
				$format = substr($methodName, 8);
				$generateFuncs[] = preg_quote($format);
			}
		}

		// test each generate method
		foreach ($generateFuncs AS $generateFunc) {
			$resized = $image->$generateFunc(300, 300);
			$this->assertFileExists(
				$resized->getFullPath(),
				$resized->getFullPath() . ' exists'
			);
		}

		$image->delete();
	}

	public function testDeleteFormattedRSImages() {
		$imageClass = new Image();
		$image = $this->mockFileUpload('RSImageTest-testUpload.png');

		// get all image generate methods
		$generateFuncs = array();
		$methodNames = $imageClass->allMethodNames(true);
		foreach ($methodNames as $methodName) {
			if (substr($methodName, 0, 8) == 'generate' && $methodName != 'generateformattedimage') {
				$format = substr($methodName, 8);
				$generateFuncs[] = preg_quote($format);
			}
		}

		// get paths for each generate method
		$paths = array();
		foreach ($generateFuncs AS $generateFunc) {
			$resized = $image->$generateFunc(300, 300);
			$paths[] = $resized->getFullPath();
		}

		// delete original file
		$image->delete();

		// test that cache files are removed too
		foreach ($paths AS $path) {
			$this->assertTrue(
				!file_exists($path),
				$path . ' does not exist'
			);
		}
	}

	public function testImageRename() {
		$upload = $this->mockFileUpload('RSImageTest-testUpload.png');
		$image = Image::get()->byID($upload->ID);
		$origFilename = $image->Filename;

		// generate a formatted image
		$origFormattedImage = $image->setWidth(300);

		// rename file
		$image->Name = 'RSImageTest-testUpload-changed.png';

		// test that changes got detected
		$this->assertEquals(
				$image->getChangedFields(),
				array(
					'Name' => array(
						'before' => 'RSImageTest-testUpload.png',
						'after' => 'RSImageTest-testUpload-changed.png',
						'level' => '2',
					),
					'Filename' => array(
						'before' => 'assets/Uploads/RSImageTest/RSImageTest-testUpload.png',
						'after' => 'assets/Uploads/RSImageTest/RSImageTest-testUpload-changed.png',
						'level' => '2',
					),
				),
				'Changed fields are correctly detected'
		);

		// write changes
		$image->write();

		// test that database record has changed
		$this->assertTrue(
			$image->Filename !== $origFilename,
			'Filename has changed'
		);

		// test that the renamed file exists
		$this->assertFileExists(
			$image->getFullPath(),
			'Image exists'
		);

		// test that original file got removed
		$this->assertTrue(
			!file_exists(Director::getAbsFile($origFilename)),
			'Image does not exist'
		);

		// test that the formatted image of the original file got removed as well
		$this->assertTrue(
			!file_exists($origFormattedImage->getFullPath()),
			'Formatted image does not exist'
		);

		$image->delete();
	}

	public function testFolderDelete() {
		$upload = $this->mockFileUpload('RSImageTest-testUpload.png');
		$testFolder = Folder::get()->filter('Name', 'RSImageTest')->first();
		$testFolderPath = $testFolder->getFullPath();
		$testFolder->delete();

		$this->assertTrue(
			!file_exists($upload->getFullPath()),
			'File does not exist'
		);

		$this->assertTrue(
			!file_exists($testFolderPath),
			'Folder does not exist'
		);
	}

	public function tearDownOnce() {
		parent::tearDownOnce();

		$testfolder = Folder::get()->filter('Name', 'RSImageTest')->first();
		if (!empty($testfolder->ID)) {
			$testfolder->delete();
		}

		if (file_exists(ASSETS_PATH . '/Uploads/RSImageTest/')) {
			Filesystem::removeFolder(ASSETS_PATH . '/Uploads/RSImageTest/');
		}
	}

	protected function mockFileUpload($tmpFileName = 'RSImageTest-testUpload.png') {
		// get tmp file
		$tmpFilePath = dirname(__FILE__) . '/RSImageTest.png';

		// emulates the $_FILES array
		$tmpFile = array(
			'name' => $tmpFileName,
			'type' => 'text/plaintext',
			'size' => filesize($tmpFilePath),
			'tmp_name' => $tmpFilePath,
			'extension' => 'txt',
			'error' => UPLOAD_ERR_OK,
		);

		// test upload into folder
		$u1 = new Upload();
		$u1->load($tmpFile, '/Uploads/RSImageTest/');

		return $u1->getFile();
	}

}
