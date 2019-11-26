<?php
namespace Suilven\Flickr\Extension;

use SilverStripe\Core\Extension;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

class FlickrImageURLExtension extends Extension
{
    public function getFlickrURLFromID($flickrID, $size='l')
    {
        /** @var FlickrPhoto $flickrImage */
        $flickrImage = FlickrPhoto::get()->filter(['FlickrID' => $flickrID])->first();

        // @todo configurable size
        switch($size) {
            case 's':
                return $flickrImage->SmallURL;
            case 'sq':
                return $flickrImage->SquareURL;
            case 'sq150':
                return $flickrImage->SmallURL150;
            case 'm':
                return $flickrImage->MediumURL;
            case 'm640':
                return $flickrImage->MediumURL640;
            case 'm800':
                return $flickrImage->MediumURL800;
            case 'l':
                return $flickrImage->LargeURL;
            case 'l1600':
                return $flickrImage->LargeURL1600;
            case 'l2048':
                return $flickrImage->LargeURL2048;
            case 'o':
                return $flickrImage->OriginalURL;
        }

        // return something if size cannot be determined
        return $flickrImage->LargeURL;
    }
}
