<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use League\CLImate\CLImate;
use Samwilson\PhpFlickr\PhotosetsApi;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DB;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Suilven\Flickr\Model\Site\FlickrSetPage;

class FlickrSetHelper extends FlickrHelper
{

    /**
     * Either get the set from the database, or if it does not exist get the details from flickr
     * and add it to the database
     *
     * @param string $flickrSetID the flickr set id
     * @return FlickrSet|null
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function getOrCreateFlickrSet(string $flickrSetID)
    {
        /** @var \Suilven\Flickr\Model\Flickr\FlickrSet|null  $flickrSet */
        $flickrSet = FlickrSet::get()->filter([
            'FlickrID' => $flickrSetID,
        ])->first();

        error_log('T1 - FS NULL=' . is_null($flickrSet));


        // if a set exists update data, otherwise create
        if (is_null($flickrSet)) {
            error_log('T2');
            $flickrSet = new FlickrSet();
            $setsHelper = $this->getPhotoSetsHelper();
            /** @var array<string,string> $setInfo */
            $setInfo = $setsHelper->getInfo($flickrSetID, null);

            $setTitle = $setInfo['title'];
            $setDescription = $setInfo['description'];
            $flickrSet->Title = $setTitle;
            $flickrSet->Description = $setDescription;
            $flickrSet->FlickrID = $flickrSetID;
            $flickrSet->write();
        }

        return $flickrSet;
    }


    /** @throws \SilverStripe\ORM\ValidationException */
    public function importSet(string $flickrSetID): void
    {
        $climate = new CLImate();

        $phpFlickr = $this->getPhpFlickr();

        $page= 1;
        // this will get updated after the first call to the API, set to ridic high value
        $pages = 1e7;
        static $only_new_photos = false;


        $controller = Controller::curr();
        $path = $controller->getRequest()->getVar('path');
        $parentNode = SiteTree::get_by_link($path);
        if (\is_null($parentNode)) {
            \user_error("ERROR: Path ".$path." cannot be found in this site");
        }

        $climate->info('Getting flickr set ' . $flickrSetID);

        $fshelper = new FlickrSetHelper();
        $flickrSet = $fshelper->getOrCreateFlickrSet($flickrSetID);

        error_log('FSID: ' . print_r($flickrSet, true));

        // see https://www.flickr.com/services/api/misc.urls.html for URL sizes
        $extras = 'license, date_upload, date_taken, owner_name, icon_server, original_format, ' .
            ' last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_t, url_s,' .
            ' url_q, url_m, url_n, url, url_z, url_c, url_h, url_k, url_l, url_o, description, url_sq';

        $perPage = Config::inst()->get(FlickrSetHelper::class, 'import_per_page');

        while ($page <= $pages) {
            $photosetsApi = new PhotosetsApi($phpFlickr);

            /** @var array<array> $photoset */
            $photoset = $photosetsApi->getPhotos(
                $flickrSetID,
                null,
                $extras,
                $perPage,
                $page
            );

            $page++;

            $climate->info(\print_r($photoset, true));
            $pages = $photoset['pages'];
            $climate->info('PAGES: ' . $pages);

            // @todo Deal with non existent id gracefully

            // @todo This makes the assumption that sets are ordered oldest first.  Refactor this
            $flickrSet->KeepClean = true;
            $flickrSet->Title = $photoset['title'];

            $firstPicTakenAt = $photoset['photo'][0]['datetaken'];
            error_log('First pic taken at ' . $firstPicTakenAt);
            $flickrSet->FirstPictureTakenAt = $firstPicTakenAt;
            $flickrSet->write();

            $climate->info("Title set to : ".$flickrSet->Title);

            // @todo This was a hack and may not be necessary now
            if ($flickrSet->Title === null) {
                echo "ABORTING DUE TO NULL TITLE FOUND IN SET - ARE YOU AUTHORISED TO READ SET INFO?";
                exit(1);
            }

            $datetime = \explode(' ', $flickrSet->FirstPictureTakenAt);
            $datetime = $datetime[0];

            list($year, $month, $day) = \explode('-', $datetime);
            echo "Month: $month; Day: $day; Year: $year<br />\n";

            // now try and find a flickr set page
            /** @var \Suilven\Flickr\Model\Site\FlickrSetPage $flickrSetPage */
            $flickrSetPage = FlickrSetPage::get()->filter(['FlickrSetForPageID' => $flickrSet->ID])->first();
            if (!isset($flickrSetPage)) {
                \error_log('>>>> Creating flickr set page <<<<');
                $flickrSetPage = new FlickrSetPage();
                $flickrSetPage->Title = $photoset['title'];
                $flickrSetPage->Description = $flickrSet->Description;

                $flickrSetPage->FlickrSetForPageID = $flickrSet->ID;
                $flickrSetPage->write();
                // create a stage version also
            }
            $flickrSetPage->Title = $photoset['title'];

            $flickrSetPage->ParentID = $parentNode->ID;
            $flickrSetPage->write();
            // @todo See what the SS4 behaviour is here
            //$flickrSetPage->copyVersionToStage("Live", "Stage");



            $numberOfPics = \count($photoset['photo']);
            $progress = $climate->progress()->total($numberOfPics);

            $ctr = 0;

            $photoHelper = new FlickrPhotoHelper();
            foreach ($photoset['photo'] as $value) {
                $ctr++;
                $progress->current($ctr);

                $flickrPhoto = $photoHelper->createFromFlickrArray($value);

                if (!$flickrPhoto) {
                    continue;
                }

                if ($value['isprimary'] === 1) {
                    $flickrSet->MainImage = $flickrPhoto;
                }

                $flickrPhoto->write();
                $flickrSet->FlickrPhotos()->add($flickrPhoto);
            }

            //update orientation
            $sql = 'update "FlickrPhoto" set "Orientation" = 90 where "ThumbnailHeight" > "ThumbnailWidth";';
            DB::query($sql);

            // now download exifs
            $ctr = 0;
            $exifHelper = new FlickrExifHelper();

            $climate->border();
            $climate->green('Importing EXIF');
            $climate->border();

            $progress = $climate->progress()->total(count($photoset['photo']));



            foreach ($photoset['photo'] as $value) {
                $progress->current($ctr);
                $flickrPhotoID = $value['id'];

                /** @var \Suilven\Flickr\Model\Flickr\FlickrPhoto $flickrPhoto */
                $flickrPhoto = FlickrPhoto::get()->filter('FlickrID', $flickrPhotoID)->first();


                if (!isset($flickrPhoto->Aperture)) {
                    $exifHelper->loadExif($flickrPhoto);
                    $flickrPhoto->write();

                }

                $ctr++;
            }
        }



        $miscHelper = new FlickrMiscHelper();
        // @todo this is borked
        //$miscHelper->fixSetMainImages();

        // $miscHelper->fixDateSetTaken();
    }
}
