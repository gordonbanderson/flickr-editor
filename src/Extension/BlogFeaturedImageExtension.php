<?php declare(strict_types = 1);

namespace Suilven\Flickr\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

/**
 * Class \Suilven\Flickr\Extension\BlogFeaturedImageExtension
 *
 * @property \Suilven\Flickr\Extension\BlogFeaturedImageExtension $owner
 * @property string $FeaturedFlickrImageID
 */
class BlogFeaturedImageExtension extends DataExtension
{
    private static $db = [
        'FeaturedFlickrImageID' => 'Varchar',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldsToTab('Root.Flickr', [
            TextField::create(
                'FeaturedFlickrImageID',
                'ID of Flickr Image to Show as the default',
                'FeaturedImage',
            ),
        ]);
    }


    public function getFeaturedFlickrImage()
    {
        return empty($this->owner->FeaturedFlickrImageID) ?
            null : FlickrPhoto::get()->filter('FlickrID', $this->owner->FeaturedFlickrImageID)->first();
    }
}
