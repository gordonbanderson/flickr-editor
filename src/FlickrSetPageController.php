<?php
namespace Suilven\Flickr;

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;


class FlickrSetPageController extends \PageController {



	function FlickrPhotos() {
		if (!isset($this->FlickrPics)) {
			$images = $this->FlickrSetForPage()->FlickrPhotos();
			$this->FlickrPics = $images;
		}

		return $this->FlickrPics;
	}



	/*
	I use this for highslide to replace the URLs in javascript if javascript is available, otherwise default to normal page URLs
	@return Mapping of silverstripe ID to URL
	*/
	function IdToUrlJson() {
		$result = array();
		foreach ($this->FlickrPhotos() as $fp) {
			$result[$fp->ID] = $fp->LargeURL;
		}

		return json_encode($result);
	}


	function HasGeo() {
		return $this->FlickrSetForPage()->HasGeo();
	}
}
