<?php
namespace Suilven\Flickr;

use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
/**
* Only show a page with login when not logged in
*/
class FlickrExif extends DataObject {

    private static $db = array(
		'TagSpace' => 'Varchar',
		'Tag' => 'Varchar',
		'Label' => 'Varchar',
		'Raw' => 'Varchar',
		'TagSpaceID' => 'Int'
	);

    private static $belongs_many_many = array(
		'FlickrPhotos' => 'FlickrPhoto'
	 );

    private static $has_one = array(
		'FlickrPhoto' => 'FlickrPhoto'
	);

	function getCMSFields_forPopup() {
		$fields = new FieldSet();
		$fields->push( new TextField( 'Title', 'Title' ) );
		$fields->push( new TextField( 'Description' ) );
		return $fields;
	}
}
