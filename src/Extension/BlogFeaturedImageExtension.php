<?php
namespace Suilven\Flickr\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

class BlogFeaturedImageExtension extends DataExtension
{
    private static $db = [
        'FeaturedFlickrImageID' => 'Varchar'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Flickr', [
            TextField::create('FeaturedFlickrImageID','ID of Flickr Image to Show as the default',
             'FeaturedImage'),
        ]);
    }
}
