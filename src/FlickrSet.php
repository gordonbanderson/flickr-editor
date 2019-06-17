<?php
namespace Suilven\Flickr;

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Assets\Folder;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;

/**
 * Only show a page with login when not logged in
 */
class FlickrSet extends DataObject
{
    private static $db = [
        'Title' => 'Varchar(255)',
        'FlickrID' => 'Varchar',
        'Description' => 'HTMLText',
        'FirstPictureTakenAt' => 'Datetime',
        // flag to indicate requiring a flickr API update
        'IsDirty' => DBBoolean::class,
        'LockGeo' => DBBoolean::class,
        'BatchTags' => 'Varchar',
        'BatchTitle' => 'Varchar',
        'BatchDescription' => 'HTMLText',
        'ImageFooter' => 'Text'
    ];

    private static $defaults = [
        'LockGeo' => true
    ];


    private static $many_many = [
        'FlickrPhotos' => FlickrPhoto::class
    ];


    // this is the assets folder
    private static $has_one = [
        'AssetFolder' => Folder::class,
        'PrimaryFlickrPhoto' => FlickrPhoto::class
    ];

    private static $has_many = [
        'FlickrBuckets' => FlickrBucket::class
    ];


    /// model admin
    private static $searchable_fields = [
        'Title',
        'Description',
        'FlickrID'
    ];


    private static $default_sort = 'FirstPictureTakenAt DESC';


    public function getCMSFields()
    {
        Requirements::javascript(FLICKR_EDIT_TOOLS_PATH . '/javascript/flickredit.js');
        Requirements::css(FLICKR_EDIT_TOOLS_PATH . '/css/flickredit.css');

        $fields = new FieldList();

        $fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
        $mainTab->setTitle(_t('SiteTree.TABMAIN', "Main"));

        $fields->addFieldToTab('Root.Main', new TextField('Title', 'Title'));
        $fields->addFieldToTab('Root.Main', new TextAreaField('Description', 'Description'));
        $fields->addFieldToTab('Root.Main', new TextField('ImageFooter', 'Text to be added to each image in this album when saving'));
        $fields->addFieldToTab('Root.Main', new CheckBoxField('LockGeo', 'If the map positions were calculated by GPS, tick this to hide map editing features'));

        $gridConfig = GridFieldConfig_RelationEditor::create();
        // need to add sort order in many to many I think // ->addComponent( new GridFieldSortableRows( 'SortOrder' ) );
        $gridConfig->getComponentByType(GridFieldAddExistingAutocompleter::class)->setSearchFields([ 'Title', 'Description' ]);
        $gridConfig->getComponentByType(GridFieldPaginator::class)->setItemsPerPage(100);


        $gridField = new GridField("Flickr Photos", "List of Photos:", $this->FlickrPhotos(), $gridConfig);
        $fields->addFieldToTab("Root.FlickrPhotos", $gridField);

        $gridConfig2 = GridFieldConfig_RelationEditor::create();
        $gridConfig2->getComponentByType(GridFieldAddExistingAutocompleter::class)->setSearchFields([ 'Title', 'Description' ]);
        $gridConfig2->getComponentByType(GridFieldPaginator::class)->setItemsPerPage(100);

        $bucketsByDate =  $this->FlickrBucketsByDate();
        if ($bucketsByDate->count() > 0) {
            $gridField2 = new GridField("Flickr Buckets", "List of Buckets:", $bucketsByDate, $gridConfig2);
            $fields->addFieldToTab("Root.SavedBuckets", $gridField2);
        }



        $forTemplate = new ArrayData([
            'Title' => $this->Title,
            'ID' => $this->ID,
            'FlickrPhotosNotInBucket' => $this->FlickrPhotosNotInBucket()
        ]);
        $html = $forTemplate->renderWith('GridFieldFlickrBuckets');

        $bucketTimeField = new NumericField('BucketTime');
        // $fields->addFieldToTab( 'Root.Buckets', $bucketTimeField );

        $lfImage = new LiteralField('BucketEdit', $html);
        $fields->addFieldToTab('Root.Buckets', $lfImage);
        $this->extend('updateCMSFields', $fields);

        $fields->addFieldToTab('Root.Batch', new TextField('BatchTitle', 'Batch Title'));
        $fields->addFieldToTab('Root.Batch', new TextAreaField('BatchDescription', 'Batch Description'));
        $fields->addFieldToTab('Root.Batch', new TextAreaField('BatchTags', 'Batch Tags'));

        $htmlBatch = "<p>Click on the batch update button to update the description and title of all of the images, and add tags to each image</p>";
        $htmlBatch .= '<input type="button" id="batchUpdatePhotographs" value="Batch Update"></input>';
        $lf = new LiteralField('BatchUpdate', $htmlBatch);
        $fields->addFieldToTab('Root.Batch', $lf);
        return $fields;
    }



