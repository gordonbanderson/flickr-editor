<?php
/**
 * Only show a page with login when not logged in
 */
class FlickrSet extends DataObject {


  static $db = array(
    'Title' => 'Varchar(255)',
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


  public static $default_sort = 'FirstPictureTakenAt DESC';




  function getCMSFields() {
    error_log("FLICKR SET GET CMS FIELDS");

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

    $bucketsByDate =  $this->FlickrBucketsByDate();
    if ($bucketsByDate->count() > 0) {
      $gridField2 = new GridField( "Flickr Buckets", "List of Buckets:", $bucketsByDate, $gridConfig2 );
      $fields->addFieldToTab( "Root.SavedBuckets", $gridField2 );
    } 
    


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
    $this->extend('updateCMSFields', $fields);

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
   

  // in 3.1 data list is immutable, hence the chaining
    $sqlbucketidsinorder = 'select distinct FlickrBucketID from (
      select FlickrBucketID, FlickrPhoto.TakenAt from FlickrBucket 
        INNER JOIN FlickrPhoto_FlickrBuckets ON FlickrBucketID = FlickrBucket.ID
        INNER JOIN FlickrPhoto ON FlickrPhoto.ID = FlickrPhoto_FlickrBuckets.FlickrPhotoID
        WHERE (FlickrSetID = '.$this->ID.')
        order by FlickrPhoto.TakenAt
      ) as OrderedBuckets';

    $buckets = FlickrBucket::get()->filter(array('FlickrSetID' => $this->ID))->
    innerJoin('FlickrPhoto_FlickrBuckets', 'FlickrBucketID = FlickrBucket.ID')->
    innerJoin('FlickrPhoto', 'FlickrPhotoID = FlickrPhoto.ID')->
    sort('TakenAt');


    $result = new ArrayList();
    foreach ($buckets->getIterator() as $bucket) {
      $result->push($bucket);
    }
    $result->removeDuplicates();
    
    return $result;
  }



/*
  Count the number of non zero lat and lon points - if > 0 then we can draw a map
*/
  public function HasGeo() {
    $ct = $this->FlickrPhotos()->where('Lat != 0 OR Lon != 0')->count();
    error_log("SET HAS GEO? CT=".$ct);
    $result = ($ct > 0);
    error_log($result);

    return $result;

  }



  public function Map() {
    $photosWithLocation = $this->FlickrPhotos()->where('Lat != 0 AND Lon !=0');
    if ($photosWithLocation->count() == 0) {
      return ''; // don't render a map
    }
    $map = $photosWithLocation->getRenderableMap();
    // $map->setDelayLoadMapFunction( true );
    $map->setZoom( 10 );
    $map->setAdditionalCSSClasses( 'fullWidthMap' );
    $map->setShowInlineMapDivStyle( true );
    $map->setClusterer(true);
    foreach($this->MapLayers() as $layer) {
      $map->addKML($layer->KmlFile()->getAbsoluteURL());
    }


    //$map->addKML('http://assets.tripodtravel.co.nz/cycling/meuang-nont-to-bang-sue-loop.kml');
    $map->setSize('100%','600px');
    return $map;
  }


  public function writeToFlickr() {
    $suffix = $this->ImageFooter ."\n\n".Controller::curr()->SiteConfig()->ImageFooter;
    $imagesToUpdate = $this->FlickrPhotos()->where('IsDirty = 1');
    $ctr = 1;
    $amount = $imagesToUpdate->count();

    foreach ($imagesToUpdate as $fp) {
      error_log("\n\n==".$ctr.'/'.$amount."==");
      $fp->writeToFlickr($suffix);
      $ctr++;
    }
  }

}

?>
