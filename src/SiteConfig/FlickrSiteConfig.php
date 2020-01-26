<?php
namespace Suilven\Flickr\SiteConfig;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataExtension;

/**
 * Class \Suilven\Flickr\SiteConfig\FlickrSiteConfig
 *
 * @property \SilverStripe\SiteConfig\SiteConfig|\Suilven\Flickr\SiteConfig\FlickrSiteConfig $owner
 * @property string $ImageFooter
 * @property boolean $AddLocation
 */
class FlickrSiteConfig extends DataExtension
{
    private static $db = array(
        'ImageFooter' => 'Text',
        'AddLocation' => 'Boolean'
    );


    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab("Root.Flickr", new TextareaField("ImageFooter", 'This text will be appended to all image descriptions'));
        $fields->addFieldToTab("Root.Flickr", new CheckboxField("AddLocation", 'Add a textual description of the location to all images'));//, 'Add the location as text to the picture');
        return $fields;
    }
}
