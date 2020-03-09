<?php
namespace Suilven\Flickr\ShortCode;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;

class FlickrPhotoSequenceShortCodeHandler
{

    public static function parse_flickr($arguments, $caption = null, $parser = null)
    {

        // first things first, if we dont have a image ids, then we don't need to
        // go any further
        if (empty($arguments['setid']) || empty($arguments['photoid']) || empty($arguments['nframes'])) {
            return '<div style="color: red">Please provide these parameters - setid, photoid, nframes</div>';
        }

        $customise = array();

        $images = new ArrayList();
        $setID =  $arguments['setid'];
        $startPhotoID = $arguments['photoid'];

        $frames = $arguments['nframes'];

        /** @var FlickrSet $set */
        $set = FlickrSet::get()->filter('FlickrID', $setID)->first(); // flickr set IDs are unique
        $startPhoto = $set->FlickrPhotos()->filter('FlickrID', $startPhotoID)->first();
        //         $startPhoto = FlickrPhoto::get()->filter('FlickrID', $startPhotoID)->first();
        $sortField = $set->SortOrder;


        // @todo Use ORM
        $ctr = 0;
        $adding = false;
       // $frames = 100;
        foreach ($set->FlickrPhotos()->sort($sortField) as $photo) {
            if ($ctr >= $frames) {
                break;
            }

            if ($photo->FlickrID == $startPhotoID ) {
                $adding = true;
            }
            if ($adding) {
                $images->add($photo);
                $ctr++;
            }
        }


        $customise['FlickrSequence'] = $images;
        //set the caption


        if (($caption === null) || ($caption === '')) {
            if (isset($arguments['caption'])) {
                $caption = $arguments['caption'];
            }
        }


        $customise['Caption'] = $caption ? Convert::raw2xml($caption) : $startPhoto->Title ;


        //overide the defaults with the arguments supplied
        $customise = array_merge($customise, $arguments);

        //get our YouTube template
        $template = new SSViewer('Includes/ShortCodeFlickrPhotoSequence');

        //return the customised template
        return $template->process(new ArrayData($customise));
    }
}
