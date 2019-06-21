<?php
namespace Suilven\Flickr\Helper;

use OAuth\Common\Storage\Memory;
use OAuth\OAuth1\Token\StdOAuth1Token;
use Samwilson\PhpFlickr\PhotosetsApi;
use Samwilson\PhpFlickr\PhpFlickr;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DB;
use Suilven\Flickr\Model\Flickr\FlickrExif;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;


class FlickrExifHelper extends FlickrHelper
{
    /**
     * @param FlickrPhoto $flickrPhoto
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function loadExif($flickrPhoto)
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

        DB::query('begin;');

        echo "Storing exif data for ".$flickrPhoto->Title."\n";
        foreach ($exifData['exif'] as $key => $exifInfo) {
            $exif = new FlickrExif();
            $exif->TagSpace = $exifInfo['tagspace'];
            $exif->TagSpaceID = $exifInfo['tagspaceid'];
            $exif->Tag = $exifInfo['tag'];
            $exif->Label = $exifInfo['label'];
            $exif->Raw = $exifInfo['raw'];
            $exif->FlickrPhotoID = $flickrPhoto->ID;
            $exif->write();

            echo "- {$exif->Tag} = {$exif->Raw}\n";

            if ($exif->Tag == 'FocalLength') {
                $raw = str_replace(' mm', '', $exif->Raw);
                $focallength = $raw; // model focal length
            } elseif ($exif->Tag == 'ImageUniqueID') {
                $flickrPhoto->ImageUniqueID = $exif->Raw;
            } elseif ($exif->Tag == 'ISO') {
                $flickrPhoto->ISO = $exif->Raw;
            } elseif ($exif->Tag == 'ExposureTime') {
                $flickrPhoto->ShutterSpeed = $exif->Raw;
            } elseif ($exif->Tag == 'FocalLengthIn35mmFormat') {
                $raw35 = $exif->Raw;
                $fl35 = str_replace(' mm', '', $raw35);
                $fl35 = (int) $fl35;
                $flickrPhoto->FocalLength35mm = $fl35;
            } elseif ($exif->Tag == 'FNumber') {
                $flickrPhoto->Aperture = $exif->Raw;
            }
            // @todo, make configurable
            // @todo Is this neccesary?
            // Hardwire phone focal length
            elseif ($exif->Tag == 'Model') {
                $name = $exif->Raw;
                if ($name === 'C6602') {
                    $flickrPhoto->FocalLength35mm = 28;
                    $fixFocalLength = 28;
                }

                if ($name === 'Canon IXUS 220 HS') {
                    $focalConversionFactor = 5.58139534884;
                }

                if ($name === 'Canon EOS 450D') {
                    $focalConversionFactor = 1.61428571429;
                }
            }


            $exif = null;
            gc_collect_cycles();
        }

        // try and fix the 35mm focal length
        if ((int)($flickrPhoto->FocalLength35mm) === 0) {
            if ($fixFocalLength) {
                $flickrPhoto->FocalLength35mm = 28; // this is hardwired for phone
            } elseif ($focalConversionFactor !== 1) {
                $f = $focalConversionFactor*$focallength;
                $flickrPhoto->FocalLength35mm = round($f);
            }
        }

        $flickrPhoto->write();

        echo "/storing exif";
        DB::query('commit;');
    }
}
