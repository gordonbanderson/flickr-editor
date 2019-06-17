<?php
/**
 * Created by PhpStorm.
 * User: gordon
 * Date: 11/4/2561
 * Time: 16:22 น.
 */

namespace Suilven\SphinxSearch\Task;


use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\FreeTextSearch\Indexes;
use Suilven\SphinxSearch\Service\Indexer;

class ImportSetTask extends BuildTask
{

    protected $title = 'Import a Flickr set';

    protected $description = 'Import a flickr set';

    private static $segment = 'import-flickr-set';

    protected $enabled = true;


    public function run($request)
    {

    }


    public function importSet()
    {
        $page= 1;
        static $only_new_photos = false;

        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        // Code for the register action here
        $flickrSetID = $this->request->param('ID');
        $path = $_GET['path'];
        $parentNode = SiteTree::get_by_link($path);
        if ($parentNode == null) {
            echo "ERROR: Path ".$path." cannot be found in this site\n";
            die;
        }

        $this->FlickrSetId = $flickrSetID;

        $photos = $this->f->photosets_getPhotos(
            $flickrSetID,
            'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description',
            null,
            500
        );

        $photoset = $photos['photoset'];

        $flickrSet = $this->getFlickrSet($flickrSetID);

        // reload from DB with date - note the use of quotes as flickr set id is a string
        $flickrSet = DataObject::get_one('FlickrSet', 'FlickrID=\''.$flickrSetID."'");
        $flickrSet->FirstPictureTakenAt = $photoset['photo'][0]['datetaken'];
        $flickrSet->KeepClean = true;
        $flickrSet->Title = $photoset['title'];
        $flickrSet->write();

        echo "Title set to : ".$flickrSet->Title;

        if ($flickrSet->Title == null) {
            echo("ABORTING DUE TO NULL TITLE FOUND IN SET - ARE YOU AUTHORISED TO READ SET INFO?");
            die;
        }

        $datetime = explode(' ', $flickrSet->FirstPictureTakenAt);
        $datetime = $datetime[0];

        list($year, $month, $day) = explode('-', $datetime);
        echo "Month: $month; Day: $day; Year: $year<br />\n";

        // now try and find a flickr set page
        $flickrSetPage = DataObject::get_one('FlickrSetPage', 'FlickrSetForPageID='.$flickrSet->ID);
        if (!$flickrSetPage) {
            $flickrSetPage = new FlickrSetPage();
            $flickrSetPage->Title = $photoset['title'];
            $flickrSetPage->Description = $flickrSet->Description;

            //update FlickrSetPage set Description = (select Description from FlickrSet where FlickrSet.ID = FlickrSetPage.FlickrSetForPageID);

            $flickrSetPage->FlickrSetForPageID = $flickrSet->ID;
            $flickrSetPage->write();
            // create a stage version also
        }
        $flickrSetPage->Title = $photoset['title'];

        $flickrSetPage->ParentID = $parentNode->ID;
        $flickrSetPage->write();
        $flickrSetPage->publish("Live", "Stage");

        $flickrSetPageID = $flickrSetPage->ID;
        gc_enable();

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

        $numberOfPics = count($photoset['photo']);
        $ctr = 1;
        foreach ($photoset['photo'] as $key => $value) {
            echo "Importing photo {$ctr}/${numberOfPics}\n";

            $flickrPhoto = $this->createFromFlickrArray($value);

            if ($value['isprimary'] == 1) {
                $flickrSet->MainImage = $flickrPhoto;
            }




            $flickrPhoto->write();
            $flickrSet->FlickrPhotos()->add($flickrPhoto);
            gc_collect_cycles();

            $flickrPhoto->write();
            gc_collect_cycles();

            if (!$flickrPhoto->LocalCopyOfImage) {


                //mkdir appears to be relative to teh sapphire dir
                $structure = "../assets/flickr/$year/$month/$day/".$flickrSetID;

                if (!file_exists('../assets/flickr')) {
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
                    exec($cmd);

                    $cmd = "chown  gordon:www-data $structure/$fpid.jpg";
                    // $cmd = "pwd";
                    echo "EXECCED:".exec($cmd);

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
        $sql = 'update FlickrPhoto set Orientation = 90 where ThumbnailHeight > ThumbnailWidth;';
        DB::query($sql);


        // now download exifs
        $ctr = 0;
        foreach ($photoset['photo'] as $key => $value) {
            echo "IMPORTING EXIF {$ctr}/$numberOfPics\n";
            $flickrPhotoID = $value['id'];
            $flickrPhoto = FlickrPhoto::get()->filter('FlickrID', $flickrPhotoID)->first();
            $flickrPhoto->loadExif();
            $flickrPhoto->write();
            $ctr++;
        }

        $this->fixSetMainImages();
        $this->fixDateSetTaken();

        die(); // abort rendering
    }
}
