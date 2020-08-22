<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Only show a page with login when not logged in
 *
 * @property string $Value
 * @property string $FlickrID
 * @property string $RawValue
 * @method \SilverStripe\ORM\ManyManyList|array<\Suilven\Flickr\Model\Flickr\FlickrBucket> FlickrBuckets()
 * @method \SilverStripe\ORM\ManyManyList|array<\FlickrPhoto> FlickrPhotos()
 */
class FlickrTag extends DataObject
{
    private static $table_name = 'FlickrTag';

    private static $db = [
        'Value' => 'Varchar',
        'FlickrID' => 'Varchar',
        'RawValue' => 'HTMLText',
    ];

    private static $display_fields = [
        'RawValue',
    ];

    private static $searchable_fields = [
        'RawValue',
    ];

    private static $summary_fields = [
        'Value',
        'RawValue',
        'FlickrID',
    ];

    private static $belongs_many_many = [
        'FlickrPhotos' => 'FlickrPhoto',
    ];

    private static $many_many = ['FlickrBuckets' => FlickrBucket::class];

    public function getCMSFields(): FieldList
    {
        $fields = new FieldList();
        $fields->push(new TextField('Value'));
        $fields->push(new TextField('RawValue'));

        return $fields;
    }


    /**
     * This is reqired for the GridFieldAutoCompleter to show tag names correctly in the CMS
     */
    public function Title(): string
    {
        return $this->RawValue;
    }
}
