<?php
/**
 *  // _config.php
 * Director::addRules(10, array(
 *  'emptycache' => 'EmptyCacheController',
 * ));
 */


require_once "phpFlickr.php";

class FlickrController extends Page_Controller implements PermissionProvider {

	static $allowed_actions = array(
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
		'changeFlickrSetMainImage'
	);

	public function fixArticleDates() {
		$articles = DataList::create('Article')->where('StartTime is null');
		foreach ($articles->getIterator() as $article) {
			$article->StartTime = $article->Created;
			$article->write();
			error_log("Updated: ".$article->StartTime." : ".$article->Title);
		}
	}


	public function providePermissions() {
		return array(
			"FLICKR_EDIT" => "Able to import and edit flickr data"
		);
	}


	function primeFlickrSetFolderImages() {
		$folders = DataList::create('FlickrSetFolder')->where('MainFlickrPhotoID = 0');

		foreach ($folders as $folder) {
			error_log("++++ FIXING CHILDREN OF ".$folder->Title." ++++");
			foreach ($folder->Children() as $folderOrSet) {
				$cname = $folderOrSet->ClassName;
				// we want to find a flickr set page we can use the image from
				if ($cname == 'FlickrSetPage') {
					$flickrImage = $folderOrSet->getPortletImage();
					//error_log("FI:".$flickrImage>" ID=".$flickrImage->ID);
					if ($flickrImage->ID != 0) {
						$folder->MainFlickrPhotoID = $flickrImage->ID;
						$folder->write();
						error_log("Found image ".$flickrImage->ID.' for folder '.$folder->ID);
						error_log("TITLE:".$flickrImage->Title);
						continue;
					}
				}

				error_log("++++ /FIXING CHILDREN OF ".$folder->Title." ++++");


			}
		}
	}


	public function dumpSetAsJson() {
		error_log("DUMPING SET AS JSON");

		die;
	}


	public function setToJson() {
		error_log("+++ SET TO JSON +++");
		$flickrSetID = $this->request->param( 'ID' );
		$flickrSet = DataList::create('FlickrSet')->where('FlickrID = '.$flickrSetID)->first();
		error_log("FLICKR SET:".$flickrSet);
		 $images = array();
		foreach ($flickrSet->FlickrPhotos() as $fp) {
			$image = array();
			$image['MediumURL'] = $fp-> MediumURL;
			$image['BatchTitle'] = $fp-> Title;
		}

		error_log(json_decode($images));
	}






	/*

	Dreamhost 5.1.56
	Ubuntu 5.5.29
	*/
	public function updateEditedImagesToFlickr() {
		$flickrSetID = $this->request->param( 'ID' );
		echo "T1:".FlickrSet::get()->filter('FlickrID',$flickrSetID)->sql();
		$flickrSet = FlickrSet::get()->filter(array('FlickrID' => $flickrSetID))->first();

		if ($flickrSet) {
			//DataList::create('FlickrSet')->where('FlickrID = '.$flickrSetID)->first();
			error_log("Updating to flickr: ".$flickrSet->Title);
			$flickrSet->writeToFlickr();
		} else {
			error_log("Unable to fnd flickr set with id *".$flickrSetID.'*');
		}


	}

	public function fixDateSetTaken() {
		$fsps = DataList::create('FlickrSetPage')->where('FirstPictureTakenAt is NULL');
		error_log($fsps->count()." set pages to fix");
		foreach ($fsps as $fsp) {
			error_log("---- FSP:".$fsp->ID.' ----');
			$fs = $fsp->FlickrSetForPage();
			error_log("FLICKR SET FOR PAGE:".$fs." - ".$fs->ID);

			if ($fs->ID == 0) {
				error_log("BROKEN FLICKR SET PAGE:".$fsp->ID);
				continue;
			}
			if ($fs->FirstPictureTakenAt == null) {
				error_log("T1 Flickr set first picture taken at date is null");
				$firstDate = $fs->FlickrPhotos()->sort('TakenAt')->where('TakenAt is not null');
				error_log($firstDate->sql());
				$firstDate = $firstDate->first();

				error_log("FD:".print_r($firstDate,1));
				error_log("IS SET:".isset($firstDate));

				if ($firstDate) {
					$fs->FirstPictureTakenAt = $firstDate->TakenAt;
					error_log("FROM PICS TAKEN AT:".$firstDate->TakenAt);
					$fs->KeepClean = true;
					$fs->write();
				} else {
					error_log("Set page has no photos with a non null time");
				}

			} else {
				error_log("T2 Flickr set first picture takent at date:".$fs->FirstPictureTakenAt);
			}
			$fsp->FirstPictureTakenAt = $fs->FirstPictureTakenAt;
			$fsp->write();

			error_log('FIRSTPICDATETIME:'.$fsp->Title . ' => '. $fsp->FirstPictureTakenAt);

			$fsp->publish( "Live", "Stage" );


		}
	}


