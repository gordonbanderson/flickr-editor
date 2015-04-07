<?php

require_once "phpFlickr.php";

/**
 * Only show a page with login when not logged in
 */
class FlickrPhoto extends DataObject {


  static $searchable_fields = array(
    'Title',
    'Description',
    'FlickrID'
  );





  static $db = array(
    'Title' => 'Varchar(255)',
    'FlickrID' => 'Varchar',
    'Description' => 'HTMLText',
    'TakenAt' => 'Datetime',
    'FlickrLastUpdated' => 'Date',
    'GeoIsPublic' => 'Boolean',

    // flag to indicate requiring a flickr API update
    'IsDirty' => 'Boolean',

    'Orientation' => 'Int',
    'WoeID' => 'Int',
    'Accuracy' => 'Int',
    'FlickrPlaceID' => 'Varchar(255)',
    'Rotation' => 'Int',
    'IsPublic' => 'Boolean',
    'Aperture' => 'Float',
    'ShutterSpeed' => 'VarChar',
    'ImageUniqueID' => 'Varchar',
    'FocalLength35mm' => 'Int',
    'ISO' => 'Int',

    'SmallURL' => 'Varchar(255)',
    'SmallHeight' => 'Int',
    'SmallWidth' => 'Int',

    'MediumURL' => 'Varchar(255)',
    'MediumHeight' => 'Int',
    'MediumWidth' => 'Int',

    'SquareURL' => 'Varchar(255)',
    'SquareHeight' => 'Int',
    'SquareWidth' => 'Int',

    'LargeURL' => 'Varchar(255)',
    'LargeHeight' => 'Int',
    'LargeWidth' => 'Int',

    'ThumbnailURL' => 'Varchar(255)',
    'ThumbnailHeight' => 'Int',
    'ThumbnailWidth' => 'Int',

    'OriginalURL' => 'Varchar(255)',
    'OriginalHeight' => 'Int',
    'OriginalWidth' => 'Int',
    'TimeShiftHours' => 'Int',
    'PromoteToHomePage' => 'Boolean'
    //TODO - place id
  );



  static $belongs_many_many = array(
    'FlickrSets' => 'FlickrSet'
  );


  // this one is what created the database FlickrPhoto_FlickrTagss
  static $many_many = array(
    'FlickrTags' => 'FlickrTag',
    'FlickrBuckets' => 'FlickrBucket'
  );


  static $has_many = array(
    'Exifs' => 'FlickrExif'
  );


  static $has_one = array(
    'LocalCopyOfImage' => 'Image'
  );


  public static $summary_fields = array(
    'Thumbnail' => 'Thumbnail',
    'Title' => 'Title',
    'TakenAt' => 'TakenAt'
  );




  static $sphinx = array(
    "search_fields" => array( "Title", "Description", 'FocalLength35mm', 'Aperture', 'ISO', 'ShutterSpeed' ),
    "filter_fields" => array(),
    "index_filter" => '"ID" != 0',
    "sort_fields" => array( "Title" )

  );

  // -- helper methods to ensure that URLs are of the form //path/to/image so that http and https work with console warnings
  public function ProtocolAgnosticLargeURL() {
    return $this->stripProtocol($this->LargeURL);
  }

  public function ProtocolAgnosticSmallURL() {
    return $this->stripProtocol($this->SmallURL);
  }


  public function ProtocolAgnosticMediumURL() {
    return $this->stripProtocol($this->MediumURL);
  }

  public function ProtocolAgnosticThumbnailURL() {
    return $this->stripProtocol($this->ThumbnailURL);
  }

  public function ProtocolAgnosticOriginalURL() {
    return $this->stripProtocol($this->OriginalURL);
  }



  private function stripProtocol($url) {
    $url = str_replace('http:', '', $url);
    $url = str_replace('https:', '', $url);
    return $url;
  }



  // thumbnail related

