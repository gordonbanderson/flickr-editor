<?php
/**
 *  // _config.php
 *	Director::addRules(10, array(
 *		'emptycache' => 'EmptyCacheController',
 *	));
 */


require_once("phpFlickr.php");

class FlickrController extends Page_Controller {

	

	static $allowed_actions = array(
        'index',
        'importSet',
        'editprofile',
        'sets',
        'primeBucketsTest'
    );


    function primeBucketsTest() {
        $fset = DataList::create('FlickrSet')->last();
        $bucket = new FlickrBucket();
        $bucket->write();// get an ID
        $photos = $fset->FlickrPhotos();
        error_log("FLICKR SET:".$fset->ID);
        error_log("PHOTOS FOUND:".$photos->count());

        $bucketPhotos = $bucket->FlickrPhotos();
        $ctr = 0;
        foreach ($photos as $key => $value) {
            $bucketPhotos->add($value);
            error_log("Adding photo ".$value->ID);
            $ctr = $ctr + 1;
            if ($ctr > 7) {
                break;
            }
        }
        $bucket->FlickrSetID = $fset->ID;
        $bucket->write();
    }


     
    public function init() {
        parent::init();

        // get flickr details from config
        $key = Config::inst()->get($this->class, 'api_key');
        $secret = Config::inst()->get($this->class, 'secret');
        $access_token = Config::inst()->get($this->class, 'access_token');

        $this->f = new phpFlickr($key,$secret);

        //Fleakr.auth_token    = ''
        $this->f->setToken($access_token);


        // Requirements, etc. here
    }
     
    public function index() {
        // Code for the index action here
        return array();
    }

    public function sets() {
         $sets = $this->f->photosets_getList('45224965@N04');

        if ($sets) {
            echo "Sets set";
        }


        foreach ($sets['photoset'] as $key => $value) {
            echo '#'.$value['title'];
            echo "\nsapphire/sake flickr/importSet ".$value['id'];
            echo "\n\n";

        }
    }



