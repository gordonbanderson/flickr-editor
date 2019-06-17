<?php
namespace Suilven\Flickr;

use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;

/**
 * Defines the GalleryFolder page type
 */
class FlickrFolder extends \Page
{
    private static $allowed_children = array('FlickrSetPage', 'FlickrFolder');

    private static $has_one = array(
        'CoverPhoto' => Image::class,
     );


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab("Root.Content.CoverPhoto", new UploadField('CoverPhoto'));


        $fields->renameField("Content", "Brief Description");


        /*
        $fields->addFieldToTab('Root.Content.Main', new CalendarDateField('Date'), 'Content');
        $fields->addFieldToTab('Root.Content.Main', new TextField('Author'), 'Content');
        */
        return $fields;
    }
}
