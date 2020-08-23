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
    /** @var string  */
    private static $table_name = 'FlickrFolder';

    /** @var string[]  */
    private static $allowed_children = [
        FlickrSetPage::class,
        FlickrFolder::class,
    ];

    /** @var array<string,string> */
    private static $has_one = [
        'CoverPhoto' => Image::class,
     ];


    public function getCMSFields(): \SilverStripe\Forms\FieldList
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab("Root.Content.CoverPhoto", new UploadField('CoverPhoto'));
        $fields->renameField("Content", "Brief Description");

        return $fields;
    }
}
