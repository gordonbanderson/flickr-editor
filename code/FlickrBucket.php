<?php

class FlickrBucket extends DataObject {


  static $db = array(
    'Title' => 'Varchar',
    'Description' => 'Text',
    // use precision 15 and 10 decimal places for coordiantes
    'Lat' => 'Decimal(18,15)',
    'Lon' => 'Decimal(18,15)',
    'Accuracy' => 'Int',
    'ZoomLevel' => 'Int',
    'TagsCSV' => 'Varchar'
  );


  static $has_one = array (
    'FlickrSet' => 'FlickrSet'
  );

  public static $summary_fields = array(
    'Title',
    'ImageStrip' => 'ImageStrip'
  );





  static $belongs_many_many = array(
    'FlickrPhotos' => 'FlickrPhoto',
    'FlickrTags' => 'FlickrTag'
  );


  static $many_many = array(
    'FlickrTags' => 'FlickrTag'
  );





  function getCMSFields() {
    $fields = new FieldList();

    $fields->push( new TabSet( "Root", $mainTab = new Tab( "Main" ) ) );
    $mainTab->setTitle( _t( 'SiteTree.TABMAIN', "Main" ) );

    $lf = new LiteralField( '<p>Instructions', 'All of the images in this bucket will have the same information that you enter here</p>' );
    $fields->push( $lf );

    $fields->addFieldToTab( 'Root.Main', $lf );
    $fields->addFieldToTab( 'Root.Main',  new TextField( 'Title', 'Bucket Title' ) );
    $fields->addFieldToTab( 'Root.Main', new TextAreaField( 'Description', 'Bucket Description' )  );

     // quick tags, faster than the grid editor - these are processed prior to save to create/assign tags
    $fields->addFieldToTab( 'Root.Main',  new TextField( 'QuickTags', 'Quick tags - enter tags here separated by commas') );




    $lf2 = new LiteralField( 'ImageStrip', $this->getImageStrip() );
    $fields->push( $lf2 );


    $lockgeo = $this->GeoLocked();

    if ( !$lockgeo ) {
      error_log( "Adding location tab as lock geo is ".$lockgeo );
      $mapField = new LatLongField( array(
            new TextField( 'Lat', 'Latitude' ),
            new TextField( 'Lon', 'Longitude' ),
            new TextField( 'ZoomLevel', 'Zoom' )
          ),
          array( 'Address' )
        );

      $guidePoints = array();
      foreach ($this->FlickrSet()->FlickrPhotos()->where('Lat != 0 and Lon != 0') as $fp) {
        if (($fp->Lat != 0) && ($fp->Lon != 0)) {
          error_log("Set lat lon");
          array_push($guidePoints, array('latitude' => $fp->Lat, 'longitude' => $fp->Lon));
        }
      }

      if (count($guidePoints) > 0) {
        $mapField->setGuidePoints($guidePoints);
      }

      $locationTab = $fields->findOrMakeTab('Root.Location');
      $locationTab->extraClass('mappableLocationTab');

      $fields->addFieldToTab( 'Root.Location', $mapField );
    }


    error_log(print_r($fields,1));


    $gridConfig = GridFieldConfig_RelationEditor::create();//->addComponent( new GridFieldSortableRows( 'Value' ) );
    $gridConfig->getComponentByType( 'GridFieldAddExistingAutocompleter' )->setSearchFields( array( 'Value','RawValue' ) );
    $gridField = new GridField( "Tags", "List of Tags", $this->FlickrTags(), $gridConfig );

    // keep in the main tab to avoid wasting time tab switching
    $fields->addFieldToTab( "Root.Main", $gridField );





    return $fields;
  }


  function GeoLocked() {
    // only show a map for editing if no sets have geolock on them
    $lockgeo = false;
    foreach ( $this->FlickrPhotos() as $fp ) {
      foreach ( $fp->FlickrSets() as $set ) {

        if ( $set->LockGeo ) {
          $lockgeo = true;
          break;
        }
      }

      if ( $lockgeo ) {
        break;
      }
    }

    return $lockgeo;
  }


  public function getImageStrip() {
    $html = '<div class="imageStrip">';
    foreach ( $this->FlickrPhotos() as $key => $photo ) {
      $html = $html . '<img class="flickrThumbnail" ';
      $html .= 'src="'.$photo->ThumbnailURL.'" ';
      $html .= 'data-flickr-thumbnail-url="'.$photo->ThumbnailURL.'" ';
      $html .= 'data-flickr-medium-url="'.$photo->MediumURL.'"/>';
    }
    $html = $html . "</div>";
    return DBField::create_field( 'HTMLText',  $html );
  }


  public function onBeforeWrite() {
    parent::onBeforeWrite();


    error_log( "OBW: ID = ".$this->ID );
    error_log("QUICK TAGS:".$this->QuickTags);
    $quickTags = FlickrTag::CreateOrFindTags($this->QuickTags);
    $this->FlickrTags()->addMany($quickTags);

    if ( $this->ID && ( $this->FlickrPhotos()->count() > 0 ) ) {
      error_log( "TESTING TITLE" );
      if ( $this->Title == '' ) {
        error_log( "Blank title nFP=".$this->FlickrPhotos()->count() );
        $this->Title = $this->FlickrPhotos()->first()->TakenAt.' - '.$this->FlickrPhotos()->last()->TakenAt;
      }
    } else {
      error_log( "SKIPPING TITLE TWEAK" );
      $this->Virginal = true;
    }



  }


  /*
    Update all the photographs in the bucket with the details of the bucket
    */
  public function onAfterWrite() {
    parent::onAfterWrite();

    error_log( "+++ POST BUCKET SAVE ++++" );
    error_log("QUICK TAGS:".$this->QuickTags);

   

    // if the title is blank resave in order to create a time from / time to title
    // this needs checked here as on before write cannot do this when the bucket has not been saved
    if ( $this->Title == '' && !isset( $this->Virginal ) ) {
      $this->write();
    }

    $lockgeo = $this->GeoLocked();

    error_log("BUCKET GEOLOCKED? ".$this->GeoLocked);
    error_log("COORS: ".$this->Lat.",".$this->Lon);
    foreach ( $this->FlickrPhotos() as $fp ) {
      $fp->Title = $this->Title;
      $description = $this->Description;
      //$description = $description ."\n\n".$this->FlickrSet()->ImageFooter;
      //$description = $description ."\n\n".Controller::curr()->SiteConfig()->ImageFooter;
      error_log("TAKEN AT:".$fp->TakenAt);
      $year =substr(''.$fp->TakenAt,0,4);
      error_log("YEAR:".$year);
      $description = str_replace('$Year', $year, $description);
      $fp->Description = $description;
      error_log("DESCRIPTION:".$description);

      if ( !$lockgeo ) {
        error_log("Setting photo lat to ".$this->Lat);
          $fp->Lat = $this->Lat;
          $fp->Lon = $this->Lon;
          error_log("Updated flickr pic coords ".$fp->ID);
      }

      $fp->FlickrTags()->addMany($this->FlickrTags());

      error_log("BUCKET TAGS:".$this->FlickrTags()->count());
      $fp->write();
      error_log("AFTER WRITE FP TAGS:".$fp->FlickrTags()->count());
    }
  }

}


?>