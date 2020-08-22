<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Site;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\FieldType\DBBoolean;
use Suilven\Flickr\FlickrPhotoSelectionField;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

/**
 * Defines the FlickrSetFolder page type
 *
 * @property bool $PromoteToHomePage
 * @property int $MainFlickrPhotoID
 * @method \Suilven\Flickr\Model\Flickr\FlickrPhoto MainFlickrPhoto()
 */
class FlickrSetFolder extends \Page
{
    private static $table_name = 'FlickrSetFolder';

    private static $allowed_children = [
        FlickrSetPage::class,
        FlickrSetFolder::class,
        FlickrSearchPage::class,
    ];

    private static $db = [
    'PromoteToHomePage' => DBBoolean::class,
     ];

    private static $has_one = [
        'MainFlickrPhoto' => FlickrPhoto::class,
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
     *
     * @example
     * <code>
     *  return $this->NewsItemImage;
     * </code>
     */
    public function getPortletImage(): string
    {
        return $this->MainFlickrPhoto();
    }


    /**
     * An accessor for text associated with the portlet
     *
     * @example
     * <code>
     * return $this->Summary
     * </code>
     */
    public function getPortletCaption(): string
    {
        return $this->Title;
    }
}
