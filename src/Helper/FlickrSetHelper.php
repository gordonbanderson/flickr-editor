<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

use League\CLImate\CLImate;
use Samwilson\PhpFlickr\PhotosetsApi;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
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
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function getOrCreateFlickrSet(string $flickrSetID): \Suilven\Flickr\Model\Flickr\FlickrSet
    {
        // do we have a set object or not
        $flickrSet = FlickrSet::get()->filter([
            'FlickrID' => $flickrSetID,
        ])->first();


        // if a set exists update data, otherwise create
        if (!$flickrSet) {
            $flickrSet = new FlickrSet();
            $setsHelper = $this->getPhotoSetsHelper();
            $setInfo = $setsHelper->getInfo($flickrSetID, null);

            $setTitle = $setInfo['title'];
            $setDescription = $setInfo['description'];
            $flickrSet->Title = $setTitle;
            $flickrSet->Description = $setDescription;
            $flickrSet->FlickrID = $flickrSetID;
            $flickrSet->KeepClean = true;
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
        if ($parentNode === null) {
            \user_error("ERROR: Path ".$path." cannot be found in this site");
        }


        $climate->info('Getting flickr set ' . $flickrSetID);

        $fshelper = new FlickrSetHelper();
        $flickrSet = $fshelper->getOrCreateFlickrSet($flickrSetID);

        // see https://www.flickr.com/services/api/misc.urls.html for URL sizes
        $extras = 'license, date_upload, date_taken, owner_name, icon_server, original_format, ' .
            ' last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_t, url_s,' .
            ' url_q, url_m, url_n, url, url_z, url_c, url_h, url_k, url_l, url_o, description, url_sq';

        $perPage = Config::inst()->get(FlickrSetHelper::class, 'import_per_page');

        while ($page <= $pages) {
            $photosetsApi = new PhotosetsApi($phpFlickr);
            $photoset = $photosetsApi->getPhotos(
                $flickrSetID,
                null,
                $extras,
                $perPage,
                $page,
            );

            $page++;

            $climate->info(\print_r($photoset, 1));
            $pages = $photoset['pages'];
            $climate->info('PAGES: ' . $pages);

            // @todo Deal with non existent id gracefully
            // Reload from the database
            $flickrSet = FlickrSet::get()->filter(['FlickrID' => $flickrSetID])->first();

            // @todo This makes the assumption that sets are ordered oldest first.  Refactor this
            $flickrSet->FirstPictureTakenAt = $photoset['photo'][0]['datetaken'];
            $flickrSet->KeepClean = true;
            $flickrSet->Title = $photoset['title'];
            $flickrSet->write();

            $climate->info("Title set to : ".$flickrSet->Title);

            // @todo This was a hack and may not be necessary now
            if ($flickrSet->Title === null) {
                echo "ABORTING DUE TO NULL TITLE FOUND IN SET - ARE YOU AUTHORISED TO READ SET INFO?";
                null();
            }

            $datetime = \explode(' ', $flickrSet->FirstPictureTakenAt);
            $datetime = $datetime[0];

            list($year, $month, $day) = \explode('-', $datetime);
            echo "Month: $month; Day: $day; Year: $year<br />\n";

            // now try and find a flickr set page
            $flickrSetPage = FlickrSetPage::get()->filter(['FlickrSetForPageID' => $flickrSet->ID])->first();
            if (!$flickrSetPage) {
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
            $ctr = 1;

            $photoHelper = new FlickrPhotoHelper();
            foreach ($photoset['photo'] as $value) {
                echo "Importing photo {$ctr}/${numberOfPics}\n";

                $flickrPhoto = $photoHelper->createFromFlickrArray($value);

                if (!$flickrPhoto) {
                    $ctr++;

                    continue;
                }

                if ($value['isprimary'] === 1) {
                    $flickrSet->MainImage = $flickrPhoto;
                }

                $flickrPhoto->write();
                $flickrSet->FlickrPhotos()->add($flickrPhoto);


                if (!$flickrPhoto->LocalCopyOfImage) {
                    //mkdir appears to be relative to teh sapphire dir
                    $structure = "../assets/flickr/$year/$month/$day/".$flickrSetID;

                    if (!\file_exists('../assets/flickr')) {
                        echo "Creating path:".$structure;

                        /*
                        // To create the nested structure, the $recursive parameter
                        // to mkdir() must be specified.

                        if (!mkdir($structure, 0, true)) {
                         //   die('Failed to create folders...');
                        }

                        $cmd = "chown  gordon:www-data $structure";
                        exec($cmd);

                        $cmd = "chown gordon:www-data ../assets/Uploads/flickr";
                        exec($cmd);

                        exec('chmod 775 ../assets/Uploads/flickr');
                        exec("chmod 775 $structure");


                        error_log("Created dir?");
                    } else {
                        echo "Dir already exists";
                    }


                    */
                        $galleries = Folder::find_or_make('flickr');
                        $galleries->Title = 'Flickr Images';
                        $galleries->write();
                        $galleries = null;
                    }

                    $download_images = Config::inst()->get($this->class, 'download_images');

                    if ($download_images && !($flickrPhoto->LocalCopyOfImageID)) {
                        $largeURL = $flickrPhoto->LargeURL;
                        $fpid = $flickrPhoto->FlickrID;

                        $cmd = "wget -O $structure/$fpid.jpg $largeURL";
                        \exec($cmd);

                        $cmd = "chown  gordon:www-data $structure/$fpid.jpg";
                        // $cmd = "pwd";
                        echo "EXECCED:".\exec($cmd);

                        $image = new Image();
                        $image->Name = $this->Title;
                        $image->Title = $this->Title;
                        $image->Filename = \str_replace('../', '', $structure.'/'.$fpid.".jpg");
                        $image->Title = $flickrPhoto->Title;
                        //$image->Name = $flickrPhoto->Title;
                        $image->ParentID = $flickrSetAssetFolderID;
                        \gc_collect_cycles();

                        $image->write();
                        \gc_collect_cycles();

                        $flickrPhoto->LocalCopyOfImageID = $image->ID;
                        $flickrPhoto->write();
                        $image = null;
                    }

                    $flickrPhoto->write();
                }

                $ctr++;

                $flickrPhoto = null;
            }

            //update orientation
            $sql = 'update "FlickrPhoto" set "Orientation" = 90 where "ThumbnailHeight" > "ThumbnailWidth";';
            DB::query($sql);


            // now download exifs
            $ctr = 0;
            $exifHelper = new FlickrExifHelper();

            \error_log('++++ EXIF ++++');


            foreach ($photoset['photo'] as $value) {
                echo "IMPORTING EXIF {$ctr}/$numberOfPics\n";
                $flickrPhotoID = $value['id'];

                /** @var \Suilven\Flickr\Model\Flickr\FlickrPhoto $flickrPhoto */
                $flickrPhoto = FlickrPhoto::get()->filter('FlickrID', $flickrPhotoID)->first();


                if (!$flickrPhoto->Aperture) {
                    $exifHelper->loadExif($flickrPhoto);
                    $flickrPhoto->write();
                } else {
                    \error_log('ALREADY IMPORTED');
                }

                $ctr++;
            }
        }



        $miscHelper = new FlickrMiscHelper();
        $miscHelper->fixSetMainImages();
        // @todo this is borked
        // $miscHelper->fixDateSetTaken();
    }
}