  function HorizontalMargin( $intendedWidth ) {
    //FIXME - is there a way to avoid a database call here?
    $fp = DataObject::get_by_id( 'FlickrPhoto', $this->ID );
    return ( $intendedWidth-$fp->ThumbnailWidth )/2;
  }


  function InfoWindow() {
    return GoogleMapUtil::sanitize( $this->renderWith( 'FlickrPhotoInfoWindow' ) );
  }


  function VerticalMargin( $intendedHeight ) {
    //FIXME - is there a way to avoid a database call here?
    $fp = DataObject::get_by_id( 'FlickrPhoto', $this->ID );
    return ( $intendedHeight-$fp->ThumbnailHeight )/2;
  }


  /*
    Mark image as dirty upon a save
    */
  function onBeforeWrite() {
    parent::onBeforeWrite();

    error_log("APRENT ON BEFORE WRITE FP ***********");

    $quickTags = FlickrTag::CreateOrFindTags($this->QuickTags);
    $this->FlickrTags()->addMany($quickTags);

    if (!$this->KeepClean) {
      $this->IsDirty = true;
    } else {
      $this->IsDirty = false;
    }
  }





  function getCMSFields() {

    Requirements::css( FLICKR_EDIT_TOOLS_PATH . '/css/flickredit.js' );

    $fields = new FieldList();


    $fields->push( new TabSet( "Root", $mainTab = new Tab( "Main" ) ) );
    $mainTab->setTitle( _t( 'SiteTree.TABMAIN', "Main" ) );


    $forTemplate = new ArrayData( array(
        'FlickrPhoto' => $this
      ) );
    $imageHtml = $forTemplate->renderWith( 'FlickrImageEditing' );


    $lfImage = new LiteralField( 'FlickrImage', $imageHtml );
    $fields->addFieldToTab( 'Root.Main', $lfImage );
    $fields->addFieldToTab( 'Root.Main',  new TextField( 'Title', 'Title') );
        $fields->addFieldToTab( 'Root.Main', new TextAreaField( 'Description', 'Description' )  );

    // only show a map for editing if no sets have geolock on them
    $lockgeo = false;
    foreach ($this->FlickrSets() as $set) {
      error_log("++++ CHECKING SET ".$set." for lock geo, has ".$set->LockGeo);
      if ($set->LockGeo == true) {
        $lockgeo = true;
        break;
      }
    }

    if (!$lockgeo) {
       $fields->addFieldToTab( "Root.Location", new LatLongField( array(
          new TextField( 'Lat', 'Latitude' ),
          new TextField( 'Lon', 'Longitude' ),
          new TextField( 'ZoomLevel', 'Zoom' )
        ),
          array( 'Address' )
          )
       );
    }

    // quick tags, faster than the grid editor - these are processed prior to save to create/assign tags
    $fields->addFieldToTab( 'Root.Main',  new TextField( 'QuickTags', 'Enter tags here separated by commas') );



    $gridConfig = GridFieldConfig_RelationEditor::create();//->addComponent( new GridFieldSortableRows( 'Value' ) );
    $gridConfig->getComponentByType( 'GridFieldAddExistingAutocompleter' )->setSearchFields( array( 'Value','RawValue' ) );
    $gridField = new GridField( "Tags", "List of Tags", $this->FlickrTags(), $gridConfig );
    $fields->addFieldToTab( "Root.Main", $gridField );

    $fields->addFieldToTab("Root.Main", new CheckboxField('PromoteToHomePage', 'Promote to Home Page'));
    return $fields;
  }




  public function AdjustedTime() {
    error_log( "ADJUSTED TIME:".$this->TimeShiftHours );
    return 'FP ADJ TIME '.$this->TimeShiftHours;
  }


  public function getThumbnail() {
    return DBField::create_field( 'HTMLVarchar',
      '<img class="flickrThumbnail" data-flickr-medium-url="'.$this->MediumURL.'" src="'.$this->ThumbnailURL.'"  data-flickr-thumbnail-url="'.$this->ThumbnailURL.'"/>' );
  }



