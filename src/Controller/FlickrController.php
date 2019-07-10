<?php
namespace Suilven\Flickr\Controller;

use Suilven\Flickr\Helper\FlickrBatchHelper;
use Suilven\Flickr\Helper\FlickrBucketHelper;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Symfony\Component\Yaml\Dumper;
use SilverStripe\Control\Director;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\ORM\DataList;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Config\Config;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DB;
use SilverStripe\Security\PermissionProvider;

// @todo FIX
// require_once "phpFlickr.php";

class FlickrController extends \PageController implements PermissionProvider
{
    private static $allowed_actions = array(
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
        'importSearchToYML'
    );


    /*

    if ($name === 'C6602') {
                                $this->FocalLength35mm = 28;
                                $fixFocalLength = 28;
                            }

                            if ($name === 'Canon IXUS 220 HS') {
                                $focalConversionFactor = 5.58139534884;
                            }
     */


    public function fixDescriptions()
    {
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $sets = FlickrSet::get()->Filter('Description', 'Array');
        foreach ($sets->getIterator() as $set) {
            echo $set->Title."\n";
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

    public function fixFocalLength35()
    {
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $finished = false;

        while (!$finished) {
            $photos = FlickrPhoto::get()->filter(array('FocalLength35mm' =>0,'IsPublic' => 1))->limit(50);

            if (count($photos) == 0) {
                $finished = true;
            }

            foreach ($photos as $photo) {
                echo $photo->Title."(".$photo->FlickrID.")\n";
                $modelExif = $photo->Exifs()->filter('Tag', 'Model')->first();

                //foreach ($photo->Exifs() as $exif) {
                //	echo ' - '.$exif->Tag.' => '.$exif->Raw."\n";
                //}

                if (isset($modelExif)) {
                    $model = $modelExif->Raw;
                    $focalLengthExif = $photo->Exifs()->filter('Tag', 'FocalLength')->first();
                    $mm = (int) str_replace(' mm', '', $focalLengthExif->Raw);

                    if ($model === 'Canon IXUS 220 HS') {
                        $f35 = round($mm * 5.58139534884);
                        $photo->FocalLength35mm = $f35;
                    } elseif ($model === 'C6602') {
                        $photo->FocalLength35mm = 28;
                    } elseif ($model === 'Canon EOS 450D') {
                        $f35 = round($mm * 1.61428571429);
                        $photo->FocalLength35mm = $f35;
                    }

                    echo " - Focal length set to ".$photo->FocalLength35mm."\n";
                    $photo->write();
                } else {
                    echo " - No exif data for image ".$photo->FlickrID."\n";

                    // mark private for now
                    $photo->IsPublic = false;
                    $photo->write();
                }
            }
        }

        die; // abort rendering
    }

    public function fixArticleDates()
    {
        $articles = DataList::create('Article')->where('StartTime is null');
        foreach ($articles->getIterator() as $article) {
            $article->StartTime = $article->Created;
            $article->write();
        }
    }


    public function providePermissions()
    {
        return array(
            "FLICKR_EDIT" => "Able to import and edit flickr data"
        );
    }


    public function primeFlickrSetFolderImages()
    {
        $folders = DataList::create('FlickrSetFolder')->where('MainFlickrPhotoID = 0');

        foreach ($folders as $folder) {
            foreach ($folder->Children() as $folderOrSet) {
                $cname = $folderOrSet->ClassName;
                // we want to find a flickr set page we can use the image from
                if ($cname == 'FlickrSetPage') {
                    $flickrImage = $folderOrSet->getPortletImage();
                    //error_log("FI:".$flickrImage>" ID=".$flickrImage->ID);
                    if ($flickrImage->ID != 0) {
                        $folder->MainFlickrPhotoID = $flickrImage->ID;
                        $folder->write();
                        continue;
                    }
                }
            }
        }
    }


    public function dumpSetAsJson()
    {
        die;
    }


    public function setToJson()
    {
        $flickrSetID = $this->request->param('ID');
        $flickrSet = DataList::create('FlickrSet')->where('FlickrID = '.$flickrSetID)->first();
        $images = array();
        foreach ($flickrSet->FlickrPhotos() as $fp) {
            $image = array();
            $image['MediumURL'] = $fp-> MediumURL;
            $image['BatchTitle'] = $fp-> Title;
        }
    }


    public function updateEditedImagesToFlickr()
    {
        $flickrSetID = $this->request->param('ID');
        $flickrSet = FlickrSet::get()->filter(array('FlickrID' => $flickrSetID))->first();
        $flickrSet = DataObject::get_by_id(FlickrSet::class, $flickrSetID);

        if ($flickrSet) {
            $flickrSet->writeToFlickr();
        } else {
            error_log('Flickr set could not be found');
        }
    }





    public function fixArticles()
    {
        $articles = DataList::create('Article')->sort('Title');
//        $articles->where('Article_Live.ID=32469');
        foreach ($articles as $article) {
            $content = $article->Content;
            $sections = explode('FLICKRPHOTO_', $content);
            $alteredContent = '';
            foreach ($sections as $section) {
                //$splits2 = split(' ', $section);
                //$flickrIDwithCrud = array_shift($splits2);
                $flickrID = '';
                for ($i=0;  $i<strlen($section);$i++) {
                    if (is_numeric($section[$i])) {
                        $flickrID .= $section[$i];
                    } else {
                        break;
                    }
                }

//                $restOfCrud = str_replace($flickrID, '', $flickrIDwithCrud);
                $section = str_replace($flickrID, '', $section);
                $section = '[FlickrPhoto id='.$flickrID.']'. $section;

                $section = str_replace('<p> </p>', '', $section);
                $alteredContent .= $section;
            }

            $article->Content = $alteredContent;

            try {
                $article->write();
                $article->publish("Live", "Stage");
            } catch (Exception $e) {
                error_log("Unable to write article ".$article->ID);
                error_log($e);
            }
        }
    }



    public function ajaxSearchForPhoto()
    {
        //FIXME authentication

        $flickrPhotoID = Convert::raw2sql($this->request->param('ID'));

        $flickrPhoto = DataList::create('FlickrPhoto')->where('FlickrID='.$flickrPhotoID)->first();
        $not_found = !$flickrPhoto ;

        $result = array(
            'found' => !$not_found
        );

        if ($flickrPhoto) {
            $result['title'] = $flickrPhoto->Title;
            $result['small_url'] = $flickrPhoto->SmallURL;
            $result['medium_url'] = $flickrPhoto->MediumURL;
            $result['id'] = $flickrPhoto->ID;
            $result['description'] = $flickrPhoto->Description;
        }

        return json_encode($result);
    }


    public function batchUpdateSet()
    {
        //FIXME authentication

        $flickrSetID = Convert::raw2sql($this->request->param('ID'));
        $batchTitle = Convert::raw2sql($_POST['BatchTitle']);
        $batchDescription = Convert::raw2sql($_POST['BatchDescription']);
        $batchTags = str_getcsv(Convert::raw2sql($_POST['BatchTags']));

        $flickrSet = DataObject::get_by_id(FlickrSet::class, $flickrSetID);
        $helper = new FlickrBatchHelper();
        $result = $helper->batchUpdateSet($flickrSet, $batchTitle, $batchDescription, $batchTags);

        return json_encode($result);
    }


    public function PublishAllFlickrSetPages()
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


    public function fixPhotoTitles()
    {
        $sets = DataList::create('FlickrSet');
        foreach ($sets as $set) {
            $pageCtr = 1;
            $flickrSetID = $set->FlickrID;

            $mainImageFlickrID = null;
            $allPagesRead = false;

            while (!$allPagesRead) {
                $photos = $this->f->photosets_getPhotos(
                    $flickrSetID,
                    'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description',
                    null,
                    500,
                    $pageCtr
                );

                $pageCtr = $pageCtr+1;



                //print_r($photos);
                $photoset = $photos['photoset'];
                $page = $photoset['page'];
                $pages = $photoset['pages'];
                $allPagesRead = ($page == $pages);


                foreach ($photoset['photo'] as $key => $photo) {
                    $fp = DataList::create('FlickrPhoto')->where('FlickrID = '.$photo['id'])->first();

                    if ($fp == null) {
                        continue;
                    }

                    $title = $photo['title'];
                    if (strlen($title) > 48) {
                        $fp->Title = $title;
                        $fp->write();
                        //error_log($fp->FlickrID . '==' . $photo['id']. '??');
                    };
                }
            }
        }
    }




    /* Fix the many many relationships, previously FlickrSetPhoto pages which have now been deleted */
    public function fixSetPhotoManyMany()
    {
        $flickrSetID = Convert::raw2sql($this->request->param('ID'));
        $flickrSets = DataList::create('FlickrSet')->where("FlickrID=".$flickrSetID);

        $allPagesRead = false;
        $flickrPhotoIDs = array();


        if ($flickrSets->count() == 1) {
            $flickrSet = $flickrSets->first();

            $pageCtr = 1;

            while (!$allPagesRead) {
                $photos = $this->f->photosets_getPhotos(
                    $flickrSetID,
                    'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description',
                    null,
                    500,
                    $pageCtr
                );

                $pageCtr = $pageCtr+1;



                //print_r($photos);
                $photoset = $photos['photoset'];
                $page = $photoset['page'];
                $pages = $photoset['pages'];
                $allPagesRead = ($page == $pages);


                foreach ($photoset['photo'] as $key => $photo) {
                    array_push($flickrPhotoIDs, $photo['id']);
                }
            }

            $flickrPhotos = DataList::create('FlickrPhoto')->where("FlickrID in (".implode(',', $flickrPhotoIDs).")");
            $flickrSet->FlickrPhotos()->removeAll();
            $flickrSet->FlickrPhotos()->addMany($flickrPhotos);
            $flickrSet->write();
        } else {
            // no flickr set found for the given ID
            error_log("Flickr set not found for id ".$flickrSetID);
        }
    }


    public function primeBucketsTest()
    {
        $fset = DataList::create('FlickrSet')->last();
        $bucket = new FlickrBucket();
        $bucket->write();// get an ID
        $photos = $fset->FlickrPhotos();

        $bucketPhotos = $bucket->FlickrPhotos();
        $ctr = 0;
        foreach ($photos as $key => $value) {
            $bucketPhotos->add($value);
            $ctr = $ctr + 1;
            if ($ctr > 7) {
                break;
            }
        }
        $bucket->FlickrSetID = $fset->ID;
        $bucket->write();
    }



    public function createBucket()
    {
        $flickrPhotoIDs = $this->request->param('OtherID');
        $flickrPhotoIDs = Convert::raw2sql($flickrPhotoIDs);
        $flickrSetID = Convert::raw2sql($this->request->param('ID'));

        $ajax_bucket_row = Convert::raw2sql($_GET['bucket_row']);
        $bucketHelper = new FlickrBucketHelper();
        $bucket = $bucketHelper->createBucket($flickrSetID, $flickrPhotoIDs);

        $result = array(
            'bucket_id' => $bucket->ID,
            'flickr_set_id' => $flickrSetID,
            'ajax_bucket_row' => $ajax_bucket_row
        );

        echo json_encode($result);
        die; // abort render
    }





    public function init()
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

    public function index()
    {
        // Code for the index action here
        return array();
    }

    public function sets()
    {
        $sets = $this->f->photosets_getList('45224965@N04');

        if ($sets) {
            echo "Sets set";
        }


        foreach ($sets['photoset'] as $key => $value) {
            echo '#'.$value['title'];
            echo "\nframework/sake flickr/importSet/".$value['id'];
            echo "\n\n";
        }
    }




    public function moveXperiaPics()
    {
        $moblogbucketsetid = $this->request->param('ID');
        //  $moblogset = FlickrSet::get()->filter(array('FlickrID' => $moblogbucketsetid))->first();
        $photos = $this->f->photos_search(array("user_id" => "me", "per_page" => 500, 'extras' => 'description, license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o'));
    }


    public function changeFlickrSetMainImage()
    {
        $flickrsetID = $this->request->param('ID');
        $flickrphotoID = $this->request->param('OtherID');
        $flickrset = FlickrSet::get()->filter('ID', $flickrsetID)->first();
        $flickrset->PrimaryFlickrPhotoID = $flickrphotoID;
        $flickrset->write();
    }



    public function splitMoblog()
    {

        /*
        echo "Created set";
        var_dump($r);
        die;
*/

        $flickrSetID = '72157624403053639';

        // var_dump($photo_response);

        $dateToImages = array();

        $photos = array();


        $page = 1;

        $completed = false;


        while (!$completed) {
            // code...
            echo "GETING PAGE ".$page;
            $photo_response = $this->f->photosets_getPhotos($flickrSetID, 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description', null, null, $page);
            $page++;

            $photos = $photo_response['photoset']['photo'];
            $completed = (count($photos) != 500);


            echo "COUNT:".count($photos);
            echo "COMPLETED?:".$completed;

            foreach ($photos as $key => $photo) {
                $title = $photo['title'];
                $takenAt = $photo['datetaken'];
                $dateParts = split(' ', $takenAt);
                $date = $dateParts[0];

                if (!isset($dateToImages[$date])) {
                    $dateToImages[$date] = array();
                }

                array_push($dateToImages[$date], $photo);



                echo $date." :: ".$title;
                echo "\n";
            }
        }


        echo "************ DONE";

        foreach ($dateToImages as $date => $photosForDate) {
            echo "DATE:".$date."\n";
            $firstPic = $dateToImages[$date][0]['id'];

            $set = $this->f->photosets_create('Moblog '.$date, 'Mobile photos for '.$date, $firstPic);
            var_dump($set);

            $setId = $set['id'];


            //      function photosets_addPhoto ($photoset_id, $photo_id) {


            foreach ($dateToImages[$date] as $key => $image) {
                echo "\t".$image['title']."\n";
                $this->f->photosets_addPhoto($setId, $image['id']);
                //      function photos_addTags ($photo_id, $tags) {

                $this->f->photos_addTags($image['id'], "moblog iphone3g");
            };

            echo "SET COMPLETED\n\n\n\n";
        }
    }





    //FIXME - oreintation missing
    /*
    mysql> update FlickrPhoto set Orientation = 90 where (ThumbnailWidth > ThumbnailHeight);
Query OK, 70 rows affected (0.01 sec)
Rows matched: 70  Changed: 70  Warnings: 0

mysql> update FlickrPhoto set Orientation = 0 where (ThumbnailWidth < ThumbnailHeight);
Query OK, 53 rows affected (0.00 sec)
Rows matched: 53  Changed: 53  Warnings: 0

*/

    public function importSearchToYML()
    {
        $searchParams = array();

        $query = $_GET['q'];
        $searchParams['text'] = $query;
        $searchParams['license'] = 7;
        $searchParams['per_page'] = 100;
        $searchParams['extras'] = 'description, license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o';
        $searchParams['sort'] = 'relevance'; // 'interestingness-desc'; // also try relevance

        $data = $this->f->photos_search($searchParams);

        $dumper = new Dumper();
        $fixtures = array();
        $ctr = 1;

        $apertures = array('2.8', '5.6', '11', '16', '22');
        $shutterSpeeds = array('1/100', '1/30', '1/15', '1/2', '2', '6', '2/250');
        $isos = array(64,100,200,400,800,1600,2000,3200);
        $focalLengths = array(24,50,80,90,120,150,200);

        foreach ($data['photo'] as $photo) {
            // the image URL becomes somthing like
            // http://farm{farm-id}.static.flickr.com/{server-id}/{id}_{secret}.jpg
            //
            $photoid = str_pad("$ctr", 4, '0', STR_PAD_LEFT);
            /*
            FlickrPhoto:
              photo0001:
                Title: Bangkok
                Description: Test photograph
                FlickrID: 1234567
                TakenAt: 24/4/2012 18:12
                FirstViewed: 28/4/2012
                Aperture: 8.0
                ShutterSpeed: 1/100
                FocalLength35mm: 140
                ISO: 400
                MediumURL: 'http://www.test.com/test.jpg',
                MediumHeight: 400,
                MediumWidth: 300

                sed s/\{\ \_content\:\ // | sed s/\ \}//
                sudo -u www-data framework/sake flickr/importSearch q='Bangkok Thailand' | sed s/\{\ \_content\:\ // | sed s/\ \}// > /home/gordon/work/git/weboftalent/moduletest/www/elastica/tests/lotsOfPhotos.yml

             */
            $currentFixture = array();
            $currentFixture['FlickrID'] = intval($photo['id']);
            $currentFixture['Title'] = $photo['title'];
            $descBlob = $photo['description']['_content'];
            $description = '';



            $splits = preg_split('/$\R?^/m', $descBlob);

            foreach ($splits as $line) {
                $description .= "<p>";
                $description .= $line;
                $description .= "</p>";
            }

            $currentFixture['Description'] = $description;
            $currentFixture['Aperture'] = $apertures[array_rand($apertures)];
            $currentFixture['ShutterSpeed'] = $shutterSpeeds[array_rand($shutterSpeeds)];
            $currentFixture['FocalLength35mm'] = $focalLengths[array_rand($focalLengths)];
            $currentFixture['ISO'] = $isos[array_rand($isos)];
            $currentFixture['IndexingOff'] = true;


            /*
                static $belongs_many_many = array(
                    'FlickrSets' => 'FlickrSet'
                );

                //1 to many
                static $has_one = array(
                    'Photographer' => 'FlickrAuthor'
                );

                //many to many
                static $many_many = array(
                    'FlickrTags' => 'FlickrTag'
                );
             */

            // get owner photo['owner']

            $fixtures['photo'.$photoid] = $currentFixture;


            $ctr++;
            $url = "https://www.flickr.com/photos/{$photo['owner']}/{$photo["id"]}/";
        }
        $fixtures = array('FlickrPhoto' => $fixtures);
        $yaml = $dumper->dump($fixtures, 3);
        echo $yaml;
        file_put_contents('/tmp/test.yml', $yaml);
        $cmd = 'cat elastica/tests/lotsOfPhotos.yml | sed s/\{\ \_content\:\ // | sed s/\ \}//';//.
                    //' > /home/gordon/work/git/weboftalent/moduletest/www/elastica/tests/lotsOfPhotos.yml';
        //exec($cmd);
    }




    public function importFromSearch()
    {
        $searchParams = array();

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
            $searchParams['extras'] = 'description, license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o';
            $searchParams['sort'] = 'relevance'; // 'interestingness-desc'; // also try relevance

            $data = $this->f->photos_search($searchParams);
            $nPages = $data['pages'];
            $totalImages = $data['total'];

            echo "Found $nPages pages\n";
            echo "n photos returned ".sizeof($data['photo']);


            foreach ($data['photo'] as $photo) {
                print_r($photo);

                echo "Import photo $ctr / $totalImages, page $page / $nPages\n";
                $flickrPhoto = $this->createFromFlickrArray($photo);
                echo "\tLoading exif data\n";
                $flickrPhoto->loadExif();
                $ctr++;
            }
            $page++;
        }
    }



    public function importSet()
    {
        $page= 1;
        static $only_new_photos = false;

        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }
        /*
        // For testing
        $flickrPhoto = FlickrPhoto::get()->filter('ID',100)->first();
        $flickrPhoto->loadExif();
        die;
        */

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








    public function editprofile()
    {
        // Code for the editprofile action here
        return array();
    }
}