	public function fixArticles() {
		$articles = DataList::create('Article')->sort('Title');
//        $articles->where('Article_Live.ID=32469');
		foreach($articles as $article) {
			error_log("Fixing: ".$article->Title);
			$content = $article->Content;
			$sections = split('FLICKRPHOTO_', $content);
			$alteredContent = '';
			foreach($sections as $section) {
				//$splits2 = split(' ', $section);
				//$flickrIDwithCrud = array_shift($splits2);
				$flickrID = '';
				for($i=0;  $i<strlen($section);$i++) {
					if (is_numeric($section[$i])) {
						$flickrID .= $section[$i];
					} else {
						break;
					}
				}

				error_log("FOUND PICTURE *".$flickrID.'*');
//                $restOfCrud = str_replace($flickrID, '', $flickrIDwithCrud);
				$section = str_replace($flickrID, '', $section);
				$section = '[FlickrPhoto id='.$flickrID.']'. $section;

				$section = str_replace('<p> </p>', '', $section);
				$alteredContent .= $section;
			}


			error_log("CONTENT");
			error_log($content);
			error_log("ALTERED CONTENT");
			error_log($alteredContent);

			$article->Content = $alteredContent;

			try {
				$article->write();
				$article->publish( "Live", "Stage" );

			} catch (Exception $e) {
				error_log("Unable to write article ".$article->ID);
				error_log($e);
			}

		}
	}



	public function ajaxSearchForPhoto() {
		//FIXME authentication
		error_log("batch update set");
		error_log(print_r($_POST,1));

		$flickrPhotoID = Convert::raw2sql( $this->request->param( 'ID' ) );
		error_log("AJAX SEARCH: ".$flickrPhotoID);

		$flickrPhoto = DataList::create('FlickrPhoto')->where('FlickrID='.$flickrPhotoID)->first();
		error_log("FLICKR PHOTO:".$flickrPhoto);
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

		error_log("RESULT:".print_r($result,1));

		return json_encode($result);

	}


	public function batchUpdateSet() {
		//FIXME authentication
		error_log("batch update set");
		error_log(print_r($_POST,1));

		$flickrSetID = Convert::raw2sql( $this->request->param( 'ID' ) );
		$batchTitle = Convert::raw2sql($_POST['BatchTitle']);
		$batchDescription = Convert::raw2sql($_POST['BatchDescription']);
		$batchTags = str_getcsv(Convert::raw2sql($_POST['BatchTags']));

		error_log("BATCH TITLE:".$batchTitle);
		error_log("BATCH DESCRIPTION:".$batchDescription);
		error_log("BATCH TAGS:".$batchTags);

		error_log("looking for flickr set with id ".$flickrSetID);
		$flickrSet = DataList::create('FlickrSet')->where('ID = '.$flickrSetID)->first();
		error_log("SET:".$flickrSet->FlickrID." , ".$flickrSet->Title);
		$flickrPhotos = $flickrSet->FlickrPhotos();

		 // $batchDescription = $batchDescription ."\n\n".$flickrSet->ImageFooter;
		 // $batchDescription = $batchDescription ."\n\n".$this->SiteConfig()->ImageFooter;

		$tags = array();
		foreach ($batchTags as $batchTag) {
			$batchTag = trim($batchTag);
			error_log("BATCH TAG:".$batchTag);
			$lowerCaseTag = strtolower($batchTag);
			$possibleTags = DataList::create('FlickrTag')->where("Value='".$lowerCaseTag."'");

			if ($possibleTags->count() == 0) {
				error_log("Creating new tag for ".$lowerCaseTag);
				$tag = new FlickrTag();
				$tag->Value = $lowerCaseTag;
				$tag->RawValue = $batchTag;
				$tag->write();
			}  else {
				error_log("Else found tag ".$lowerCaseTag);
				$tag = $possibleTags->first();
			}

			error_log("TAG:".$tag->ID);

			array_push($tags, $tag->ID);
			//$tag = DataList::create('FlickrExif')->where('WIP');
		}


		error_log("TAGS:".print_r($tags,1));

		foreach ($flickrPhotos as $fp) {
			error_log("Updating ".$fp->ID);
			error_log("Changing title from '".$fp->Title."' to '".$batchTitle."'");
			$fp->Title=$batchTitle;
			$fp->Description = $batchDescription;
			$fp->FlickrTags()->addMany($tags);
			$fp->write();
		}

		$result = array(
			'number_of_images_updated' => $flickrPhotos->count()
		);

		return json_encode($result);

	}


