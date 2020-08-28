<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Suilven\Flickr\Model\Site\FlickrSetPage;

class FlickrMiscHelper extends FlickrHelper
{
    public function fixSetMainImages(): void
    {
        $sets = FlickrSet::get()->filter(['PrimaryFlickrPhotoID' => 0]);
        $photoSetsHelper = $this->getPhotoSetsHelper();

        /** @var \Suilven\Flickr\Model\Flickr\FlickrSet $set */
        foreach ($sets as $set) {
            $pageCtr = 1;
            $flickrSetID = $set->FlickrID;

            $allPagesRead = false;

            while (!$allPagesRead) {
               // \error_log('Page CTR: ' . $pageCtr);
               // \error_log('SET ID: ' . $flickrSetID);

                $photos = $photoSetsHelper->getPhotos(
                    $flickrSetID,
                    null,
                    null,
                    500,
                    $pageCtr
                );

                $pageCtr +=1;


              //  \error_log('================================');
              //  \error_log(\print_r($photos, true));

                //print_r($photos);
                $photoset = $photos['photo'];
                $page = $photos['page'];
                $pages = $photos['pages'];
                $allPagesRead = ($page === $pages);

                foreach ($photoset as $photo) {
                    if ($photo['isprimary'] !== 1) {
                        continue;
                    }

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


    public function fixDateSetTaken(): void
    {
        $fsps = FlickrSetPage::get()->where(['FirstPictureTakenAt'=> null]);
        foreach ($fsps as $fsp) {
            $fs = $fsp->FlickrSetForPage();

            if ($fs->ID === 0) {
                continue;
            }
            if ($fs->FirstPictureTakenAt === null) {
                $sortField = $fs->SortOrder;
                $firstDate = $fs->FlickrPhotos()->sort($sortField)->where($sortField . ' is not null');
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
