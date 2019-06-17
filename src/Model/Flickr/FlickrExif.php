<?php
namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

/**
* Only show a page with login when not logged in
*/
class FlickrExif extends DataObject
{
    private static $table_name = 'FlickrExif';

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

    public function getCMSFields_forPopup()
    {
        $fields = new FieldSet();
        $fields->push(new TextField('Title', 'Title'));
        $fields->push(new TextField('Description'));
        return $fields;
    }
}