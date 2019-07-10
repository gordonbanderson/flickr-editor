<?php
namespace Suilven\Flickr\Helper;

use SilverStripe\ORM\DataList;
use Suilven\Flickr\Model\Flickr\FlickrTag;

class FlickrUpdateMetaHelper extends FlickrHelper
{
    public function writePhotoToFlickr($flickrPhoto, $descriptionSuffix)
    {
        $apiHelper = $this->getPhotosAPIHelper();
        $phpFlickr = $this->getPhpFlickr(); // needed for location

        $fullDesc = $flickrPhoto->Description."\n\n".$descriptionSuffix;
        $fullDesc = trim($fullDesc);

        $year = substr($flickrPhoto->TakenAt, 0, 4);
        $fullDesc = str_replace('$Year', $year, $fullDesc);
        $apiHelper->setMeta($flickrPhoto->FlickrID, $flickrPhoto->Title, $fullDesc);

        $tagString = '';
        foreach ($flickrPhoto->FlickrTags() as $tag) {
            $tagString .= '"'.$tag->Value.'" ';
        }

        $apiHelper->setTags($flickrPhoto->FlickrID, $tagString);

        if ($flickrPhoto->HasGeo()) {
            $phpFlickr->photos_geo_setLocation($flickrPhoto->FlickrID, $flickrPhoto->getMappableLatitude(), $flickrPhoto->getMappableLongitude());
        }

        $flickrPhoto->KeepClean = true;
        $flickrPhoto->write();
    }
}
