<?php
namespace Suilven\Flickr\Helper;

use OAuth\Common\Storage\Memory;
use OAuth\OAuth1\Token\StdOAuth1Token;
use Samwilson\PhpFlickr\PhotosetsApi;
use Samwilson\PhpFlickr\PhpFlickr;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DB;
use Suilven\Flickr\Model\Flickr\FlickrExif;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;


class FlickrMiscHelper extends FlickrHelper
{
    public function fixSetMainImages()
    {
        $sets = FlickrSet::get()->filter(['PrimaryFlickrPhotoID' => 0]);
        $photosHelper = $this->getPhotoSetsHelper();
        foreach ($sets as $set) {
            $pageCtr = 1;
            $flickrSetID = $set->FlickrID;

            $mainImageFlickrID = null;
            $allPagesRead = false;

            while (!$allPagesRead) {
                $photos = $photosHelper->getPhotos(
                    $flickrSetID,
                    null,
                    'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description',
                    500,
                    $pageCtr
                );

                $pageCtr = $pageCtr+1;


                error_log('================================');
                error_log(print_r($photos, 1));

                //print_r($photos);
                $photoset = $photos['photo'];
                $page = $photos['page'];
                $pages = $photos['pages'];
                $allPagesRead = ($page == $pages);

                foreach ($photoset as $key => $photo) {
                    echo '.';
                    if ($photo['isprimary'] == 1) {
                        $fp = FlickrPhoto::get()->filter(['FlickrID' => $photo['id']])->first();

                        if (isset($fp)) {
                            $set->PrimaryFlickrPhotoID = $fp->ID;
                            $set->write();
                        } else {
                            $firstPicID = $set->FlickrPhotos()->first()->ID;
                            $set->PrimaryFlickrPhotoID = $firstPicID;
                            $set->write();
                        }
                    }
                }
            }
        }
    }


    public function fixDateSetTaken()
    {
        $fsps = DataList::create('FlickrSetPage')->where('FirstPictureTakenAt is NULL');
        foreach ($fsps as $fsp) {
            $fs = $fsp->FlickrSetForPage();

            if ($fs->ID == 0) {
                continue;
            }
            if ($fs->FirstPictureTakenAt == null) {
                $firstDate = $fs->FlickrPhotos()->sort('TakenAt')->where('TakenAt is not null');
                $firstDate = $firstDate->first();

                if ($firstDate) {
                    $fs->FirstPictureTakenAt = $firstDate->TakenAt;
                    $fs->KeepClean = true;
                    $fs->write();
                }
            }
            $fsp->FirstPictureTakenAt = $fs->FirstPictureTakenAt;
            $fsp->write();

            $fsp->publish("Live", "Stage");
        }
    }

}
