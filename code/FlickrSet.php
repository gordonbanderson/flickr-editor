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
    'LockGeo' => 'Boolean'
  );


  static $many_many = array(
    'FlickrPhotos' => 'FlickrPhoto'
  );


  // this is the assets folder
  static $has_one = array (
    'AssetFolder' => 'Folder'
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

    $gridField2 = new GridField( "Flickr Buckets", "List of Buckets:", $this->FlickrBuckets(), $gridConfig2 );
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
