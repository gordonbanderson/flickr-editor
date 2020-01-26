<?php
namespace Suilven\Flickr\Helper;

use Samwilson\PhpFlickr\PhpFlickr;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use Suilven\Flickr\Model\Flickr\FlickrGallery;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Suilven\Flickr\Model\Site\FlickrGalleryPage;

class FlickrGalleryHelper extends FlickrHelper
{

    /**
     * Either get the set from the database, or if it does not exist get the details from flickr and add it to the database
     * @param string $flickrSetID the flickr set id
     * @return DataObject|FlickrSet|null
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function getOrCreateFlickrGallery($flickrSetID)
    {
        // do we have a set object or not
        $flickrGallery = FlickrGallery::get()->filter([
            'FlickrID' => $flickrSetID
        ])->first();


        // if a set exists update data, otherwise create
        if (!$flickrGallery) {

            $flickrGallery = new FlickrGallery();
            $phpFlickr = $this->getPhpFlickr();

            $galleryInfo = $phpFlickr->galleries_getInfo($flickrSetID)['gallery'];
            error_log(print_r($galleryInfo, 1));

            $setTitle = $galleryInfo['title'];
            $setDescription = $galleryInfo['description'];
            $flickrGallery->Title = $setTitle;
            $flickrGallery->Description = $setDescription;
            $flickrGallery->FlickrID = $flickrSetID;
            $flickrGallery->KeepClean = true;
            $flickrGallery->write();
        }

        return $flickrGallery;
    }


    /**
     * @param PhpFlickr $phpFlickr
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function importGallery($flickrSetID)
    {
        $phpFlickr = $this->getPhpFlickr();

        $page= 1;
        $pages = 1e7; // this will get updated after the first call to the API, set to ridic high value
        static $only_new_photos = false;


        $path = $_GET['path'];
        $parentNode = SiteTree::get_by_link($path);
        if ($parentNode == null) {
            user_error( "ERROR: Path ".$path." cannot be found in this site");
        }


        error_log('Getting flickr set ' . $flickrSetID);

        $fshelper = new FlickrGalleryHelper();
        //
        $flickrGallery = $fshelper->getOrCreateFlickrGallery($flickrSetID);
        //$flickrGallery = $fshelper->getOrCreateFlickrGallery('45192826-72157711959956183');

        // see https://www.flickr.com/services/api/misc.urls.html for URL sizes
        $extras = 'license, date_upload, date_taken, owner_name, icon_server, original_format, ' .
            ' last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_t, url_s,' .
            ' url_q, url_m, url_n, url, url_z, url_c, url_h, url_k, url_l, url_o, description, url_sq';

        $perPage = Config::inst()->get(FlickrSetHelper::class, 'import_per_page');

        while ($page <= $pages) {

            //    public function galleries_getPhotos($gallery_id, $extras = null, $per_page = null, $page = null)
            $photoset = $phpFlickr->galleries_getPhotos(
                $flickrSetID,
                $extras,
                $perPage,
                $page

            )['photos'];

            error_log(print_r($photoset, 1));

            $page++;

            error_log(print_r($photoset, 1));
            $pages = $photoset['pages'];
            error_log('PAGES: ' . $pages);

            // @todo Deal with non existent id gracefully
            // Reload from the database
            $flickrGallery = FlickrGallery::get()->filter(['FlickrID' => $flickrSetID])->first();

            // @todo This makes the assumption that sets are ordered oldest first.  Refactor this
            $flickrGallery->FirstPictureTakenAt = $photoset['photo'][0]['datetaken'];
            $flickrGallery->KeepClean = true;
            $flickrGallery->write();


            // @todo This was a hack and may not be necessary now
            if ($flickrGallery->Title == null) {
                error_log("ABORTING DUE TO NULL TITLE FOUND IN SET - ARE YOU AUTHORISED TO READ SET INFO?");
                die;
            }

            $datetime = explode(' ', $flickrGallery->FirstPictureTakenAt);
            $datetime = $datetime[0];

            list($year, $month, $day) = explode('-', $datetime);
            error_log( "Month: $month; Day: $day; Year: $year<br />\n");

            // now try and find a flickr set page
            $flickrGalleryPage = FlickrGalleryPage::get()->filter(['FlickrGalleryForPageID' => $flickrGallery->ID])->first();
            if (!$flickrGalleryPage) {
                error_log('>>>> Creating flickr set page <<<<');
                $flickrGalleryPage = new FlickrGalleryPage();
                $flickrGalleryPage->Title = $flickrGallery->Title;
                $flickrGalleryPage->Description = $flickrGallery->Description;


                $flickrGalleryPage->FlickrGalleryForPageID = $flickrGallery->ID;
                $flickrGalleryPage->write();
                // create a stage version also
            }
           // $flickrSetPage->Title = $photoset['title'];

            $flickrGalleryPage->ParentID = $parentNode->ID;
            $flickrGalleryPage->write();

            $flickrSetPageID = $flickrGalleryPage->ID;
            gc_enable();

            /*
            $f1 = Folder::find_or_make("flickr/$year");
            $f1->Title = $year;
            $f1->write();

            $f1 = Folder::find_or_make("flickr/$year/$month");
            $f1->Title = $month;
            $f1->write();

            $f1 = Folder::find_or_make("flickr/$year/$month/$day");
            $f1->Title = $day;
            $f1->write();

            exec("chmod 775 ../assets/flickr/$year");
            exec("chmod 775 ../assets/flickr/$year/$month");
            exec("chmod 775 ../assets/flickr/$year/$month/$day");
            exec("chown gordon:www-data ../assets/flickr/$year");
            ;
            exec("chown gordon:www-data ../assets/flickr/$year/$month");
            ;
            exec("chown gordon:www-data ../assets/flickr/$year/$month/$day");
            ;


            $folder = Folder::find_or_make("flickr/$year/$month/$day/" . $flickrSetID);

            $cmd = "chown gordon:www-data ../assets/flickr";
            exec($cmd);

            exec('chmod 775 ../assets/flickr');


            // new folder case
            if ($flickrSet->AssetFolderID == 0) {
                $flickrSet->AssetFolderID = $folder->ID;
                $folder->Title = $flickrSet->Title;
                $folder->write();

                $cmd = "chown gordon:www-data ../assets/flickr/$year/$month/$day/".$flickrSetID;
                exec($cmd);

                $cmd = "chmod 775 ../assets/flickr/$year/$month/$day/".$flickrSetID;
                exec($cmd);
            }

            $flickrSetAssetFolderID = $flickrSet->AssetFolderID;

            $flickrSetPageDatabaseID = $flickrSetPage->ID;


            //$flickrSet = NULL;
            $flickrSetPage = null;
            */

