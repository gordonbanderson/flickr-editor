<?php

class FlickrBucket extends DataObject {  


  static $db = array(
    'Title' => 'Varchar',
    'Description' => 'Text',
        // use precision 15 and 10 decimal places for coordiantes
    'Lat' => 'Decimal(18,15)',
    'Lon' => 'Decimal(18,15)',
    'Accuracy' => 'Int',
    'ZoomLevel' => 'Int'
  );


  static $has_one = array (
    'FlickrSet' => 'FlickrSet'
  );

  public static $summary_fields = array(
    'ImageStrip' => 'ImageStrip'
  );


  


   static $belongs_many_many = array(
      'FlickrPhotos' => 'FlickrPhoto'
   );





   function getCMSFields() {
        $fields = new FieldList();

        $fields->push( new TabSet( "Root", $mainTab = new Tab( "Main" ) ) );
        $mainTab->setTitle( _t( 'SiteTree.TABMAIN', "Main" ) );

        $lf = new LiteralField('<p>Instructions', 'All of the images in this bucket will have the same information that you enter here</p>');
        $fields->push($lf);

        $fields->addFieldToTab( 'Root.Main', $lf );
        $fields->addFieldToTab( 'Root.Main',  new TextField( 'Title', 'Bucket Title') );
        $fields->addFieldToTab( 'Root.Main', new TextAreaField( 'Description', 'Bucket Description' )  );

      

        $lf2 = new LiteralField('ImageStrip', $this->getImageStrip());
        $fields->push($lf2);


         $fields->addFieldToTab( "Root.Location", new LatLongField( array(
          new TextField( 'Lat', 'Latitude' ),
          new TextField( 'Lon', 'Longitude' ),
          new TextField( 'ZoomLevel', 'Zoom' )
        ),
        array( 'Address' )
      ) );
         
        return $fields;
    }


    public function getImageStrip() {
      $html = '<div class="imageStrip">';
      foreach ($this->FlickrPhotos() as $key => $photo) {
        $html = $html . '<img src="'.$photo->ThumbnailURL.'"/>';
      }
      $html = $html . "</div>";
      return DBField::create_field( 'HTMLText',  $html);
    }

}


?>