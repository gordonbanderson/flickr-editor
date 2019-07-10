<?php
namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
* Only show a page with login when not logged in
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


    /*
    Static helper
    */
    public static function CreateOrFindTags($csv)
    {
        $result = new ArrayList();

        if (trim($csv) == '') {
            return $result; // ie empty array
        }

        $tags = explode(',', $csv);
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (!$tagName) {
                continue;
            }
            $ftag = DataList::create('FlickrTag')->where("Value='".strtolower($tagName)."'")->first();
            if (!$ftag) {
                $ftag = FlickrTag::create();
                $ftag->RawValue = $tagName;
                $ftag->Value  = strtolower($tagName);
                $ftag->write();
            }

            $result->add($ftag);
        }

        return $result;
    }
}
