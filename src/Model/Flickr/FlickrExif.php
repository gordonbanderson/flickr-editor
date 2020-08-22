<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

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
 * @method \SilverStripe\ORM\ManyManyList|array<\Suilven\Flickr\Model\Flickr\FlickrPhoto> FlickrPhotos()
 */
class FlickrExif extends DataObject
{
    private static $table_name = 'FlickrExif';

    private static $db = [
        'TagSpace' => 'Varchar',
        'Tag' => 'Varchar',
        'Label' => 'Varchar',
         'Raw' => 'Varchar',
        'TagSpaceID' => 'Int',
    ];

    private static $belongs_many_many = [
        'FlickrPhotos' => FlickrPhoto::class,
     ];

    private static $has_one = [
        'FlickrPhoto' => FlickrPhoto::class,
    ];

    public function getCMSFields_forPopup(): FieldSet
    {
        $fields = new FieldSet();
        $fields->push(new TextField('Title', 'Title'));
        $fields->push(new TextField('Description'));

        return $fields;
    }
}
