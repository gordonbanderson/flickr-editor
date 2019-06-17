<?php
namespace Suilven\Flickr;

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataList;

/**
 * Defines the FlickrSetFolder page type
 */
class FlickrSetFolder extends \Page
{
    private static $allowed_children = array('FlickrSetPage', 'FlickrSetFolder');

    private static $db = array(
    'PromoteToHomePage' => DBBoolean::class
     );


    private static $has_one = array('MainFlickrPhoto' => 'FlickrPhoto');



    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.CoverPhoto', new FlickrPhotoSelectionField('MainFlickrPhotoID', 'Cover Photo', $this->MainFlickrPhoto()));


        $fields->renameField("Content", "Brief Description");
        $fields->addFieldToTab("Root.HomePage", new CheckboxField('PromoteToHomePage', 'Promote to Home Page'));

        return $fields;
    }


    public function getPortletTitle()
    {
        return $this->Title;
    }


    /**
     * An accessor method for an image for a portlet
     * @example
     * <code>
     *  return $this->NewsItemImage;
     * </code>
     *
     * @return string
     */
    public function getPortletImage()
    {
        return $this->MainFlickrPhoto();
    }


    /**
     * An accessor for text associated with the portlet
     * @example
     * <code>
     * return $this->Summary
     * </code>
     *
     * @return string
     */
    public function getPortletCaption()
    {
        return $this->Title;
    }
}
