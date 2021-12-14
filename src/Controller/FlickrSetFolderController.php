<?php declare(strict_types = 1);

namespace Suilven\Flickr\Controller;

use SilverStripe\ORM\DataList;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class \Suilven\Flickr\Controller\FlickrSetFolder_Controller
 */
class FlickrSetFolderController extends \PageController
{
    /** @return \SilverStripe\ORM\DataList<\Suilven\Flickr\Controller\FlickrSetPage> */
    public function FlickrSetsNewestFirst(): DataList
    {
        return DataList::create('FlickrSetPage')->where('ParentID = '.$this->ID)->
        sort('FirstPictureTakenAt desc');
    }


    /** @return \SilverStripe\ORM\DataList<\Suilven\Flickr\Controller\FlickrSetFolder> */
    public function FlickrSetFoldersNewestFirst(): DataList
    {
        return DataList::create('FlickrSetFolder')->where('ParentID = '.$this->ID)->
        sort('Created desc');
    }
}
