<?php
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
    'Title' => 'Varchar',
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
    'TimeShiftHours' => 'Int'







    //TODO - place id
  );


  /*
  [id] => 5585296500
                    [secret] => 15a4f4dee3
                    [server] => 5223
                    [farm] => 6
                    [title] => IMG_2675
                    [isprimary] => 0
                    [license] => 2
                    [dateupload] => 1301838968
                    [datetaken] => 2011-04-03 06:27:34
                    [datetakengranularity] => 0
                    [ownername] => gordon.b.anderson
                    [iconserver] => 2796
                    [iconfarm] => 3
                    [originalsecret] => d2046d36d6
                    [originalformat] => jpg
                    [lastupdate] => 1301840457

                    [accuracy] => 13

                    [place_id] => ymXOuPpQULp1CrCb
                    [geo_is_family] => 0
                    [geo_is_friend] => 0
                    [geo_is_contact] => 0
                    [geo_is_public] => 1
                    [tags] =>
                    [machine_tags] =>
                    [o_width] => 2048
                    [o_height] => 1364
                    [views] => 7
                    [media] => photo

*/



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
    }
  }




  function getCMSFields() {

    Requirements::css( FLICKR_EDIT_TOOLS_PATH . '/css/flickredit.js' );

    $fields = new FieldList();

    $fields->push( new TabSet( "Root", $mainTab = new Tab( "Main" ) ) );
    $mainTab->setTitle( _t( 'SiteTree.TABMAIN', "Main" ) );





    /*
          $gridFieldConfig = GridFieldConfig::create()->addComponents(
      new GridFieldToolbarHeader(),
      new GridFieldAddNewButton('toolbar-header-right'),
      new GridFieldSortableHeader(),
      new GridFieldDataColumns(),
      new GridFieldPaginator(10),
      new GridFieldEditButton(),
      new GridFieldDeleteAction(),
      new GridFieldDetailForm()
    $gridConfig->getComponentByType( 'GridFieldAddExistingAutocompleter' )->setSearchFields( array( 'Title', 'Description' ) );


    );
    */


    /*
        $gridConfig = GridFieldConfig::create()->addComponent( new GridFieldFlickrImage());


        // FIXME is there a better way of doing this?
        $fl = new FieldList();
        $fl ->push($this);
       $gridField = new GridField( 'FlickrImage','The Flickr Image',$fl,$gridConfig );
      $fields->addFieldToTab( "Root.Main", $gridField );


        //$fields->push( new UploadField('LocalCopyOfImage'));
        */

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
      ) );
    }
   

    return $fields;
  }




  public function AdjustedTime() {
    error_log( "ADJUSTED TIME:".$this->TimeShiftHours );
    return 'FP ADJ TIME '.$this->TimeShiftHours;
  }


  public function getThumbnail() {
    return DBField::create_field( 'HTMLVarchar', '<img src="'.$this->ThumbnailURL.'"/>' );
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
