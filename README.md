# RSImage

Overloads Image->cacheFilename() so the filename of a formatted image stays the same as the original

## Image->Filename:
	assets/Uploads/filename.jpg

## Image->cachedFilename():
	assets/Uploads/_resampled/croppedImage800600-filename.jpg

## RSImage->cachedFilename():
	assets/Uploads/_croppedImage800600/filename.jpg