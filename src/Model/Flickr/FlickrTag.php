<?php
namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * Only show a page with login when not logged in
 *
 * @property string $Value
 * @property string $FlickrID
 * @property string $RawValue
 * @method \SilverStripe\ORM\ManyManyList|\Suilven\Flickr\Model\Flickr\FlickrBucket[] FlickrBuckets()
 * @method \SilverStripe\ORM\ManyManyList|\FlickrPhoto[] FlickrPhotos()
 */
class FlickrTag extends DataObject
{
    private static $table_name = 'FlickrTag';

    private static $db = array(
        'Value' => 'Varchar',
        'FlickrID' => 'Varchar',
        'RawValue' => 'HTMLText'
    );

    private static $display_fields = array(
        'RawValue'
    );


    private static $searchable_fields = array(
        'RawValue'
    );

    private static $summary_fields = array(
        'Value',
        'RawValue',
        'FlickrID'
    );

    private static $belongs_many_many = array(
        'FlickrPhotos' => 'FlickrPhoto'
    );

    private static $many_many = array('FlickrBuckets' => FlickrBucket::class);



    public function NormaliseCount($c)
    {
        return log(doubleval($c), 2);
    }


    public function getCMSFields()
    {
        $fields = new FieldList();
        $fields->push(new TextField('Value'));
        $fields->push(new TextField('RawValue'));
        return $fields;
    }


    // this is required so the grid field autocompleter returns readable entries after searching
    public function Title()
    {
        return $this->RawValue;
    }



}
