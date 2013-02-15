<?php

require_once "phpFlickr.php";

/**
 * Only show a page with login when not logged in
 */
class FlickrPhoto extends DataObject implements Mappable {


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

    // use precision 15 and 10 decimal places for coordiantes
    'Lat' => 'Decimal(18,15)',
    'Lon' => 'Decimal(18,15)',
    'Accuracy' => 'Int',


    'Orientation' => 'Int',
    'ZoomLevel' => 'Int',
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



  // thumbnail related

  function HorizontalMargin( $intendedWidth ) {
    //FIXME - is there a way to avoid a database call here?
    error_log( "HORIZONTAL" );
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
   

    $gridConfig = GridFieldConfig_RelationEditor::create();//->addComponent( new GridFieldSortableRows( 'Value' ) );
    $gridConfig->getComponentByType( 'GridFieldAddExistingAutocompleter' )->setSearchFields( array( 'Value','RawValue' ) );
    $gridField = new GridField( "Tags", "List of Tags", $this->FlickrTags(), $gridConfig );
    $fields->addFieldToTab( "Root.Tags", $gridField );

    $fields->addFieldToTab("Root.HomePage", new CheckboxField('PromoteToHomePage', 'Promote to Home Page'));
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



  public function getMappableLatitude() {
    return $this->Lat;
  }

  public function getMappableLongitude() {
    return $this->Lon;
  }

  public function getMapContent() {

    return 'wip';
    //return GoogleMapUtil::sanitize($this->renderWith('MapBubbleMember'));
  }
  public function getMapCategory() {
    return 'photo';
  }

  public function getMapPin() {
    return false; //standard pin
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

?>