	public function PublishAllFlickrSetPages() {
		$pages = DataList::create('FlickrSetPage');
		foreach ($pages as $fsp) {
			error_log("Publshing page ".$fsp->Title);
			$fsp->publish( "Stage", "Live" );
		}

		$pages = DataList::create('FlickrSetFolder');
		foreach ($pages as $fsp) {
			error_log("Publshing page ".$fsp->Title);
			$fsp->publish( "Stage", "Live" );
		}
	}


	 public function fixPhotoTitles() {


		$sets = DataList::create('FlickrSet');
		foreach($sets as $set) {
			error_log("Fixing titles for ".$set->ID.':'.$set->Title."\n");

			$pageCtr = 1;
			$flickrSetID = $set->FlickrID;

			$mainImageFlickrID = null;
			$allPagesRead = false;

			while ( !$allPagesRead ) {
				$photos = $this->f->photosets_getPhotos( $flickrSetID,
					'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description',
					NULL,
					500,
					$pageCtr );

				$pageCtr = $pageCtr+1;



				//print_r($photos);
				$photoset = $photos['photoset'];
				$page = $photoset['page'];
				$pages = $photoset['pages'];
				$allPagesRead = ( $page == $pages );
				error_log( "Fixing page $page of $pages, all read = $allPagesRead" );


				foreach ( $photoset['photo'] as $key => $photo ) {
					$fp = DataList::create('FlickrPhoto')->where('FlickrID = '.$photo['id'])->first();

					if ($fp == null) {
						error_log("MISSING IMAGE IN SS DB:".$photo['id']);
						continue;
					}

					$title = $photo['title'];
					if (strlen($title) > 48) {
						error_log($fp->Title.' --> '.$title);
						$fp->Title = $title;
						$fp->write();
						//error_log($fp->FlickrID . '==' . $photo['id']. '??');
					};

				}


			}
		}
	}


	public function fixSetMainImages() {
		$sets = DataList::create('FlickrSet')->where('PrimaryFlickrPhotoID = 0');
		foreach($sets as $set) {
			error_log("Finding main image for set ".$set->ID.':'.$set->Title."\n");

			$pageCtr = 1;
			$flickrSetID = $set->FlickrID;

			$mainImageFlickrID = null;
			$allPagesRead = false;

			while ( !$allPagesRead ) {

				$photos = $this->f->photosets_getPhotos( $flickrSetID,
					'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description',
					NULL,
					500,
					$pageCtr );

				$pageCtr = $pageCtr+1;



				//print_r($photos);
				$photoset = $photos['photoset'];
				$page = $photoset['page'];
				$pages = $photoset['pages'];
				$allPagesRead = ( $page == $pages );
				error_log( "Fixing page $page of $pages, all read = $allPagesRead" );


				foreach ( $photoset['photo'] as $key => $photo ) {
					echo '.';
					if ($photo['isprimary'] == 1) {
						error_log("\nFound main image of ID ".$photo['id']."\n\n");
						$fp = DataList::create('FlickrPhoto')->where('FlickrID = '.$photo['id'])->first();

						if (isset($fp)) {
							 error_log("FlickrPhoto in SS DB:".$fp->ID);
							$set->PrimaryFlickrPhotoID = $fp->ID;
							$set->write();
						} else {
							error_log("USING FIRST AAVILABLE IMAGE");
							$firstPicID = $set->FlickrPhotos()->first()->ID;
							$set->PrimaryFlickrPhotoID = $firstPicID;
							$set->write();
						}

					}
				}


			}
		}
	}


