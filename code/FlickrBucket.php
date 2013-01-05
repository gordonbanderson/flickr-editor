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
    'FlickrPhotos' => 'FlickrPhoto'
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



    $lf2 = new LiteralField( 'ImageStrip', $this->getImageStrip() );
    $fields->push( $lf2 );


    $lockgeo = $this->GeoLocked();

    if ( !$lockgeo ) {
      error_log( "Adding location tab as lock geo is ".$lockgeo );
      $fields->addFieldToTab( "Root.Location", new LatLongField( array(
            new TextField( 'Lat', 'Latitude' ),
            new TextField( 'Lon', 'Longitude' ),
            new TextField( 'ZoomLevel', 'Zoom' )
          ),
          array( 'Address' )
        ) );
    }




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
      $description = $description ."\n\n".$this->FlickrSet()->ImageFooter;
      $description = $description ."\n\n".Controller::curr()->SiteConfig()->ImageFooter;
      error_log("TAKEN AT:".$fp->TakenAt);
      $year =substr(''.$fp->TakenAt,0,4);
      error_log("YEAR:".$year);
      $description = str_replace('$Year', $year, $description);
      $fp->Description = $description;
      error_log("DESCRIPTION:".$description);

      if ( !$lockgeo ) {
          $fp->Lat = $this->Lat;
          $fp->Lon = $this->Lon;
          error_log("Updated flickr pic coords ".$fp->ID);
      }

      $fp->write();
    }
  }

}


?>