    /*
    Mark image as dirty upon a save
    */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->KeepClean) {
            $this->IsDirty = true;
        }
    }

    public function FlickrPhotosNotInBucket()
    {
        return $this->FlickrPhotos()->where('FlickrPhoto.ID not in (select FlickrPhotoID as ID from FlickrPhoto_FlickrBuckets)');
    }


    public function FlickrBucketsByDate()
    {
        // in 3.1 data list is immutable, hence the chaining
        $sqlbucketidsinorder = 'select distinct FlickrBucketID from (
	  select FlickrBucketID, FlickrPhoto.TakenAt from FlickrBucket
		INNER JOIN FlickrPhoto_FlickrBuckets ON FlickrBucketID = FlickrBucket.ID
		INNER JOIN FlickrPhoto ON FlickrPhoto.ID = FlickrPhoto_FlickrBuckets.FlickrPhotoID
		WHERE (FlickrSetID = '.$this->ID.')
		order by FlickrPhoto.TakenAt
	  ) as OrderedBuckets';

        $buckets = FlickrBucket::get()->filter(['FlickrSetID' => $this->ID])->
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
    public function HasGeo()
    {
        $ct = $this->FlickrPhotos()->where('Lat != 0 OR Lon != 0')->count();
        return $ct > 0;
    }


    /*
      Render a map at the provided lat,lon, zoom from the editing functions,
      */
    public function BasicMap()
    {
        $photosWithLocation = $this->FlickrPhotos()->where('Lat != 0 AND Lon !=0');
        if ($photosWithLocation->count() == 0) {
            return ''; // don't render a map
        }

        //$photosWithLocation->setRenderMarkers(false);
        $map = $photosWithLocation->getRenderableMap();

        $map->setZoom($this->owner->ZoomLevel);
        $map->setAdditionalCSSClasses('fullWidthMap');
        $map->setShowInlineMapDivStyle(true);

        //$map->setInfoWindowWidth(500);


        // add any KML map layers
        if (Object::has_extension($this->owner->ClassName, 'MapLayerExtension')) {
            foreach ($this->owner->MapLayers() as $layer) {
                $map->addKML($layer->KmlFile()->getAbsoluteURL());
            }
            $map->setEnableAutomaticCenterZoom(true);
        }


        // add points of interest taking into account the default icon of the layer as an override
        if (Object::has_extension($this->owner->ClassName, 'PointsOfInterestLayerExtension')) {
            $markercache = SS_Cache::factory('mappable');

            $ck = $this->getPoiMarkersCacheKey();
            $map->MarkersCacheKey = $ck;

            // If we have JSON already do not load the objects
            if (!($jsonMarkers = $markercache->test($ck))) {
                foreach ($this->owner->PointsOfInterestLayers() as $layer) {
                    $layericon = $layer->DefaultIcon();
                    if ($layericon->ID === 0) {
                        $layericon = null;
                    }
                    foreach ($layer->PointsOfInterest() as $poi) {
                        if ($poi->MapPinEdited) {
                            if ($poi->MapPinIconID == 0) {
                                $poi->CachedMapPin = $layericon;
                            }
                            $map->addMarkerAsObject($poi);
                        }
                    }
                }
            }
        }

        $map->setClusterer(true);
        $map->setEnableAutomaticCenterZoom(true);

        $map->setZoom(10);
        $map->setAdditionalCSSClasses('fullWidthMap');
        $map->setShowInlineMapDivStyle(true);
        $map->setClusterer(true);

        return $map;
    }



    public function writeToFlickr()
    {
        $suffix = $this->ImageFooter ."\n\n".Controller::curr()->SiteConfig()->ImageFooter;
        $imagesToUpdate = $this->FlickrPhotos()->where('IsDirty = 1');
        $ctr = 1;
        $amount = $imagesToUpdate->count();

        foreach ($imagesToUpdate as $fp) {
            error_log('UPDATING:'.$fp->Title);
            $fp->writeToFlickr($suffix);
            $ctr++;
        }
    }
}
