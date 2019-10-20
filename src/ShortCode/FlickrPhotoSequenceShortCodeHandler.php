<?php
namespace Suilven\Flickr\ShortCode;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\Core\Convert;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ArrayData;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

class FlickrPhotoSequenceShortCodeHandler
{

    // taken from http://www.ssbits.com/tutorials/2010/2-4-using-short-codes-to-embed-a-youtube-video/ and adapted for SS3
    public static function parse_flickr($arguments, $caption = null, $parser = null)
    {

        // first things first, if we dont have a image ids, then we don't need to
        // go any further
        if (empty($arguments['ids'])) {
            return;
        }

        $customise = array();

        $images = new ArrayList();
        $idsArray = explode(',', $arguments['ids']);

        foreach($idsArray as $id) {
            $fp = DataList::create(FlickrPhoto::class)->filter(['FlickrID' => $id])->first();
            error_log('FP: ' . $fp->ID);
            if (!$fp) {
                return '';
            }
            $images->add($fp);
        }

        $customise['FlickrSequence'] = $images;
        //set the caption


        if (($caption === null) || ($caption === '')) {
            if (isset($arguments['caption'])) {
                $caption = $arguments['caption'];
            }
        }


        $customise['Caption'] = $caption ? Convert::raw2xml($caption) : $fp->Title ;
        $customise['Position'] = !empty($arguments['position']) ? $arguments['position'] : 'center';
        $customise['Small'] = true;
        if ($customise['Position'] == 'center') {
            $customise['Small'] = false;
        }

        $fp = null;

        //overide the defaults with the arguments supplied
        $customise = array_merge($customise, $arguments);

        //get our YouTube template
        $template = new SSViewer('Includes/ShortCodeFlickrPhotoSequence');

        //return the customised template
        return $template->process(new ArrayData($customise));
    }
}