    public function splitMoblog() {

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
            # code...

            echo "GETING PAGE ".$page;

             $photo_response = $this->f->photosets_getPhotos($flickrSetID, 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description', NULL, NULL, $page);

             $page++;

        
        

            $photos = $photo_response['photoset']['photo'];
            $completed = (count($photos) != 500);


            echo "COUNT:".count($photos);
            echo "COMPLETED?:".$completed;

            foreach ($photos as $key => $photo) {
                $title = $photo['title'];
                $takenAt = $photo['datetaken'];
                $dateParts = split(' ',$takenAt);
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
            error_log("FIRST PIC:".$firstPic);

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



  
  //FIXME - oreination missing
  /*
  mysql> update FlickrPhoto set Orientation = 90 where (ThumbnailWidth > ThumbnailHeight);
Query OK, 70 rows affected (0.01 sec)
Rows matched: 70  Changed: 70  Warnings: 0

mysql> update FlickrPhoto set Orientation = 0 where (ThumbnailWidth < ThumbnailHeight);
Query OK, 53 rows affected (0.00 sec)
Rows matched: 53  Changed: 53  Warnings: 0

*/
     
    public function importSet() {


        $page= 1;
        static $only_new_photos = false;



       // phpInfo();
        
        /*
        ini_set('xdebug.profiler_enable', 'On');
        ini_set('xdebug.show_local_vars',1);
        ini_set('xdebug.profiler_output_dir', '/tmp/xdebug');

        ini_set('xdebug.profiler_enable_trigger', 1 );

        ini_set('xdebug.profiler_enable', 'Off');

        ini_set('xdebug.profiler_append', 'On');
        ini_set('xdebug.profiler_output_name', "profile.log");
*/


/*


        $sets = $this->f->photosets_getList('45224965@N04');
        echo "SETS:".$sets." is set?" .isset($sets);

        if ($sets) {
            echo "Sets set";
        }
        echo (print_r($sets,1));


        foreach ($sets['photoset'] as $key => $value) {
            echo "\nsapphire/sake flickr/importSet ".$value['id'];

        }

        die;
*/


        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if(!$canAccess) return Security::permissionFailure($this);

        // Code for the register action here
        error_log("import set");
        $flickrSetID = $this->request->param('ID');
        //error_log("ID PARAM:".print_r($flickrSetID, 1));

        $this->FlickrSetId = $flickrSetID;



        $photos = $this->f->photosets_getPhotos($flickrSetID, 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description');

        $photoset = $photos['photoset'];

        echo count($photoset);

        foreach ($photoset as $key => $value) {
            error_log("K-V: ".$key.' --> '.$value);
        }



        //var_dump($photoset);


       // error_log("PHOTOSET:".print_r($photoset, 1));
       // $this->photoDebug = print_r($photoset, 1);

        $setInfo = $this->f->photosets_getInfo($flickrSetID);

        $setTitle = $setInfo['title'];
        $setDescription = $setInfo['description'];


       // error_log(print_r($photoset,1));

//FirstPictureTakenAt


       
        // do we have a set object or not
        $flickrSet = DataObject::get_one('FlickrSet', 'FlickrID=\''.$flickrSetID."'");

        // if a set exists update data, otherwise create
        if (!$flickrSet) {
            error_log("Creating new flickr set:".$flickrSetID);
            $flickrSet = new FlickrSet();   
            $flickrSet->FirstPictureTakenAt = $photoset['photo'][0]['datetaken'];
            error_log("Setting first pic date to ".$flickrSet->FirstPictureTakenAt);
        } else {
            error_log("Reusing existing set");
        }

        $flickrSet->Title = $setTitle;
        $flickrSet->Description = $setDescription;
        $flickrSet->FlickrID = $flickrSetID;
        $flickrSet->KeepClean = true;
        $flickrSet->write();

        error_log("Searching for flickr set with flickr ID *".$flickrSetID."*");

        // reload from DB with date - note the use of quotes as flickr set id is a string
        $flickrSet = DataObject::get_one('FlickrSet', 'FlickrID=\''.$flickrSetID."'");
        $flickrSet->KeepClean = true;


        error_log("AFTER RELOAD FS = ".$flickrSet);

        error_log("DATE:".print_r($flickrSet->FirstPictureTakenAt,1));

        $datetime = split(' ', $flickrSet->FirstPictureTakenAt);
        $datetime = $datetime[0];
        error_log("DT:".$datetime);
        list($year, $month, $day) = split('[/.-]', $datetime);
        echo "Month: $month; Day: $day; Year: $year<br />\n";

        

        // now try and find a flickr set page
        $flickrSetPage = DataObject::get_one('FlickrSetPage', 'FlickrSetForPageID='.$flickrSet->ID);
        if (!$flickrSetPage) {
            $flickrSetPage = new FlickrSetPage();   
        }

        $flickrSetPage->Title = $flickrSet->Title;
        $flickrSetPage->Description = $flickrSet->Description;
        //update FlickrSetPage set Description = (select Description from FlickrSet where FlickrSet.ID = FlickrSetPage.FlickrSetForPageID);

        $flickrSetPage->FlickrSetForPageID = $flickrSet->ID;
        $flickrSetPage->write();

        // create a stage version also
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
            exec("chown gordon:www-data ../assets/flickr/$year");;
            exec("chown gordon:www-data ../assets/flickr/$year/$month");;
            exec("chown gordon:www-data ../assets/flickr/$year/$month/$day");;


            $folder = Folder::find_or_make("flickr/$year/$month/$day/" . $flickrSetID);

            $cmd = "chown gordon:www-data ../assets/flickr";
            exec($cmd);

            exec('chmod 775 ../assets/flickr');

            error_log("FOLDER - find or make:".$folder->ID);



 // new folder case
        if ($flickrSet->AssetFolderID == 0) {
            error_log("Setting flickr set asset folder id");
            $flickrSet->AssetFolderID = $folder->ID;
            $folder->Title = $flickrSet->Title;
            $folder->write();

            error_log("FOLDER ID:".$folder->ID);

            error_log("Written folder");
            


             $cmd = "chown gordon:www-data ../assets/flickr/$year/$month/$day/".$flickrSetID;
             error_log("CMD:".$cmd);
            exec($cmd);

            $cmd = "chmod 775 ../assets/flickr/$year/$month/$day/".$flickrSetID;
            error_log("CMD:.$cmd");
            exec($cmd);


        }

        $flickrSetAssetFolderID = $flickrSet->AssetFolderID;

        $flickrSetPageDatabaseID = $flickrSetPage->ID;


        //$flickrSet = NULL;
        $flickrSetPage = NULL;



        foreach ($photoset['photo'] as $key => $value) {

            gc_collect_cycles();


            error_log("\n\n\n====".$key.":".$value['title']);
            error_log("MEMORY:".memory_get_usage(true));

            error_log(print_r($value,1));

            $flickrPhotoID = $value['id'];

            // do we have a set object or not
            $flickrPhoto = DataObject::get_one('FlickrPhoto', 'FlickrID='.$flickrPhotoID);

            // if a set exists update data, otherwise create
            if (!$flickrPhoto) {
                $flickrPhoto = new FlickrPhoto();   
            }

            // if we are in the mode of only importing new then skip to the next iteration if this pic already exists
            else if ($only_new_photos) {
                error_log("\tPhoto already imported - skipping");
                continue;
            }
            error_log("Importing pic");

            $flickrPhoto->Title = $value['title'];

            $flickrPhoto->FlickrID = $flickrPhotoID;
            $flickrPhoto->KeepClean = true;
            

            $flickrPhoto->MediumURL = $value['url_m'];
            $flickrPhoto->MediumHeight = $value['height_m'];
            $flickrPhoto->MediumWidth = $value['width_m'];

            $flickrPhoto->SquareURL = $value['url_s'];
            $flickrPhoto->SquareHeight = $value['height_s'];
            $flickrPhoto->SquareWidth = $value['width_s'];


            $flickrPhoto->ThumbnailURL = $value['url_t'];
            $flickrPhoto->ThumbnailHeight = $value['height_t'];
            $flickrPhoto->ThumbnailWidth = $value['width_t'];

            $flickrPhoto->SmallURL = $value['url_s'];
            $flickrPhoto->SmallHeight = $value['height_s'];
            $flickrPhoto->SmallWidth = $value['width_s'];

            $flickrPhoto->LargeURL = $value['url_l'];
            $flickrPhoto->LargeHeight = $value['height_l'];
            $flickrPhoto->LargeWidth = $value['width_l'];

            $flickrPhoto->OriginalURL = $value['url_o'];
            $flickrPhoto->OriginalHeight = $value['height_o'];
            $flickrPhoto->OriginalWidth = $value['width_o'];

            $flickrPhoto->Description = $value['description'];


          


            $lat = number_format($value['latitude'], 15);
            $lon = number_format($value['longitude'], 15);

  error_log("LAT:".$lat);
            error_log("LON:".$lon);


            if($value['latitude']) {$flickrPhoto->Lat = $lat;}
            if($value['longitude']) {$flickrPhoto->Lon = $lon;}
            if($value['accuracy']) {$flickrPhoto->Accuracy = $value['accuracy'];}
            if(isset($value['geo_is_public'])) {$flickrPhoto->GeoIsPublic = $value['geo_is_public'];}
            if( isset($value['woeid'])) {$flickrPhoto->WoeID = $value['woeid'];}
            
            error_log("GETTING FLICKR PHOTO INFO");


            $singlePhotoInfo = $this->f->photos_getInfo($flickrPhotoID);

           // error_log(print_r($singlePhotoInfo, 1));
//die;
            $flickrPhoto->Description = $singlePhotoInfo['photo']['description'];
            $flickrPhoto->TakenAt = $singlePhotoInfo['photo']['dates']['taken'];
            $flickrPhoto->Rotation = $singlePhotoInfo['photo']['rotation'];

            if(isset($singlePhotoInfo['photo']['visibility'])) {
                $flickrPhoto->IsPublic = $singlePhotoInfo['photo']['visibility']['ispublic'];
            }

            $flickrPhoto->write();


            $photoTagIDs = array();

            error_log("PRE TAGS FLICKR PHOTO ID:".$flickrPhoto->ID);


            foreach ($singlePhotoInfo['photo']['tags']['tag'] as $key => $taginfo) {

                error_log("Checking tag ".$taginfo['_content']);
              //  $tag = DataObject::get_one('Tag', 'Value = \''.$taginfo['_content']+'\'');

                $tag = DataObject::get_one('FlickrTag', "\"Value\"='".$taginfo['_content']."'");

                if (!$tag) {
                    $tag = new FlickrTag();
                }


                $tag->FlickrID = $taginfo['id'];
                $tag->Value = $taginfo['_content'];
                $tag->RawValue = $taginfo['raw'];


                $tag->write();


                error_log("TA, PHOTO IDS:".$tag->ID.", ".$flickrPhoto->ID);


                error_log("EXPLODED TAG");

                $ftags= $flickrPhoto->FlickrTags();
                $ftags->add($tag);

                error_log("Added tag ".$tag->ID." to pics ".$flickrPhoto->ID);
                $flickrPhoto->write();


                $tag = NULL;
                $ftags = NULL;

                gc_collect_cycles();







/*
                $tag->FlickrPhotos = DataObject::get ('FlickrPhoto', "ID=-1");
                $tag->write(); 

                $tag->FlickrPhotos->add($flickrPhoto);

                array_push($photoTagIDs, $tag->ID);
                */
            }


            $flickrPhoto->write();
            $flickrSet->FlickrPhotos()->add($flickrPhoto);
            gc_collect_cycles();




            // now get the exif data
            error_log("Loading EXIF data");
            $exifData = $this->f->photos_getExif($flickrPhotoID);
           // error_log(print_r($exifData,1));

            // delete the old exif data
            $sql = "DELETE from FlickrExif where FlickrPhotoID=".$flickrPhoto->ID;
            error_log($sql);
            DB::query($sql);


            foreach ($exifData['exif'] as $key => $exifInfo) {
                $exif = new FlickrExif();
                $exif->TagSpace = $exifInfo['tagspace'];
                $exif->TagSpaceID = $exifInfo['tagspaceid'];
                $exif->Tag = $exifInfo['tag'];
                $exif->Label = $exifInfo['label'];
                $exif->Raw = $exifInfo['raw'];
                $exif->FlickrPhotoID = $flickrPhoto->ID;
                $exif->write();

                if ($exif->Tag == 'ImageUniqueID') {
                    $flickrPhoto->ImageUniqueID = $exif->Raw;
                } else
                if ($exif->Tag == 'ISO') {
                    $flickrPhoto->ISO = $exif->Raw;
                } else
                if ($exif->Tag == 'ExposureTime') {
                    $flickrPhoto->ShutterSpeed = $exif->Raw;
                } else
                if ($exif->Tag == 'FocalLengthIn35mmFormat') {
                    $raw35 = $exif->Raw;
                    error_log("RAW 35:".$raw35);
                    $fl35 = str_replace(' mm', '', $raw35);

                    error_log("POST MANGLING 1: ".$fl35);

                    $fl35 = (int) $fl35;

                    error_log("POST MANGLING 2: ".$fl35);
                    $flickrPhoto->FocalLength35mm = $fl35;
                } else
                if ($exif->Tag == 'FNumber') {
                     $flickrPhoto->Aperture = $exif->Raw;
                };

                $exif = NULL;
                gc_collect_cycles();


            }


            $flickrPhoto->write();
                    gc_collect_cycles();



            if(!$flickrPhoto->LocalCopyOfImage) {


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
                    $galleries = NULL;
                }


               

/*
                    if (!$folder->ID) {
                        $folder->Title = $flickrSet->Title;
                        $folder->setName($flickrSet->Title);
                        $folder->write();

                        

                          
                    } else {
                        error_log("FOLDER EXISTS");
                    }
*/


                   
                   


                   

                          

                    

                    
                    
                   // $this->requireDefaultAlbum();
                    //FormResponse::add( "\$( 'Form_EditForm' ).getPageFromServer( $this->ID );" );
                

               




                
                $download_images = Config::inst()->get($this->class, 'download_images');
                error_log("DOWNLOAD IMAGES? ".$download_images);

                if ($download_images && !($flickrPhoto->LocalCopyOfImageID)) {
                    error_log("No local copy - downloading from flickr");
                    error_log("MEM IMAGE T1:".memory_get_usage(true));
                    $largeURL = $flickrPhoto->LargeURL;
                    $fpid = $flickrPhoto->FlickrID;
                    error_log("MEM IMAGE T2:".memory_get_usage(true));

                    $cmd = "wget -O $structure/$fpid.jpg $largeURL";
                    exec($cmd);

                    error_log("MEM IMAGE T3:".memory_get_usage(true));


                    $cmd = "chown  gordon:www-data $structure/$fpid.jpg";
                   // $cmd = "pwd";
                    error_log("COMMAND:".$cmd);

                    echo "EXECCED:".exec($cmd);

                    error_log("MEM IMAGE T4:".memory_get_usage(true));

                    $image = new Image();
                    $image->Name = $this->Title;
                    $image->Title = $this->Title;
                    $image->Filename = str_replace('../', '', $structure.'/'.$fpid.".jpg");
                    error_log("Setting title of image to ".$flickrPhoto->Title);
                    $image->Title = $flickrPhoto->Title;
                    //$image->Name = $flickrPhoto->Title;
                    $image->ParentID = $flickrSetAssetFolderID;
                    error_log("MEM IMAGE T5:".memory_get_usage(true));
        gc_collect_cycles();

                    $image->write();
        gc_collect_cycles();

                    error_log("MEM IMAGE T6:".memory_get_usage(true));


                    error_log("Setting image parent id to "+$flickrSetAssetFolderID);


                    $flickrPhoto->LocalCopyOfImageID = $image->ID;
                    error_log("MEM IMAGE T7:".memory_get_usage(true));

                    $flickrPhoto->write();
                                        error_log("MEM IMAGE T8:".memory_get_usage(true));

                    $image = NULL;
                                        error_log("MEM IMAGE T9:".memory_get_usage(true));

                }



            

               


            }


            // do we have a page, if not create one.  Then populate it with the photo


            error_log("MEM T10:".memory_get_usage(true));

        
            error_log("MEM T11:".memory_get_usage(true));



            $flickrPhoto = NULL;

            error_log("MEM T14:".memory_get_usage(true));


        }


        //update orientation
        error_log("Updating orientations");
        $sql = 'update FlickrPhoto set Orientation = 90 where ThumbnailHeight > ThumbnailWidth;';
        DB::query($sql);

        //error_log(print_r($photos,1));

        error_log("Abort render");
        die(); // abort rendering


        return array();
    }



     
    public function editprofile() {
        // Code for the editprofile action here
        return array();
    }
	
}