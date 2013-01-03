<?php
/**
 * Only show a page with login when not logged in
 */
class FlickrSet extends DataObject {


  static $db = array(
    'Title' => 'Varchar',
    'FlickrID' => 'Varchar',
    'Description' => 'HTMLText',
    'FirstPictureTakenAt' => 'Datetime',
    // flag to indicate requiring a flickr API update
    'IsDirty' => 'Boolean',
    'LockGeo' => 'Boolean',
    'BatchTags' => 'Varchar',
    'BatchTitle' => 'Varchar',
    'BatchDescription' => 'HTMLText',
    'ImageFooter' => 'Text'
  );


  static $many_many = array(
    'FlickrPhotos' => 'FlickrPhoto'
  );


  // this is the assets folder
  static $has_one = array (
    'AssetFolder' => 'Folder',
    'PrimaryFlickrPhoto' => 'FlickrPhoto'
  );

  static $has_many = array(
    'FlickrBuckets' => 'FlickrBucket'
  );


  /// model admin
  static $searchable_fields = array(
    'Title',
    'Description',
    'FlickrID'
  );



  function getCMSFields() {

    Requirements::javascript( FLICKR_EDIT_TOOLS_PATH . '/javascript/flickredit.js' );
    Requirements::css( FLICKR_EDIT_TOOLS_PATH . '/css/flickredit.css' );

    $fields = new FieldList();

    $fields->push( new TabSet( "Root", $mainTab = new Tab( "Main" ) ) );
    $mainTab->setTitle( _t( 'SiteTree.TABMAIN', "Main" ) );



    $fields->addFieldToTab( 'Root.Main',  new TextField( 'Title', 'Title') );
    $fields->addFieldToTab( 'Root.Main', new TextAreaField( 'Description', 'Description' )  );
    $fields->addFieldToTab( 'Root.Main',  new TextField( 'ImageFooter', 'Text to be added to each image in this album when saving') );
    $fields->addFieldToTab( 'Root.Main', new CheckBoxField( 'LockGeo', 'If the map positions were calculated by GPS, tick this to hide map editing features' )  );


    $gridConfig = GridFieldConfig_RelationEditor::create();
    // need to add sort order in many to many I think // ->addComponent( new GridFieldSortableRows( 'SortOrder' ) );
    $gridConfig->getComponentByType( 'GridFieldAddExistingAutocompleter' )->setSearchFields( array( 'Title', 'Description' ) );
    $gridConfig->getComponentByType( 'GridFieldPaginator' )->setItemsPerPage( 100 );


    $gridField = new GridField( "Flickr Photos", "List of Photos:", $this->FlickrPhotos(), $gridConfig );
    $fields->addFieldToTab( "Root.FlickrPhotos", $gridField );

    $gridConfig2 = GridFieldConfig_RelationEditor::create();
    $gridConfig2->getComponentByType( 'GridFieldAddExistingAutocompleter' )->setSearchFields( array( 'Title', 'Description' ) );
    $gridConfig2->getComponentByType( 'GridFieldPaginator' )->setItemsPerPage( 100 );

    $gridField2 = new GridField( "Flickr Buckets", "List of Buckets:", $this->FlickrBucketsByDate(), $gridConfig2 );
    $fields->addFieldToTab( "Root.SavedBuckets", $gridField2 );


    $forTemplate = new ArrayData( array(
        'Title' => $this->Title,
        'ID' => $this->ID,
        'FlickrPhotosNotInBucket' => $this->FlickrPhotosNotInBucket()

      ) );
    $html = $forTemplate->renderWith( 'GridFieldFlickrBuckets' );

    $bucketTimeField = new NumericField( 'BucketTime' );
   // $fields->addFieldToTab( 'Root.Buckets', $bucketTimeField );

    $lfImage = new LiteralField( 'BucketEdit', $html );
    $fields->addFieldToTab( 'Root.Buckets', $lfImage );

    $fields->addFieldToTab( 'Root.Batch',  new TextField( 'BatchTitle', 'Batch Title') );
    $fields->addFieldToTab( 'Root.Batch', new TextAreaField( 'BatchDescription', 'Batch Description' )  );
    $fields->addFieldToTab( 'Root.Batch', new TextAreaField( 'BatchTags', 'Batch Tags' )  );
    
    $htmlBatch = "<p>Click on the batch update button to update the description and title of all of the images, and add tags to each image</p>";
    $htmlBatch .= '<input type="button" id="batchUpdatePhotographs" value="Batch Update"></input>';
    $lf = new LiteralField( 'BatchUpdate', $htmlBatch );
    $fields->addFieldToTab( 'Root.Batch', $lf);


    return $fields;
  }



