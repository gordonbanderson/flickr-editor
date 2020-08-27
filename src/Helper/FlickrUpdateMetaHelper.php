<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use Suilven\Flickr\Model\Flickr\FlickrPhoto;

class FlickrUpdateMetaHelper extends FlickrHelper
{
    /**
     * Update Flickr photos with the changes made in SilverStripe
     *
     * @param string $descriptionSuffix a suffix to be appended to each description, e.g. copyright
     * @throws \Samwilson\PhpFlickr\FlickrException
     */
    public function writePhotoToFlickr(FlickrPhoto $flickrPhoto, string $descriptionSuffix): void
    {
        $apiHelper = $this->getPhotosHelper();
        // needed for location
        $phpFlickr = $this->getPhpFlickr();

        $fullDesc = $flickrPhoto->Description."\n\n".$descriptionSuffix;
        $fullDesc = \trim($fullDesc);

        $year = \substr($flickrPhoto->TakenAt, 0, 4);
        $fullDesc = \str_replace('$Year', $year, $fullDesc);

        /** @var int $flickrIDAsInt */
        $flickrIDAsInt = intval($flickrPhoto->FlickrID);
        $apiHelper->setMeta($flickrIDAsInt, $flickrPhoto->Title, $fullDesc);

        $tagString = '';
        foreach ($flickrPhoto->FlickrTags() as $tag) {
            $tagString .= '"'.$tag->Value.'" ';
        }

        $apiHelper->setTags($flickrIDAsInt, $tagString);

        if ($flickrPhoto->HasGeo()) {
            // @TODO Use FLickrPhotosGeoHelper instead
            // @TODO smindel/gis coordinates
            /*
            $phpFlickr->photos_geo_setLocation(
                $flickrPhoto->FlickrID,
                $flickrPhoto->getMappableLatitude(),
                $flickrPhoto->getMappableLongitude()
            );
            */
        }

        $flickrPhoto->write();
    }
}
