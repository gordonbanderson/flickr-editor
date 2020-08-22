<?php declare(strict_types = 1);

namespace Suilven\Flickr\Controller;

use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrBatchHelper;
use Suilven\Flickr\Helper\FlickrBucketHelper;
use Suilven\Flickr\Model\Flickr\FlickrSet;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class \Suilven\Flickr\Controller\FlickrController
 */
class FlickrController extends \PageController implements PermissionProvider
{
    private static $allowed_actions = [
        'index',
        'importSet',
        'editprofile',
        'sets',
        'primeBucketsTest',
        'createBucket',
        'fixSetPhotoManyMany',
        'fixSetMainImages',
        'PublishAllFlickrSetPages',
        'batchUpdateSet',
        'ajaxSearchForPhoto',
        'fixArticles',
        'fixDateSetTaken',
        'fixArticleDates',
        'fixPhotoTitles',
        'ajaxSearchForPhoto',
        'updateEditedImagesToFlickr',
        'dumpSetAsJson',
        'primeFlickrSetFolderImages',
        'moveXperiaPics',
        'changeFlickrSetMainImage',
        'fixFocalLength35',
        'fixDescriptions',
        'importFromSearch',
        'importSearchToYML',
    ];


    /** @TODO is this needed? */
    public function fixDescriptions(): \SilverStripe\Control\HTTPResponse
    {
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $sets = FlickrSet::get()->Filter('Description', 'Array');
        foreach ($sets->getIterator() as $set) {
            echo $set->Title . "\n";
            $setInfo = $this->f->photosets_getInfo($set->FlickrID);

            $setTitle = $setInfo['title']['_content'];
            $setDescription = $setInfo['description']['_content'];
            $set->Title = $setTitle;
            $set->Description = $setDescription;
            $set->write();

            $fsps = FlickrSetPage::get()->filter('FlickrSetForPageID', $set->ID);
            foreach ($fsps->getIterator() as $fsp) {
                echo $fsp;
                $fsp->Title = $setTitle;
                $fsp->Description = $setDescription;
                $fsp->write();
                $fsp->publish("Live", "Stage");
            }
        }
    }


    /** @TODO obsolete? */
    public function fixFocalLength35(): \SilverStripe\Control\HTTPResponse
    {
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $finished = false;

        while (!$finished) {
            $photos = FlickrPhoto::get()->filter(['FocalLength35mm' => 0, 'IsPublic' => 1])->limit(50);

            if (\count($photos) === 0) {
                $finished = true;
            }

            foreach ($photos as $photo) {
                echo $photo->Title . "(" . $photo->FlickrID . ")\n";
                $modelExif = $photo->Exifs()->filter('Tag', 'Model')->first();

                //foreach ($photo->Exifs() as $exif) {
                //  echo ' - '.$exif->Tag.' => '.$exif->Raw."\n";
                //}

                if (isset($modelExif)) {
                    $model = $modelExif->Raw;
                    $focalLengthExif = $photo->Exifs()->filter('Tag', 'FocalLength')->first();
                    $mm = (int)\str_replace(' mm', '', $focalLengthExif->Raw);

                    if ($model === 'Canon IXUS 220 HS') {
                        $f35 = \round($mm * 5.58139534884);
                        $photo->FocalLength35mm = $f35;
                    } elseif ($model === 'C6602') {
                        $photo->FocalLength35mm = 28;
                    } elseif ($model === 'Canon EOS 450D') {
                        $f35 = \round($mm * 1.61428571429);
                        $photo->FocalLength35mm = $f35;
                    }

                    echo " - Focal length set to " . $photo->FocalLength35mm . "\n";
                    $photo->write();
                } else {
                    echo " - No exif data for image " . $photo->FlickrID . "\n";

                    // mark private for now
                    $photo->IsPublic = false;
                    $photo->write();
                }
            }
        }

        // abort rendering
        die;
    }


    /** @TODO obsolete? */
    public function fixArticleDates(): void
    {
        $articles = DataList::create('Article')->where('StartTime is null');
        foreach ($articles->getIterator() as $article) {
            $article->StartTime = $article->Created;
            $article->write();
        }
    }