	/* Fix the many many relationships, previously FlickrSetPhoto pages which have now been deleted */
	public function fixSetPhotoManyMany() {
		$flickrSetID = Convert::raw2sql( $this->request->param( 'ID' ) );
		error_log( "Fixing many to many relationship for set $flickrSetID" );
		$flickrSets = DataList::create( 'FlickrSet' )->where( "FlickrID=".$flickrSetID );

		error_log( "Sets found:".$flickrSets->count() );

		$allPagesRead = false;
		$flickrPhotoIDs = array();


		if ( $flickrSets->count() == 1 ) {
			$flickrSet = $flickrSets->first();
			error_log( "Found set titled ".$flickrSet->Title );
			error_log( "SS ID:".$flickrSet->ID );
			error_log( "Photos prior to resetting: ".$flickrSet->FlickrPhotos()->count() );


			//while ()

			$pageCtr = 1;

			while ( !$allPagesRead ) {

				$photos = $this->f->photosets_getPhotos( $flickrSetID,
					'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description',
					NULL,
					500,
					$pageCtr );

				$pageCtr = $pageCtr+1;



				//print_r($photos);
				$photoset = $photos['photoset'];
				$page = $photoset['page'];
				$pages = $photoset['pages'];
				$allPagesRead = ( $page == $pages );
				error_log( "Fixing page $page of $pages, all read = $allPagesRead" );


				foreach ( $photoset['photo'] as $key => $photo ) {
					array_push( $flickrPhotoIDs, $photo['id'] );
				}


			}

			$flickrPhotos = DataList::create( 'FlickrPhoto' )->where( "FlickrID in (".implode( ',', $flickrPhotoIDs ).")" );
			error_log( "Number of pictures from Flickr:" . $flickrPhotos->count() );
			$flickrSet->FlickrPhotos()->removeAll();
			$flickrSet->FlickrPhotos()->addMany( $flickrPhotos );
			$flickrSet->write();



		} else {
			// no flickr set found for the given ID
			error_log( "Flickr set not found for id ".$flickrSetID );
		}

	}


	function primeBucketsTest() {
		$fset = DataList::create( 'FlickrSet' )->last();
		$bucket = new FlickrBucket();
		$bucket->write();// get an ID
		$photos = $fset->FlickrPhotos();
		error_log( "FLICKR SET:".$fset->ID );
		error_log( "PHOTOS FOUND:".$photos->count() );

		$bucketPhotos = $bucket->FlickrPhotos();
		$ctr = 0;
		foreach ( $photos as $key => $value ) {
			$bucketPhotos->add( $value );
			error_log( "Adding photo ".$value->ID );
			$ctr = $ctr + 1;
			if ( $ctr > 7 ) {
				break;
			}
		}
		$bucket->FlickrSetID = $fset->ID;
		$bucket->write();
	}



	public function createBucket() {

		$flickrPhotoIDs = $this->request->param( 'OtherID' );
		$flickrSetID = Convert::raw2sql( $this->request->param( 'ID' ) );
		$ajax_bucket_row = Convert::raw2sql( $_GET['bucket_row'] );
		error_log( "BUCKET ROW:".$ajax_bucket_row );



		error_log( "PARAMS:".print_r( $this->request->params(), 1 ) );

		$sanitizedIDs = Convert::raw2sql( $flickrPhotoIDs );


		$flickrPhotos = FlickrPhoto::get()->where( 'ID in ('.$sanitizedIDs.')' );
		$flickrSet = FlickrSet::get()->where( 'ID='.$flickrSetID )->first();
		$bucket = new FlickrBucket();

		$bucket->write();

		$bucketPhotos = $bucket->FlickrPhotos();
		foreach ( $flickrPhotos as $fp ) {
			error_log('PIC:'.$fp->Lat);
			$bucketPhotos->add( $fp );
		}
		$bucket->FlickrSetID = $flickrSet->ID;

		$bucket->write();





		error_log( "BUCKET ID:".$bucket->ID );

		error_log( $flickrPhotoIDs );

		$result = array(
			'bucket_id' => $bucket->ID,
			'flickr_set_id' => $flickrSet->ID,
			'ajax_bucket_row' => $ajax_bucket_row

		);

		echo json_encode( $result );

		//echo $bucket->ID;
		die; // abort render

	}