  private function initialiseFlickr() {
    if (!isset($this->f)) {
              // get flickr details from config
        $key = Config::inst()->get( 'FlickrController', 'api_key' );
        $secret = Config::inst()->get('FlickrController', 'secret' );
        $access_token = Config::inst()->get( 'FlickrController', 'access_token' );

        $this->f = new phpFlickr( $key, $secret );

        //Fleakr.auth_token    = ''
        $this->f->setToken( $access_token );
    }
  }

  public function HasGeo() {
    return $this->Lat != 0 || $this->Lon != 0;
  }


  public function loadExif() {
    error_log( "Loading EXIF data" );
    $this->initialiseFlickr();
    error_log('Getting exif from flickr');
    $exifData = $this->f->photos_getExif( $this->FlickrID );
    error_log('/getting exif from flickr');
    //error_log(print_r($exifData,1));

    // delete the old exif data
    $sql = "DELETE from FlickrExif where FlickrPhotoID=".$this->ID;
    error_log( $sql );
    DB::query( $sql );

    echo "Storing exif data";
    foreach ( $exifData['exif'] as $key => $exifInfo ) {
      DB::query('begin;');
        $exif = new FlickrExif();
        $exif->TagSpace = $exifInfo['tagspace'];
        $exif->TagSpaceID = $exifInfo['tagspaceid'];
        $exif->Tag = $exifInfo['tag'];
        $exif->Label = $exifInfo['label'];
        $exif->Raw = $exifInfo['raw']['_content'];
        $exif->FlickrPhotoID = $this->ID;
        $exif->write();

        if ( $exif->Tag == 'ImageUniqueID' ) {
            $this->ImageUniqueID = $exif->Raw;
        } else
            if ( $exif->Tag == 'ISO' ) {
                $this->ISO = $exif->Raw;
            } else
            if ( $exif->Tag == 'ExposureTime' ) {
                $this->ShutterSpeed = $exif->Raw;
            } else
            if ( $exif->Tag == 'FocalLengthIn35mmFormat' ) {
                $raw35 = $exif->Raw;
                error_log( "RAW 35:".$raw35 );
                $fl35 = str_replace( ' mm', '', $raw35 );

                error_log( "POST MANGLING 1: ".$fl35 );

                $fl35 = (int) $fl35;

                error_log( "POST MANGLING 2: ".$fl35 );
                $this->FocalLength35mm = $fl35;
            } else
            if ( $exif->Tag == 'FNumber' ) {
                $this->Aperture = $exif->Raw;
            };

        $exif = NULL;
        gc_collect_cycles();


    }
    echo "/storing exif";
    DB::query('commit;');
  }


  /*
  Update Flickr with details held in SilverStripe
  @param $descriptionSuffix The suffix to be appended to the photographic description
  */
  public function writeToFlickr($descriptionSuffix) {
    $this->initialiseFlickr();
    error_log("Updated flickr photo ".$this->FlickrID);

    $fullDesc = $this->Description."\n\n".$descriptionSuffix;
    $fullDesc = trim($fullDesc);

    $year = substr($this->TakenAt,0,4);
    $fullDesc = str_replace('$Year', $year, $fullDesc);
    $this->f->photos_setMeta($this->FlickrID, $this->Title, $fullDesc);

    $tagString = '';
    foreach ($this->FlickrTags() as $tag) {
      $tagString .= '"'.$tag->Value.'" ';
    }

    error_log("Setting tags:".$tagString);

    $this->f->photos_setTags($this->FlickrID, $tagString);

    if ($this->HasGeo()) {
      error_log("Updating map coordinates");
      $this->f->photos_geo_setLocation ($this->FlickrID, $this->getMappableLatitude(), $this->getMappableLongitude());
    }

    $this->KeepClean = true;
    $this->write();

      //function photos_setMeta ($photo_id, $title, $description)
      //                $this->f->photos_addTags( $image['id'], "moblog iphone3g" );

  }


}