    /** @return array<string,string> */
    public function providePermissions(): array
    {
        return [
            "FLICKR_EDIT" => "Able to import and edit flickr data",
        ];
    }


    public function primeFlickrSetFolderImages(): void
    {
        $folders = DataList::create('FlickrSetFolder')
            ->where('MainFlickrPhotoID = 0');

        foreach ($folders as $folder) {
            foreach ($folder->Children() as $folderOrSet) {
                $cname = $folderOrSet->ClassName;
                // we want to find a flickr set page we can use the image from
                if ($cname !== 'FlickrSetPage') {
                    continue;
                }

                $flickrImage = $folderOrSet->getPortletImage();
                //error_log("FI:".$flickrImage>" ID=".$flickrImage->ID);
                if ($flickrImage->ID !== 0) {
                    $folder->MainFlickrPhotoID = $flickrImage->ID;
                    $folder->write();

                    continue;
                }
            }
        }
    }


    public function updateEditedImagesToFlickr(): void
    {
        $flickrSetID = $this->request->param('ID');
        $flickrSet = FlickrSet::get()->filter(['FlickrID' => $flickrSetID])->first();
        $flickrSet = DataObject::get_by_id(FlickrSet::class, $flickrSetID);

        if ($flickrSet) {
            $flickrSet->writeToFlickr();
        } else {
            \error_log('Flickr set could not be found');
        }
    }


    /** @return false|string */
    public function ajaxSearchForPhoto()
    {
        //FIXME authentication

        $flickrPhotoID = Convert::raw2sql($this->request->param('ID'));

        $flickrPhoto = DataList::create('FlickrPhoto')->where('FlickrID=' .
            $flickrPhotoID)->first();
        $not_found = !$flickrPhoto;

        $result = [
            'found' => !$not_found,
        ];

        if ($flickrPhoto) {
            $result['title'] = $flickrPhoto->Title;
            $result['small_url'] = $flickrPhoto->SmallURL;
            $result['medium_url'] = $flickrPhoto->MediumURL;
            $result['id'] = $flickrPhoto->ID;
            $result['description'] = $flickrPhoto->Description;
        }

        return \json_encode($result);
    }


    /**
     * @return false|string
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function batchUpdateSet()
    {
        //FIXME authentication

        $flickrSetID = Convert::raw2sql($this->request->param('ID'));
        $batchTitle = Convert::raw2sql($_POST['BatchTitle']);
        $batchDescription = Convert::raw2sql($_POST['BatchDescription']);
        $batchTags = \str_getcsv(Convert::raw2sql($_POST['BatchTags']));

        $flickrSet = DataObject::get_by_id(FlickrSet::class, $flickrSetID);
        $helper = new FlickrBatchHelper();
        $result = $helper->batchUpdateSet($flickrSet, $batchTitle, $batchDescription, $batchTags);

        return \json_encode($result);
    }


    public function PublishAllFlickrSetPages(): void
    {
        $pages = DataList::create('FlickrSetPage');
        foreach ($pages as $fsp) {
            $fsp->publish("Stage", "Live");
        }

        $pages = DataList::create('FlickrSetFolder');
        foreach ($pages as $fsp) {
            $fsp->publish("Stage", "Live");
        }
    }


    public function primeBucketsTest(): void
    {
        $fset = DataList::create('FlickrSet')->last();
        $bucket = new FlickrBucket();
        // get an ID
        $bucket->write();
        $photos = $fset->FlickrPhotos();

        $bucketPhotos = $bucket->FlickrPhotos();
        $ctr = 0;
        foreach ($photos as $value) {
            $bucketPhotos->add($value);
            $ctr += 1;
            if ($ctr > 7) {
                break;
            }
        }
        $bucket->FlickrSetID = $fset->ID;
        $bucket->write();
    }


    public function createBucket(): void
    {
        $flickrPhotoIDs = $this->request->param('OtherID');
        $flickrPhotoIDs = Convert::raw2sql($flickrPhotoIDs);
        $flickrSetID = Convert::raw2sql($this->request->param('ID'));

        $ajax_bucket_row = Convert::raw2sql($_GET['bucket_row']);
        $bucketHelper = new FlickrBucketHelper();
        $bucket = $bucketHelper->createBucket($flickrSetID, $flickrPhotoIDs);

        $result = [
            'bucket_id' => $bucket->ID,
            'flickr_set_id' => $flickrSetID,
            'ajax_bucket_row' => $ajax_bucket_row,
        ];

        echo \json_encode($result);
        // abort render
        die;
    }


    public function init(): void
    {
        parent::init();

        if (!Permission::check("FLICKR_EDIT")) {
            //FIXME - enable in the CMS first, then do
            //Security::permissionFailure();
        }

        /*
         OBSOLETE, move to helper classes

        // get flickr details from config
        $key = Config::inst()->get($this->class, 'api_key');
        $secret = Config::inst()->get($this->class, 'secret');
        $access_token = Config::inst()->get($this->class, 'access_token');

        $this->f = new phpFlickr($key, $secret);

        //Fleakr.auth_token    = ''
        $this->f->setToken($access_token);
        */


