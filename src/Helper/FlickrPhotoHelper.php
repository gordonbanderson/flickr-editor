<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use Suilven\Flickr\Model\Flickr\FlickrAuthor;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrTag;

class FlickrPhotoHelper extends FlickrHelper
{

    public function createFromFlickrArray($photoInfo, $only_new_photos = false)
    {
        $flickrPhotoID = $photoInfo['id'];

        // the author, e.g. gordonbanderson
        $pathalias = $photoInfo['pathalias'];

        // do we have a set object or not
        $flickrPhoto = FlickrPhoto::get()->filter(['FlickrID' => $flickrPhotoID])->first();

        // if a set exists update data, otherwise create
        if (!$flickrPhoto) {
            $flickrPhoto = new FlickrPhoto();
        }

        if ($flickrPhoto->Imported) {
            \error_log('Skipping import, already done');

            return false;
        }

        // if we are in the mode of only importing new then skip to the next iteration if this pic already exists


        if ($only_new_photos) {
            // @todo Fix, this fails continue;
        }

        // ordering images when the timestamps are identical is an issue.  If using a script to upload
        // images in order, this field can be used for ordering purposes
        $flickrPhoto->UploadUnixTimeStamp = $photoInfo['dateupload'];

        $flickrPhoto->Title = $photoInfo['title'];

        $flickrPhoto->FlickrID = $flickrPhotoID;
        $flickrPhoto->KeepClean = true;

        //240 on longest side
        $flickrPhoto->SmallURL = $photoInfo['url_s'];
        $flickrPhoto->SmallHeight = $photoInfo['height_s'];
        $flickrPhoto->SmallWidth = $photoInfo['width_s'];

        // 320 on longest sidehttps://live.staticflickr.com/65535/48426658216_7435981a4a_m.jpg
        // Checked - GBA
        $flickrPhoto->SmallURL320 = $photoInfo['url_n'];
        $flickrPhoto->SmallHeight320 = $photoInfo['height_n'];
        $flickrPhoto->SmallWidth320 = $photoInfo['width_n'];

        // 500 on longest side
        // Checked - GBA
        $flickrPhoto->MediumURL = $photoInfo['url_m'];
        $flickrPhoto->MediumHeight = $photoInfo['height_m'];
        $flickrPhoto->MediumWidth = $photoInfo['width_m'];

        // 640 on longest side
        // Checked - GBA
        $flickrPhoto->MediumURL640 = $photoInfo['url_z'];
        $flickrPhoto->MediumHeight640 = $photoInfo['height_z'];
        $flickrPhoto->MediumWidth640 = $photoInfo['width_z'];

        // 800 on longest side
        // Checked - GBA
        $flickrPhoto->MediumURL800 = $photoInfo['url_c'];
        $flickrPhoto->MediumHeight800 = $photoInfo['height_c'];
        $flickrPhoto->MediumWidth800 = $photoInfo['width_c'];

        // Checked - GBA
        $flickrPhoto->SquareURL = $photoInfo['url_sq'];
        $flickrPhoto->SquareHeight = $photoInfo['height_sq'];
        $flickrPhoto->SquareWidth = $photoInfo['width_sq'];

        // Checked - GBA
        $flickrPhoto->SquareURL150 = $photoInfo['url_q'];
        $flickrPhoto->SquareHeight150 = $photoInfo['height_q'];
        $flickrPhoto->SquareWidth150 = $photoInfo['width_q'];


        // Checked - GBA
        $flickrPhoto->ThumbnailURL = $photoInfo['url_t'];
        $flickrPhoto->ThumbnailHeight = $photoInfo['height_t'];
        $flickrPhoto->ThumbnailWidth = $photoInfo['width_t'];

        // Checked - GBA
        $flickrPhoto->SmallURL = $photoInfo['url_s'];
        $flickrPhoto->SmallHeight = $photoInfo['height_s'];
        $flickrPhoto->SmallWidth = $photoInfo['width_s'];

        // If the image is too small, large size will not be set

        // Checked - GBA
        if (isset($photoInfo['url_l'])) {
            $flickrPhoto->LargeURL = $photoInfo['url_l'];
            $flickrPhoto->LargeHeight = $photoInfo['height_l'];
            $flickrPhoto->LargeWidth = $photoInfo['width_l'];
        }

        // checked - GBA
        if (isset($photoInfo['url_h'])) {
            $flickrPhoto->LargeURL1600 = $photoInfo['url_h'];
            $flickrPhoto->LargeHeight1600 = $photoInfo['height_h'];
            $flickrPhoto->LargeWidth1600 = $photoInfo['width_h'];
        }

        // checked - GBA
        if (isset($photoInfo['url_k'])) {
            $flickrPhoto->LargeURL2048 = $photoInfo['url_k'];
            $flickrPhoto->LargeHeight2048 = $photoInfo['height_k'];
            $flickrPhoto->LargeWidth2048 = $photoInfo['width_k'];
        }


        // checked - GBA
        $flickrPhoto->OriginalURL = $photoInfo['url_o'];
        $flickrPhoto->OriginalHeight = $photoInfo['height_o'];
        $flickrPhoto->OriginalWidth = $photoInfo['width_o'];

        // $value['description']['_content'];
        $flickrPhoto->Description = 'test';

        $author = FlickrAuthor::get()->filter('PathAlias', $pathalias)->first();
        if (!$author) {
            $author = new FlickrAuthor();
            $author->PathAlias = $pathalias;
            $author->write();
        }

        $flickrPhoto->PhotographerID = $author->ID;

        $lat = \number_format($photoInfo['latitude'], 15);
        $lon = \number_format($photoInfo['longitude'], 15);


        if ($photoInfo['latitude']) {
            $flickrPhoto->Lat = $lat;
            $flickrPhoto->ZoomLevel = 15;
        }
        if ($photoInfo['longitude']) {
            $flickrPhoto->Lon = $lon;
        }

        if ($photoInfo['accuracy']) {
            $flickrPhoto->Accuracy = $photoInfo['accuracy'];
        }

        if (isset($photoInfo['geo_is_public'])) {
            $flickrPhoto->GeoIsPublic = $photoInfo['geo_is_public'];
        }

        if (isset($photoInfo['woeid'])) {
            $flickrPhoto->WoeID = $photoInfo['woeid'];
        }

        $photosHelper = $this->getPhotosHelper();
        $singlePhotoInfo = $photosHelper->getInfo(($flickrPhotoID));


        $flickrPhoto->Description = $singlePhotoInfo['description'];

        $flickrPhoto->TakenAt = $singlePhotoInfo['dates']['taken'];
        $flickrPhoto->Rotation = $singlePhotoInfo['rotation'];

        if (isset($singlePhotoInfo['visibility'])) {
            $flickrPhoto->IsPublic = $singlePhotoInfo['visibility']['ispublic'];
        }

        $flickrPhoto->Imported = true;

        $flickrPhoto->write();


        \error_log(
            'Written photo object',
        );



        foreach ($singlePhotoInfo['tags']['tag'] as $key => $taginfo) {
            \error_log('TAG');

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
        }

        return $flickrPhoto;
    }
}
