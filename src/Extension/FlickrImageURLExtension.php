<?php
namespace Suilven\Flickr\Extension;

use SilverStripe\Core\Extension;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

class FlickrImageURLExtension extends Extension
{
    public function getFlickrURLFromID($flickrID)
    {
        /** @var FlickrPhoto $flickrImage */
        $flickrImage = FlickrPhoto::get()->filter(['FlickrID' => $flickrID])->first();

        // @todo configurable size
        return $flickrImage->LargeURL;
    }
}