	public function init() {
		parent::init();

		if (!Permission::check("FLICKR_EDIT")) {
			error_log("No permission to edit flickr");
			//FIXME - enable in the CMS first, then do
			//Security::permissionFailure();
		}


		// get flickr details from config
		$key = Config::inst()->get( $this->class, 'api_key' );
		$secret = Config::inst()->get( $this->class, 'secret' );
		$access_token = Config::inst()->get( $this->class, 'access_token' );

		$this->f = new phpFlickr( $key, $secret );

		//Fleakr.auth_token    = ''
		$this->f->setToken( $access_token );


		// Requirements, etc. here
	}

	public function index() {
		// Code for the index action here
		return array();
	}

	public function sets() {
		$sets = $this->f->photosets_getList( '45224965@N04' );

		if ( $sets ) {
			echo "Sets set";
		}


		foreach ( $sets['photoset'] as $key => $value ) {
			echo '#'.$value['title'];
			echo "\nframework/sake flickr/importSet/".$value['id'];
			echo "\n\n";

		}
	}




	public function moveXperiaPics() {
		$moblogbucketsetid = $this->request->param('ID');
		error_log('Searching for flickr set with id '.$moblogbucketsetid);
		//  $moblogset = FlickrSet::get()->filter(array('FlickrID' => $moblogbucketsetid))->first();
		//  error_log('MOBLOG SET:'.print_r($moblogset,1));
		$photos = $this->f->photos_search(array("user_id" => "me", "per_page" => 500, 'extras' => 'description, license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o'));
		foreach ($photos as $photo) {
			error_log('PHOTO');
			error_log(print_r($photo,1));
		}

	}