  /*
    Mark image as dirty upon a save
    */
  function onBeforeWrite() {
    parent::onBeforeWrite();
    if (!$this->KeepClean) {
      $this->IsDirty = true;
    }
  }

  function FlickrPhotosNotInBucket() {
    error_log("FLICKR PHOTOS NOT IN BUCKETS");
    return $this->FlickrPhotos()->where('FlickrPhoto.ID not in (select FlickrPhotoID as ID from FlickrPhoto_FlickrBuckets)');
  }

  function FlickrBucketsByDate() {
    /*
    The following does not work as the TakenAt field is returned with the results, meaning things cannot be uniquified
    $result = $this->FlickrBuckets()->innerJoin('FlickrPhoto_FlickrBuckets','FlickrBucketID = FlickrBucket.ID')->
    innerJoin('FlickrPhoto', 'FlickrPhoto.ID = FlickrPhoto_FlickrBuckets.FlickrPhotoID')->
    sort('TakenAt');
    */
    $result = $this->FlickrBuckets()->where('
      FlickrBucket.ID in (select distinct FlickrBucketID from FlickrBucket 
      INNER JOIN FlickrPhoto_FlickrBuckets ON FlickrBucketID = FlickrBucket.ID
      INNER JOIN FlickrPhoto ON FlickrPhoto.ID = FlickrPhoto_FlickrBuckets.FlickrPhotoID
      WHERE (FlickrSetID = '.$this->ID.') order by TakenAt)');

    
    return $result;
  }



/*
  Count the number of non zero lat and lon points - if > 0 then we can draw a map
*/
  public function HasGeo() {
    return $this->FlickrPhotos()->where('Latitude != 0 AND Longitude != 0')->count() > 0;
  }



  public function Map() {
    //    $prod->SetZoom(4);



    $map = $this->FlickrPhotos()->where('Lat != 0 AND Lon !=0')->RenderMap();
   // $map->setDelayLoadMapFunction( true );
    $map->setZoom( 10 );
    $map->setAdditionalCSSClasses( 'fullWidthMap' );
    $map->setShowInlineMapDivStyle( true );
    $map->setClusterer(true);
    //$map->addKML('http://assets.tripodtravel.co.nz/cycling/meuang-nont-to-bang-sue-loop.kml');

   /*
    $map->addLine(
      array(13.836966000000000,100.525958000000003),
      array(13.719272000000000,100.504747000000005)
    );
*/
    return $map;
  }





  /*
--------------------+----------------+------+-----+---------+----------------+
| Field               | Type           | Null | Key | Default | Extra          |
+---------------------+----------------+------+-----+---------+----------------+
| id                  | int(11)        | NO   | PRI | NULL    | auto_increment |
| flickr_id           | varchar(255)   | YES  | UNI | NULL    |                |
| title               | varchar(255)   | YES  |     | NULL    |                |
| description         | text           | YES  |     | NULL    |                |
| taken_at            | datetime       | YES  | MUL | NULL    |                |
| position            | int(11)        | YES  |     | NULL    |                |
| created_at          | datetime       | YES  |     | NULL    |                |
| updated_at          | datetime       | YES  |     | NULL    |                |
| flickr_last_updated | datetime       | YES  |     | NULL    |                |
| latitude            | decimal(15,10) | YES  |     | NULL    |                |
| longitude           | decimal(15,10) | YES  |     | NULL    |                |
| zoom_level          | int(11)        | YES  |     | NULL    |                |
| timezone_name_id    | int(11)        | YES  |     | NULL    |                |
| permalink           | varchar(255)   | YES  | UNI | NULL    |                |
| orientation_id      | int(11)        | YES  |     | NULL    |
  */
}



?>