        // Requirements, etc. here
    }


    // @TODO not sure what is needed here
    public function index(): void
    {
        // Code for the index action here
    }


    public function sets(): void
    {
        $sets = $this->f->photosets_getList('45224965@N04');

        if ($sets) {
            echo "Sets set";
        }


        foreach ($sets['photoset'] as $value) {
            echo '#' . $value['title'];
            echo "\nframework/sake flickr/importSet/" . $value['id'];
            echo "\n\n";
        }
    }


    public function importFromSearch(): void
    {
        $searchParams = [];

        //any high number
        $nPages = 1e7;

        $page = 1;
        $ctr = 1;


        while ($page <= $nPages) {
            echo "\n\nLoading $page / $nPages\n";
            $query = $_GET['q'];
            $searchParams['text'] = $query;
            $searchParams['license'] = 7;
            $searchParams['per_page'] = 500;
            $searchParams['page'] = $page;
            $searchParams['extras'] = 'description, license, date_upload, date_taken, owner_name,' .
                'icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views' .
            ', media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, ' .
                'url_o';
            // 'interestingness-desc'; // also try relevance
            $searchParams['sort'] = 'relevance';

            $data = $this->f->photos_search($searchParams);
            $nPages = $data['pages'];
            $totalImages = $data['total'];

            echo "Found $nPages pages\n";
            echo "n photos returned " . \sizeof($data['photo']);


            foreach ($data['photo'] as $photo) {
                \print_r($photo);

                echo "Import photo $ctr / $totalImages, page $page / $nPages\n";
                $flickrPhoto = $this->createFromFlickrArray($photo);
                echo "\tLoading exif data\n";
                $flickrPhoto->loadExif();
                $ctr++;
            }
            $page++;
        }
    }


    // @todo this should be a helper / task
    public function importSet(): void
    {
        /*

        static $only_new_photos = false;

        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }
        */

        // Code for the register action here
        $flickrSetID = $this->request->param('ID');
        $path = $_GET['path'];
        $parentNode = SiteTree::get_by_link($path);
        if ($parentNode === null) {
            echo "ERROR: Path " . $path . " cannot be found in this site\n";
            die;
        }

        $this->FlickrSetId = $flickrSetID;

        $photos = $this->f->photosets_getPhotos(
            $flickrSetID,
            'license, date_upload, date_taken, owner_name, icon_server, original_format, ' .
                'last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq,' .
                'url_t, url_s, url_m, url_o, url_l,description',
            null,
            500
        );

        $photoset = $photos['photoset'];

        $this->getFlickrSet($flickrSetID);

        // reload from DB with date - note the use of quotes as flickr set id is a string
        $flickrSet = DataObject::get_one('FlickrSet', 'FlickrID=\'' . $flickrSetID . "'");
        $flickrSet->FirstPictureTakenAt = $photoset['photo'][0]['datetaken'];
        $flickrSet->KeepClean = true;
        $flickrSet->Title = $photoset['title'];
        $flickrSet->write();

        echo "Title set to : " . $flickrSet->Title;

        if ($flickrSet->Title === null) {
            echo "ABORTING DUE TO NULL TITLE FOUND IN SET - ARE YOU AUTHORISED TO READ SET INFO?";
            die;
        }

        $datetime = \explode(' ', $flickrSet->FirstPictureTakenAt);
        $datetime = $datetime[0];

        list($year, $month, $day) = \explode('-', $datetime);
        echo "Month: $month; Day: $day; Year: $year<br />\n";

        // now try and find a flickr set page
        $flickrSetPage = DataObject::get_one('FlickrSetPage', 'FlickrSetForPageID=' . $flickrSet->ID);
        if (!$flickrSetPage) {
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
        $flickrSetPage->publish("Live", "Stage");

        $f1 = Folder::find_or_make("flickr/$year");
        $f1->Title = $year;
        $f1->write();

        $f1 = Folder::find_or_make("flickr/$year/$month");
        $f1->Title = $month;
        $f1->write();

        $f1 = Folder::find_or_make("flickr/$year/$month/$day");
        $f1->Title = $day;
        $f1->write();

        \exec("chmod 775 ../assets/flickr/$year");
        \exec("chmod 775 ../assets/flickr/$year/$month");
        \exec("chmod 775 ../assets/flickr/$year/$month/$day");
        \exec("chown gordon:www-data ../assets/flickr/$year");
        ;
        \exec("chown gordon:www-data ../assets/flickr/$year/$month");
        ;
        \exec("chown gordon:www-data ../assets/flickr/$year/$month/$day");
        ;


        $folder = Folder::find_or_make("flickr/$year/$month/$day/" . $flickrSetID);

        $cmd = "chown gordon:www-data ../assets/flickr";
        \exec($cmd);

        \exec('chmod 775 ../assets/flickr');


        // new folder case
        if ($flickrSet->AssetFolderID === 0) {
            $flickrSet->AssetFolderID = $folder->ID;
            $folder->Title = $flickrSet->Title;
            $folder->write();

            $cmd = "chown gordon:www-data ../assets/flickr/$year/$month/$day/" . $flickrSetID;
            \exec($cmd);

            $cmd = "chmod 775 ../assets/flickr/$year/$month/$day/" . $flickrSetID;
            \exec($cmd);
        }

        $flickrSetAssetFolderID = $flickrSet->AssetFolderID;

        $numberOfPics = \count($photoset['photo']);
        $ctr = 1;
        foreach ($photoset['photo'] as $value) {
            echo "Importing photo {$ctr}/${numberOfPics}\n";

            $flickrPhoto = $this->createFromFlickrArray($value);

            if ($value['isprimary'] === 1) {
                $flickrSet->MainImage = $flickrPhoto;
            }


            $flickrPhoto->write();
            $flickrSet->FlickrPhotos()->add($flickrPhoto);
            \gc_collect_cycles();

            $flickrPhoto->write();
            \gc_collect_cycles();

            if (!$flickrPhoto->LocalCopyOfImage) {
                //mkdir appears to be relative to teh sapphire dir
                $structure = "../assets/flickr/$year/$month/$day/" . $flickrSetID;

                if (!\file_exists('../assets/flickr')) {
                    echo "Creating path:" . $structure;

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
                    echo "EXECCED:" . \exec($cmd);

                    $image = new Image();
                    $image->Name = $this->Title;
                    $image->Title = $this->Title;
                    $image->Filename = \str_replace('../', '', $structure . '/' . $fpid . ".jpg");
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
        $sql = 'update FlickrPhoto set Orientation = 90 where ThumbnailHeight > ThumbnailWidth;';
        DB::query($sql);


        // now download exifs
        $ctr = 0;
        foreach ($photoset['photo'] as $value) {
            echo "IMPORTING EXIF {$ctr}/$numberOfPics\n";
            $flickrPhotoID = $value['id'];
            $flickrPhoto = FlickrPhoto::get()->filter('FlickrID', $flickrPhotoID)->first();
            $flickrPhoto->loadExif();
            $flickrPhoto->write();
            $ctr++;
        }

        $this->fixSetMainImages();
        $this->fixDateSetTaken();

        // abort rendering
        die;
    }
}