	public function changeFlickrSetMainImage() {
		$flickrsetID = $this->request->param('ID');
		$flickrphotoID = $this->request->param('OtherID');
		error_log('Set,photo id = '.$flickrsetID.','.$flickrphotoID);
		$flickrset = FlickrSet::get()->filter('ID', $flickrsetID)->first();
		$flickrset->PrimaryFlickrPhotoID = $flickrphotoID;
		$flickrset->write();
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


		while ( !$completed ) {
			// code...

			echo "GETING PAGE ".$page;

			$photo_response = $this->f->photosets_getPhotos( $flickrSetID, 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description', NULL, NULL, $page );

			$page++;




			$photos = $photo_response['photoset']['photo'];
			$completed = ( count( $photos ) != 500 );


			echo "COUNT:".count( $photos );
			echo "COMPLETED?:".$completed;

			foreach ( $photos as $key => $photo ) {
				$title = $photo['title'];
				$takenAt = $photo['datetaken'];
				$dateParts = split( ' ', $takenAt );
				$date = $dateParts[0];

				if ( !isset( $dateToImages[$date] ) ) {
					$dateToImages[$date] = array();
				}

				array_push( $dateToImages[$date], $photo );



				echo $date." :: ".$title;
				echo "\n";
			}
		}


		echo "************ DONE";

		foreach ( $dateToImages as $date => $photosForDate ) {
			echo "DATE:".$date."\n";
			$firstPic = $dateToImages[$date][0]['id'];
			error_log( "FIRST PIC:".$firstPic );

			$set = $this->f->photosets_create( 'Moblog '.$date, 'Mobile photos for '.$date, $firstPic );
			var_dump( $set );

			$setId = $set['id'];


			//      function photosets_addPhoto ($photoset_id, $photo_id) {


			foreach ( $dateToImages[$date] as $key => $image ) {
				echo "\t".$image['title']."\n";
				$this->f->photosets_addPhoto( $setId, $image['id'] );
				//      function photos_addTags ($photo_id, $tags) {

				$this->f->photos_addTags( $image['id'], "moblog iphone3g" );
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


		$canAccess = ( Director::isDev() || Director::is_cli() || Permission::check( "ADMIN" ) );
		if ( !$canAccess ) return Security::permissionFailure( $this );

		// Code for the register action here
		error_log( "import set" );
		$flickrSetID = $this->request->param( 'ID' );
		$path = $_GET['path'];
		error_log("PATH:".$path);
		$parentNode = SiteTree::get_by_link($path);
		error_log("PARENT NODE:".$parentNode);

		//error_log("ID PARAM:".print_r($flickrSetID, 1));

		$this->FlickrSetId = $flickrSetID;

		$photos = $this->f->photosets_getPhotos( $flickrSetID, 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o, url_l,description' );
		$photoset = $photos['photoset'];

		echo count( $photoset );

		foreach ( $photoset as $key => $value ) {
			error_log( "K-V: ".$key.' --> '.$value );
		}


		//var_dump($photoset);


		// error_log("PHOTOSET:".print_r($photoset, 1));
		// $this->photoDebug = print_r($photoset, 1);



		$flickrSet = $this->getFlickrSet( $flickrSetID );




		error_log( "Searching for flickr set with flickr ID *".$flickrSetID."*" );

		// reload from DB with date - note the use of quotes as flickr set id is a string
		$flickrSet = DataObject::get_one( 'FlickrSet', 'FlickrID=\''.$flickrSetID."'" );
		$flickrSet->FirstPictureTakenAt = $photoset['photo'][0]['datetaken'];
		$flickrSet->KeepClean = true;
		$flickrSet->Title = $photoset['title'];
		$flickrSet->write();

		echo "Title set to : ".$flickrSet->Title;


		error_log( "AFTER RELOAD FS = ".$flickrSet." (".$flickrSet->ID.")" );

		if ( $flickrSet->Title == null ) {
			error_log( "ABORTING DUE TO NULL TITLE FOUND IN SET" );
			die;
		}

		error_log( "DATE:".print_r( $flickrSet->FirstPictureTakenAt, 1 ) );

		$datetime = split( ' ', $flickrSet->FirstPictureTakenAt );
		$datetime = $datetime[0];
		error_log( "DT:".$datetime );
		list( $year, $month, $day ) = split( '[/.-]', $datetime );
		echo "Month: $month; Day: $day; Year: $year<br />\n";



		// now try and find a flickr set page
		$flickrSetPage = DataObject::get_one( 'FlickrSetPage', 'FlickrSetForPageID='.$flickrSet->ID );
		if ( !$flickrSetPage ) {
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
		$flickrSetPage->publish( "Live", "Stage" );


		error_log("Updated FSP ".$flickrSetPage->ID);
		error_log("PARENT NOW ".$parentNode->ID);


		$flickrSetPageID = $flickrSetPage->ID;
		gc_enable();




		$f1 = Folder::find_or_make( "flickr/$year" );
		$f1->Title = $year;
		$f1->write();



		$f1 = Folder::find_or_make( "flickr/$year/$month" );
		$f1->Title = $month;
		$f1->write();

		$f1 = Folder::find_or_make( "flickr/$year/$month/$day" );
		$f1->Title = $day;
		$f1->write();

		exec( "chmod 775 ../assets/flickr/$year" );
		exec( "chmod 775 ../assets/flickr/$year/$month" );
		exec( "chmod 775 ../assets/flickr/$year/$month/$day" );
		exec( "chown gordon:www-data ../assets/flickr/$year" );;
		exec( "chown gordon:www-data ../assets/flickr/$year/$month" );;
		exec( "chown gordon:www-data ../assets/flickr/$year/$month/$day" );;


		$folder = Folder::find_or_make( "flickr/$year/$month/$day/" . $flickrSetID );

		$cmd = "chown gordon:www-data ../assets/flickr";
		exec( $cmd );

		exec( 'chmod 775 ../assets/flickr' );

		error_log( "FOLDER - find or make:".$folder->ID );



		// new folder case
		if ( $flickrSet->AssetFolderID == 0 ) {
			error_log( "Setting flickr set asset folder id" );
			$flickrSet->AssetFolderID = $folder->ID;
			$folder->Title = $flickrSet->Title;
			$folder->write();

			error_log( "FOLDER ID:".$folder->ID );

			error_log( "Written folder" );



			$cmd = "chown gordon:www-data ../assets/flickr/$year/$month/$day/".$flickrSetID;
			error_log( "CMD:".$cmd );
			exec( $cmd );

			$cmd = "chmod 775 ../assets/flickr/$year/$month/$day/".$flickrSetID;
			error_log( "CMD:.$cmd" );
			exec( $cmd );


		}

		$flickrSetAssetFolderID = $flickrSet->AssetFolderID;

		$flickrSetPageDatabaseID = $flickrSetPage->ID;


		//$flickrSet = NULL;
		$flickrSetPage = NULL;

		$numberOfPics = count($photoset['photo']);

		foreach ( $photoset['photo'] as $key => $value ) {

			gc_collect_cycles();


			error_log( "\n\n\n====".$key."/".$numberOfPics.":".$value['title'] );
			error_log( "MEMORY:".memory_get_usage( true ) );

			error_log( print_r( $value, 1 ) );

			$flickrPhotoID = $value['id'];

			// do we have a set object or not
			$flickrPhoto = DataObject::get_one( 'FlickrPhoto', 'FlickrID='.$flickrPhotoID );

			// if a set exists update data, otherwise create
			if ( !$flickrPhoto ) {
				$flickrPhoto = new FlickrPhoto();
			}

			// if we are in the mode of only importing new then skip to the next iteration if this pic already exists
			else if ( $only_new_photos ) {
					error_log( "\tPhoto already imported - skipping" );
					continue;
				}
			error_log( "Importing pic" );

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

			$flickrPhoto->Description = 'test';// $value['description']['_content'];




			$lat = number_format( $value['latitude'], 15 );
			$lon = number_format( $value['longitude'], 15 );

			error_log( "LAT:".$lat );
			error_log( "LON:".$lon );


			if ( $value['latitude'] ) {
				$flickrPhoto->Lat = $lat;
				$flickrPhoto->ZoomLevel = 15;
			}
			if ( $value['longitude'] ) {
				$flickrPhoto->Lon = $lon;
			}

			if ( $value['accuracy'] ) {
				$flickrPhoto->Accuracy = $value['accuracy'];
			}

			if ( isset( $value['geo_is_public'] ) ) {
				$flickrPhoto->GeoIsPublic = $value['geo_is_public'];
			}

			if ( isset( $value['woeid'] ) ) {
				$flickrPhoto->WoeID = $value['woeid'];
			}

			error_log( "GETTING FLICKR PHOTO INFO" );


			$singlePhotoInfo = $this->f->photos_getInfo( $flickrPhotoID );

			 // error_log(print_r($singlePhotoInfo, 1));
			 // die;
			$flickrPhoto->Description = $singlePhotoInfo['photo']['description']['_content'];
			$flickrPhoto->TakenAt = $singlePhotoInfo['photo']['dates']['taken'];
			$flickrPhoto->Rotation = $singlePhotoInfo['photo']['rotation'];

			if ( isset( $singlePhotoInfo['photo']['visibility'] ) ) {
				$flickrPhoto->IsPublic = $singlePhotoInfo['photo']['visibility']['ispublic'];
			}

			$flickrPhoto->write();


			if ($value['isprimary'] == 1) {
				$flickrSet->MainImage = $flickrPhoto;
			}


			$photoTagIDs = array();

			error_log( "PRE TAGS FLICKR PHOTO ID:".$flickrPhoto->ID );


			foreach ( $singlePhotoInfo['photo']['tags']['tag'] as $key => $taginfo ) {

				error_log( "Checking tag ".$taginfo['_content'] );
				//  $tag = DataObject::get_one('Tag', 'Value = \''.$taginfo['_content']+'\'');

				$tag = DataObject::get_one( 'FlickrTag', "\"Value\"='".$taginfo['_content']."'" );

				if ( !$tag ) {
					$tag = new FlickrTag();
				}


				$tag->FlickrID = $taginfo['id'];
				$tag->Value = $taginfo['_content'];
				$tag->RawValue = $taginfo['raw'];


				$tag->write();


				error_log( "TA, PHOTO IDS:".$tag->ID.", ".$flickrPhoto->ID );


				error_log( "EXPLODED TAG" );

				$ftags= $flickrPhoto->FlickrTags();
				$ftags->add( $tag );

				error_log( "Added tag ".$tag->ID." to pics ".$flickrPhoto->ID );
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
			$flickrSet->FlickrPhotos()->add( $flickrPhoto );
			gc_collect_cycles();


			$flickrPhoto->write();
			gc_collect_cycles();



			if ( !$flickrPhoto->LocalCopyOfImage ) {


				//mkdir appears to be relative to teh sapphire dir
				$structure = "../assets/flickr/$year/$month/$day/".$flickrSetID;

				if ( !file_exists( '../assets/flickr' ) ) {
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
					$galleries = Folder::find_or_make( 'flickr' );
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








				$download_images = Config::inst()->get( $this->class, 'download_images' );
				error_log( "DOWNLOAD IMAGES? ".$download_images );

				if ( $download_images && !( $flickrPhoto->LocalCopyOfImageID ) ) {
					error_log( "No local copy - downloading from flickr" );
					error_log( "MEM IMAGE T1:".memory_get_usage( true ) );
					$largeURL = $flickrPhoto->LargeURL;
					$fpid = $flickrPhoto->FlickrID;
					error_log( "MEM IMAGE T2:".memory_get_usage( true ) );

					$cmd = "wget -O $structure/$fpid.jpg $largeURL";
					exec( $cmd );

					error_log( "MEM IMAGE T3:".memory_get_usage( true ) );


					$cmd = "chown  gordon:www-data $structure/$fpid.jpg";
					// $cmd = "pwd";
					error_log( "COMMAND:".$cmd );

					echo "EXECCED:".exec( $cmd );

					error_log( "MEM IMAGE T4:".memory_get_usage( true ) );

					$image = new Image();
					$image->Name = $this->Title;
					$image->Title = $this->Title;
					$image->Filename = str_replace( '../', '', $structure.'/'.$fpid.".jpg" );
					error_log( "Setting title of image to ".$flickrPhoto->Title );
					$image->Title = $flickrPhoto->Title;
					//$image->Name = $flickrPhoto->Title;
					$image->ParentID = $flickrSetAssetFolderID;
					error_log( "MEM IMAGE T5:".memory_get_usage( true ) );
					gc_collect_cycles();

					$image->write();
					gc_collect_cycles();

					error_log( "MEM IMAGE T6:".memory_get_usage( true ) );


					error_log( "Setting image parent id to "+$flickrSetAssetFolderID );


					$flickrPhoto->LocalCopyOfImageID = $image->ID;
					error_log( "MEM IMAGE T7:".memory_get_usage( true ) );

					$flickrPhoto->write();
					error_log( "MEM IMAGE T8:".memory_get_usage( true ) );

					$image = NULL;
					error_log( "MEM IMAGE T9:".memory_get_usage( true ) );

				}



				$result = $flickrPhoto->write();

				error_log("WRITE? ".$result);



			}





			// do we have a page, if not create one.  Then populate it with the photo


			error_log( "MEM T10:".memory_get_usage( true ) );


			error_log( "MEM T11:".memory_get_usage( true ) );



			$flickrPhoto = NULL;

			error_log( "MEM T14:".memory_get_usage( true ) );


		}

		 //update orientation
		error_log( "Updating orientations" );
		$sql = 'update FlickrPhoto set Orientation = 90 where ThumbnailHeight > ThumbnailWidth;';
		DB::query( $sql );


		// now download exifs
		error_log("START EDITING - NOW DOING EXIF");
		foreach ( $photoset['photo'] as $key => $value ) {
			$flickrPhotoID = $value['id'];
			error_log("Adding EXIF for photo $flickrPhotoID");
			$flickrPhoto = FlickrPhoto::get()->filter('FlickrID',$flickrPhotoID)->first();
			error_log("FP:".$flickrPhotoID);
			$flickrPhoto->loadExif();
			$flickrPhoto->write();
		}



		//error_log(print_r($photos,1));

		$this->fixSetMainImages();
		$this->fixDateSetTaken();


		error_log( "Abort render" );
		die(); // abort rendering


		return array();
	}


	/*
	Either get the set from the database, or if it does not exist get the details from flickr and add it to the database
	*/
	private function getFlickrSet( $flickrSetID ) {
		// do we have a set object or not
		$flickrSet = DataObject::get_one( 'FlickrSet', 'FlickrID=\''.$flickrSetID."'" );

		// if a set exists update data, otherwise create
		if ( !$flickrSet ) {
			error_log( "Creating new flickr set:".$flickrSetID );
			$flickrSet = new FlickrSet();
			$setInfo = $this->f->photosets_getInfo( $flickrSetID );

			$setTitle = $setInfo['title'];
			$setDescription = $setInfo['description'];
			$flickrSet->Title = $setTitle;
			$flickrSet->Description = $setDescription;
			$flickrSet->FlickrID = $flickrSetID;
			$flickrSet->KeepClean = true;
			$flickrSet->write();
			error_log( "Setting first pic date to ".$flickrSet->FirstPictureTakenAt );
		} else {
			error_log( "Reusing existing set" );
		}

		return $flickrSet;
	}


	public function editprofile() {
		// Code for the editprofile action here
		return array();
	}

}
