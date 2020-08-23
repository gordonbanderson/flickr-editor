<?php declare(strict_types = 1);

namespace Suilven\Flickr\ShortCode;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;
use Suilven\Flickr\Model\Flickr\FlickrSet;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

class FlickrPhotoSequenceShortCodeHandler
{

    /**
     * @param array<string,string> $arguments
     * @return \SilverStripe\ORM\FieldType\DBHTMLText|string
     */
    public static function parse_flickr(array $arguments, ?string $caption = null)
    {

        // first things first, if we dont have a image ids, then we don't need to
        // go any further
        if (!isset($arguments['setid']) || !isset($arguments['photoid']) ||
            !isset($arguments['nframes'])) {
            return '<div style="color: red">Please provide these parameters - setid, photoid,
                nframes</div>';
        }

        $customise = [];

        $images = new ArrayList();
        $setID = $arguments['setid'];
        $startPhotoID = $arguments['photoid'];

        $frames = $arguments['nframes'];

        /** @var \Suilven\Flickr\Model\Flickr\FlickrSet $set */
        $set = FlickrSet::get()->filter('FlickrID', $setID)->first();
        $startPhoto = $set->FlickrPhotos()->filter('FlickrID', $startPhotoID)->first();

        if (is_null($startPhoto)) {
            return '<!-- Flickr Photo with ID ' . $startPhotoID . ' not found -->';
        }

        $sortField = $set->SortOrder;


        // @todo Use ORM
        $ctr = 0;
        $adding = false;
       // $frames = 100;
        foreach ($set->FlickrPhotos()->sort($sortField) as $photo) {
            if ($ctr >= $frames) {
                break;
            }

            if ($photo->FlickrID === $startPhotoID) {
                $adding = true;
            }
            if (!$adding) {
                continue;
            }

            $images->add($photo);
            $ctr++;
        }


        $customise['FlickrSequence'] = $images;
        //set the caption


        if (($caption === null) || ($caption === '')) {
            if (isset($arguments['caption'])) {
                $caption = $arguments['caption'];
            }
        }

        $customise['Caption'] = !is_null($caption)
            ? Convert::raw2xml($caption)
            : $startPhoto->Title ;


        //overide the defaults with the arguments supplied
        $customise = \array_merge($customise, $arguments);

        //get our YouTube template
        $template = new SSViewer('Includes/ShortCodeFlickrPhotoSequence');

        //return the customised template
        return $template->process(new ArrayData($customise));
    }
}
