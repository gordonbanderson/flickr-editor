<?php
namespace Suilven\Flickr\Helper;

use OAuth\Common\Storage\Memory;
use OAuth\OAuth1\Token\StdOAuth1Token;
use Samwilson\PhpFlickr\PhotosetsApi;
use Samwilson\PhpFlickr\PhpFlickr;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrAuthor;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrTag;


class FlickrPhotoHelper extends FlickrHelper
{
    public function createFromFlickrArray($value, $only_new_photos = false)
    {
        gc_collect_cycles();

        $flickrPhotoID = $value['id'];

        // the author, e.g. gordonbanderson
        $pathalias = $value['pathalias'];

        // do we have a set object or not
        $flickrPhoto = FlickrPhoto::get()->filter(['FlickrID' => $flickrPhotoID])->first();

        // if a set exists update data, otherwise create
        if (!$flickrPhoto) {
            $flickrPhoto = new FlickrPhoto();
        }

        // if we are in the mode of only importing new then skip to the next iteration if this pic already exists
        elseif ($only_new_photos) {
            // @todo Fix, this fails continue;
        }

        $flickrPhoto->Title = $value['title'];

        $flickrPhoto->FlickrID = $flickrPhotoID;
        $flickrPhoto->KeepClean = true;

        //240 on longest side
        $flickrPhoto->MediumURL = $value['url_s'];
        $flickrPhoto->MediumHeight = $value['height_s'];
        $flickrPhoto->MediumWidth = $value['width_s'];

        // 320 on longest side
        // Checked - GBA
        $flickrPhoto->MediumURL320 = $value['url_n'];
        $flickrPhoto->MediumHeight320 = $value['height_n'];
        $flickrPhoto->MediumWidth320 = $value['width_n'];

        // 500 on longest side
        // Checked - GBA
        $flickrPhoto->MediumURL500 = $value['url_m'];
        $flickrPhoto->MediumHeight500 = $value['height_m'];
        $flickrPhoto->MediumWidth500 = $value['width_m'];

        // 640 on longest side
        // Checked - GBA
        $flickrPhoto->MediumURL640 = $value['url_z'];
        $flickrPhoto->MediumHeight640 = $value['height_z'];
        $flickrPhoto->MediumWidth640 = $value['width_z'];

        // 800 on longest side
        // Checked - GBA
        $flickrPhoto->MediumURL800 = $value['url_c'];
        $flickrPhoto->MediumHeight800 = $value['height_c'];
        $flickrPhoto->MediumWidth800 = $value['width_c'];

        // Checked - GBA
        $flickrPhoto->SquareURL = $value['url_sq'];
        $flickrPhoto->SquareHeight = $value['height_sq'];
        $flickrPhoto->SquareWidth = $value['width_sq'];

        // Checked - GBA
        $flickrPhoto->SquareURL150 = $value['url_q'];
        $flickrPhoto->SquareHeight150 = $value['height_q'];
        $flickrPhoto->SquareWidth150 = $value['width_q'];


        // Checked - GBA
        $flickrPhoto->ThumbnailURL = $value['url_t'];
        $flickrPhoto->ThumbnailHeight = $value['height_t'];
        $flickrPhoto->ThumbnailWidth = $value['width_t'];

        // Checked - GBA
        $flickrPhoto->SmallURL = $value['url_s'];
        $flickrPhoto->SmallHeight = $value['height_s'];
        $flickrPhoto->SmallWidth = $value['width_s'];

        // If the image is too small, large size will not be set

        // Checked - GBA
        if (isset($value['url_l'])) {
            $flickrPhoto->LargeURL = $value['url_l'];
            $flickrPhoto->LargeHeight = $value['height_l'];
            $flickrPhoto->LargeWidth = $value['width_l'];
        }

        // checked - GBA
        if (isset($value['url_z'])) {
            $flickrPhoto->LargeURL1600 = $value['url_h'];
            $flickrPhoto->LargeHeight1600 = $value['height_h'];
            $flickrPhoto->LargeWidth1600 = $value['width_h'];
        }

        // checked - GBA
        if (isset($value['url_z'])) {
            $flickrPhoto->LargeURL2048 = $value['url_z'];
            $flickrPhoto->LargeHeight2048 = $value['height_z'];
            $flickrPhoto->LargeWidth2048 = $value['width_z'];
        }


        // checked - GBA
        $flickrPhoto->OriginalURL = $value['url_o'];
        $flickrPhoto->OriginalHeight = $value['height_o'];
        $flickrPhoto->OriginalWidth = $value['width_o'];

        $flickrPhoto->Description = 'test';// $value['description']['_content'];

        $author = FlickrAuthor::get()->filter('PathAlias', $pathalias)->first();
        if (!$author) {
            $author = new FlickrAuthor();
            $author->PathAlias = $pathalias;
            $author->write();
        }

        $flickrPhoto->PhotographerID = $author->ID;

        $lat = number_format($value['latitude'], 15);
        $lon = number_format($value['longitude'], 15);


        if ($value['latitude']) {
            $flickrPhoto->Lat = $lat;
            $flickrPhoto->ZoomLevel = 15;
        }
        if ($value['longitude']) {
            $flickrPhoto->Lon = $lon;
        }

        if ($value['accuracy']) {
            $flickrPhoto->Accuracy = $value['accuracy'];
        }

        if (isset($value['geo_is_public'])) {
            $flickrPhoto->GeoIsPublic = $value['geo_is_public'];
        }

        if (isset($value['woeid'])) {
            $flickrPhoto->WoeID = $value['woeid'];
        }

        $photosHelper = $this->getPhotosHelper();
        $singlePhotoInfo = $photosHelper->getInfo(($flickrPhotoID));


        $flickrPhoto->Description = $singlePhotoInfo['description'];
        error_log(print_r($singlePhotoInfo, 1));

        $flickrPhoto->TakenAt = $singlePhotoInfo['dates']['taken'];
        $flickrPhoto->Rotation = $singlePhotoInfo['rotation'];

        if (isset($singlePhotoInfo['visibility'])) {
            $flickrPhoto->IsPublic = $singlePhotoInfo['visibility']['ispublic'];
        }

        $flickrPhoto->write();

        error_log(
            'Written photo object'
        );



        foreach ($singlePhotoInfo['tags']['tag'] as $key => $taginfo) {
            error_log('TAG');

            $tag = FlickrTag::get()->filter(['Value' => $taginfo['_content']])->first();
            if (!$tag) {
                $tag = new FlickrTag();
            }

            $tag->FlickrID = $taginfo['id'];
            $tag->Value = $taginfo['_content'];
            $tag->RawValue = $taginfo['raw'];
            $tag->write();

            $ftags= $flickrPhoto->FlickrTags();
            $ftags->add($tag);

            $flickrPhoto->write();

            $tag = null;
            $ftags = null;

            gc_collect_cycles();
        }

        return $flickrPhoto;
    }
}
