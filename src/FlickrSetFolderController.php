<?php
namespace Suilven\Flickr;

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataList;

class FlickrSetFolder_Controller extends \PageController
{
    public function FlickrSetsNewestFirst()
    {
        return DataList::create('FlickrSetPage')->where('ParentID = '.$this->ID)->sort('FirstPictureTakenAt desc');
    }

    public function FlickrSetFoldersNewestFirst()
    {
        return DataList::create('FlickrSetFolder')->where('ParentID = '.$this->ID)->sort('Created desc');
    }
}
