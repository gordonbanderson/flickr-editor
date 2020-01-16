<?php
namespace Suilven\Flickr\Model\Site;

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataList;
use Suilven\Flickr\FlickrPhotoSelectionField;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

/**
 * Defines the FlickrSetFolder page type
 */
class FlickrSetFolder extends \Page
{
    private static $table_name = 'FlickrSetFolder';

    private static $allowed_children = [
        FlickrSetPage::class,
        FlickrSetFolder::class,
        FlickrSearchPage::class
    ];

    private static $db = [
    'PromoteToHomePage' => DBBoolean::class
     ];

    private static $has_one = [
        'MainFlickrPhoto' => FlickrPhoto::class
    ];


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
