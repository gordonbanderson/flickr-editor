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
use Suilven\Flickr\Model\Site\FlickrSetPage;


class FlickrMiscHelper extends FlickrHelper
{
    public function fixSetMainImages()
    {
        $sets = FlickrSet::get()->filter(['PrimaryFlickrPhotoID' => 0]);
        $photosHelper = $this->getPhotoSetsHelper();

        /**
         * @var FlickrSet $set
         */
        foreach ($sets as $set) {
            $pageCtr = 1;
            $flickrSetID = $set->FlickrID;

            $mainImageFlickrID = null;
            $allPagesRead = false;

            while (!$allPagesRead) {
                error_log('Page CTR: ' . $pageCtr);
                error_log('SET ID: ' . $flickrSetID);

                $photos = $photosHelper->getPhotos(
                    $flickrSetID,
                    null,
                    null,
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
                        }

                        // @todo What if there is no primary id set?
                    }
                }
            }
        }
    }


    public function fixDateSetTaken()
    {
        $fsps = FlickrSetPage::get()->where(['FirstPictureTakenAt'=> null]);
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