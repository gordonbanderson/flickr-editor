<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use Suilven\Flickr\FlickrSetPage;

/**
 * Defines the GalleryFolder page type
 *
 * @property int $CoverPhotoID
 * @method \SilverStripe\Assets\Image CoverPhoto()
 */
class FlickrFolder extends \Page
{
    private static $table_name = 'FlickrFolder';

    private static $allowed_children = [
        FlickrSetPage::class,
        FlickrFolder::class,
    ];

    private static $has_one = [
        'CoverPhoto' => Image::class,
     ];


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