            $numberOfPics = count($photoset['photo']);
            $ctr = 1;

            $photoHelper = new FlickrPhotoHelper();
            foreach ($photoset['photo'] as $key => $value) {
                error_log( "Importing photo {$ctr}/${numberOfPics}\n");

                error_log(print_r($key,1));
                error_log(print_r($value,1));

                $flickrPhoto = $photoHelper->createFromFlickrArray($value);

                if (!$flickrPhoto) {
                    $ctr++;
                    continue;
                }

                if ($value['is_primary'] == 1) {
                    $flickrGallery->MainImage = $flickrPhoto;
                }

                $flickrPhoto->write();
                $flickrGallery->FlickrPhotos()->add($flickrPhoto);


                if (!$flickrPhoto->LocalCopyOfImage) {


                    //mkdir appears to be relative to teh sapphire dir
                    $structure = "../assets/flickr/$year/$month/$day/".$flickrSetID;

                    if (!file_exists('../assets/flickr')) {
                        error_log( "Creating path:".$structure);

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
                        error_log( "Dir already exists";
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
                        exec($cmd);

                        $cmd = "chown  gordon:www-data $structure/$fpid.jpg";
                        // $cmd = "pwd";
                        error_log( "EXECCED:".exec($cmd));

                        $image = new Image();
                        $image->Name = $this->Title;
                        $image->Title = $this->Title;
                        $image->Filename = str_replace('../', '', $structure.'/'.$fpid.".jpg");
                        $image->Title = $flickrPhoto->Title;
                        //$image->Name = $flickrPhoto->Title;
                        $image->ParentID = $flickrSetAssetFolderID;
                        gc_collect_cycles();

                        $image->write();
                        gc_collect_cycles();

                        $flickrPhoto->LocalCopyOfImageID = $image->ID;
                        $flickrPhoto->write();
                        $image = null;
                    }

                    $result = $flickrPhoto->write();
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

            error_log('++++ EXIF ++++');


            foreach ($photoset['photo'] as $key => $value) {
                error_log( "IMPORTING EXIF {$ctr}/$numberOfPics\n");
                $flickrPhotoID = $value['id'];

                /** @var FlickrPhoto $flickrPhoto */
                $flickrPhoto = FlickrPhoto::get()->filter('FlickrID', $flickrPhotoID)->first();


                if (!$flickrPhoto->Aperture) {
                    $exifHelper->loadExif($flickrPhoto);
                    $flickrPhoto->write();
                } else {
                    error_log('ALREADY IMPORTED');
                }

                $ctr++;
            }
        }

    }
}
