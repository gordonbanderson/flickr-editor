<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use SilverStripe\Core\Extensible;
use SilverStripe\ORM\DB;
use Suilven\Flickr\Model\Flickr\FlickrExif;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

class FlickrExifHelper extends FlickrHelper
{

    use Extensible;

    /** @throws \SilverStripe\ORM\ValidationException */
    public function loadExif(FlickrPhoto $flickrPhoto): void
    {
        $phpFlickr = $this->getPhpFlickr();
        $exifData = $phpFlickr->photos_getExif($flickrPhoto->FlickrID);


        // delete any old exif data
        $sql = 'DELETE from "FlickrExif" where "FlickrPhotoID"='.$flickrPhoto->ID;
        DB::query($sql);

        // conversion factor or fixed legnth depending on model of camera
        $focallength = -1;
        $fixFocalLength = 0;
        $focalConversionFactor = 1;


        echo "Using exif data for ".$flickrPhoto->Title."\n";
        $exifs = [];
        foreach ($exifData['exif'] as $key => $exifInfo) {
            $exif = new FlickrExif();
            $exif->TagSpace = $exifInfo['tagspace'];
            $exif->TagSpaceID = $exifInfo['tagspaceid'];
            $exif->Tag = $exifInfo['tag'];
            $exif->Label = $exifInfo['label'];
            $exif->Raw = $exifInfo['raw'];
            $exif->FlickrPhotoID = $flickrPhoto->ID;

            $exifs[$exif->Tag] = $exif;
            //$exif->write();


            switch ($exif->Tag) {
                case 'FocalLength':
                    $raw = \str_replace(' mm', '', $exif->Raw);
                    // model focal length
                    $focallength = $raw;

                    break;
                case 'ImageUniqueID':
                    $flickrPhoto->ImageUniqueID = $exif->Raw;

                    break;
                case 'ISO':
                    $flickrPhoto->ISO = $exif->Raw;

                    break;
                case 'ExposureTime':
                    $flickrPhoto->ShutterSpeed = $exif->Raw;

                    break;
                case 'FocalLengthIn35mmFormat':
                    $raw35 = $exif->Raw;
                    $fl35 = \str_replace(' mm', '', $raw35);
                    $fl35 = (int) $fl35;
                    $flickrPhoto->FocalLength35mm = $fl35;

                    break;
                case 'Aperture':
                    $flickrPhoto->Aperture = $exif->Raw;

                    break;

                    // Flickr appeared to have changed the tag to FNumber
                case 'FNumber':
                    $flickrPhoto->Aperture = $exif->Raw;

                    break;
                default:
                    // do nothing
                    break;
            };
        }

        $this->extend('augmentPhotographWithExif', $flickrPhoto, $exifs);


        $flickrPhoto->write();

        echo "/storing exif";
    }
}
