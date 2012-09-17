<?php
Object::useCustomClass('Image', 'RSImage');

File::$class_for_file_extension = array(
	'*' => 'File',
	'jpg' => 'RSImage',
	'jpeg' => 'RSImage',
	'png' => 'RSImage',
	'gif' => 'RSImage',
);