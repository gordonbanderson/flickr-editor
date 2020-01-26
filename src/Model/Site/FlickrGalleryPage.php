<?php
namespace Suilven\Flickr\Model\Site;

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use Suilven\Flickr\Model\Flickr\FlickrGallery;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

/**
 * Class \Suilven\Flickr\Model\Site\FlickrGalleryPage
 *
 * @property int $FlickrGalleryForPageID
 * @method \Suilven\Flickr\Model\Flickr\FlickrGallery FlickrGalleryForPage()
 */
class FlickrGalleryPage extends FlickrSetPage
{
    private static $table_name = 'FlickrGalleryPage';

    private static $has_one = [
        'FlickrGalleryForPage' => FlickrGallery::class
    ];

    public function getFlickrImageCollectionForPage()
    {
        return $this->FlickrGalleryForPage();
    }
}
