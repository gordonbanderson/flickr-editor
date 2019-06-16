<?php
namespace Suilven\Flickr;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataExtension;

class FlickrSiteConfig extends DataExtension {

	private static $db = array(
		'ImageFooter' => 'Text',
		'AddLocation' => 'Boolean'
	);


	function updateCMSFields(FieldList $fields) {
		$fields->addFieldToTab("Root.Flickr", new TextareaField("ImageFooter", 'This text will be appended to all image descriptions'));
		$fields->addFieldToTab("Root.Flickr", new CheckboxField("AddLocation", 'Add a textual description of the location to all images'));//, 'Add the location as text to the picture');
		return $fields;
	}

}
