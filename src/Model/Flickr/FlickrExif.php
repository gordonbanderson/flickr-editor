<?php
namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

/**
 * Only show a page with login when not logged in
 *
 * @property string $TagSpace
 * @property string $Tag
 * @property string $Label
 * @property string $Raw
 * @property int $TagSpaceID
 * @property int $FlickrPhotoID
 * @method \Suilven\Flickr\Model\Flickr\FlickrPhoto FlickrPhoto()
 * @method \SilverStripe\ORM\ManyManyList|\Suilven\Flickr\Model\Flickr\FlickrPhoto[] FlickrPhotos()
 */
class FlickrExif extends DataObject
{
    private static $table_name = 'FlickrExif';

    private static $db = array(
        'TagSpace' => 'Varchar',
        'Tag' => 'Varchar',
        'Label' => 'Varchar',
        'Raw' => 'Varchar',
        'TagSpaceID' => 'Int'
    );

    private static $belongs_many_many = array(
        'FlickrPhotos' => FlickrPhoto::class
     );

    private static $has_one = array(
        'FlickrPhoto' => FlickrPhoto::class
    );

    public function getCMSFields_forPopup()
    {
        $fields = new FieldSet();
        $fields->push(new TextField('Title', 'Title'));
        $fields->push(new TextField('Description'));
        return $fields;
    }
}
