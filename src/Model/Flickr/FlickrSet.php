<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Assets\Folder;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Only show a page with login when not logged in
 *
 * @property string $Title
 * @property string $FlickrID
 * @property string $Description
 * @property string $FirstPictureTakenAt
 * @property bool $IsDirty
 * @property bool $LockGeo
 * @property string $BatchTags
 * @property string $BatchTitle
 * @property string $BatchDescription
 * @property string $ImageFooter
 * @property string $SpriteCSS
 * @property string $SortOrder
 * @property int $AssetFolderID
 * @property int $PrimaryFlickrPhotoID
 * @method \SilverStripe\Assets\Folder AssetFolder()
 * @method \Suilven\Flickr\Model\Flickr\FlickrPhoto PrimaryFlickrPhoto()
 * @method \SilverStripe\ORM\DataList|array<\Suilven\Flickr\Model\Flickr\FlickrBucket> FlickrBuckets()
 * @method \SilverStripe\ORM\ManyManyList|array<\Suilven\Flickr\Model\Flickr\FlickrPhoto> FlickrPhotos()
 */
class FlickrSet extends DataObject
{
    private static $table_name = 'FlickrSet';

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
        'ImageFooter' => 'Text',
        'SpriteCSS' => 'Text',
        'SortOrder' => "Enum('TakenAt,UploadUnixTimeStamp', 'UploadUnixTimeStamp')",
    ];

    private static $defaults = [
        'LockGeo' => true,
    ];

    private static $many_many = [
        'FlickrPhotos' => FlickrPhoto::class,
    ];

    // this is the assets folder
    private static $has_one = [
        'AssetFolder' => Folder::class,
        'PrimaryFlickrPhoto' => FlickrPhoto::class,
    ];

    private static $has_many = [
        'FlickrBuckets' => FlickrBucket::class,
    ];

    /// model admin
    private static $summary_fields = [
        'Title',
        'Description',
        'FlickrID',
    ];

    private static $default_sort = 'FirstPictureTakenAt DESC';


    public function getCMSFields(): FieldList
    {
        //dist/admin/client/js/thirdpartyvendor.js
        //Requirements::javascript('weboftalent/flickr:dist/admin/client/js/thirdpartyvendor.js');
        Requirements::javascript('weboftalent/flickr:dist/admin/client/js/flickredit.js');
        Requirements::css('weboftalent/flickr:dist/admin/client/css/flickredit.css');

        $fields = new FieldList();

        $fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
        $mainTab->setTitle(\_t('SiteTree.TABMAIN', "Main"));

        $fields->addFieldToTab('Root.Main', new TextField('Title', 'Title'));
        $fields->addFieldToTab('Root.Main', new TextareaField('Description', 'Description'));

        $fields->addFieldsToTab(
            'Root.Main',
            DropdownField::create('SortOrder', 'Sort Order', \singleton(FlickrSet::class)->
            dbObject('SortOrder')->enumValues()),
        );

        $fields->addFieldToTab('Root.Main', new TextField(
            'ImageFooter',
            'Text to be added to each image in this album when saving',
        ));
        $fields->addFieldToTab('Root.Main', new CheckboxField(
            'LockGeo',
            'If the map positions were calculated by GPS, tick this to hide map editing features',
        ));


        $gridConfig = GridFieldConfig_RelationEditor::create();
        // need to add sort order in many to many I think // ->addComponent( new GridFieldSortableRows( 'SortOrder' ) );
        $gridConfig->getComponentByType(GridFieldAddExistingAutocompleter::class)->
            setSearchFields(['Title', 'Description']);

        // @todo Make page size configurable
        $gridConfig->getComponentByType(GridFieldPaginator::class)->setItemsPerPage(100);
        $gridField = new GridField(
            "FlickrPhotos",
            "List of Photos:",
            $this->FlickrPhotos(),
            $gridConfig,
        );
        $fields->addFieldToTab("Root.FlickrPhotos", $gridField);

        $gridConfig2 = GridFieldConfig_RelationEditor::create();
        $gridConfig2->getComponentByType(GridFieldAddExistingAutocompleter::class)->
            setSearchFields(['Title', 'Description']);
        $gridConfig2->getComponentByType(GridFieldPaginator::class)->setItemsPerPage(100);

        /** @var \SilverStripe\ORM\DataList $bucketsByDate */
        $bucketsByDate = $this->FlickrBucketsByDate();

        /*
        foreach($bucketsByDate as $item) {
            print_r($item);
            die;
        }
        */
        if ($bucketsByDate->count() > 0) {
            $gridField2 = new GridField(
                "FlickrBuckets",
                "List of Buckets:",
                $bucketsByDate,
                $gridConfig2,
            );
            $fields->addFieldToTab("Root.SavedBuckets", $gridField2);
        }


        $forTemplate = new ArrayData([
            'Title' => $this->Title,
            'ID' => $this->ID,
            'FlickrPhotosNotInBucket' => $this->FlickrPhotosNotInBucket(),
        ]);
        $html = $forTemplate->renderWith('Includes/ReactFlickrBuckets');
        $lfImage = new LiteralField('BucketEdit', $html);


        // @todo This is loading all the thumbnails, disable temporarily
         $fields->addFieldToTab('Root.Buckets', $lfImage);


        $templateData = new ArrayData([
            'FlickrSet' => $this,
            'SecurityToken' => SecurityToken::inst()->getValue(),
        ]);
        $html = $forTemplate->renderWith('Includes/VisibleImageSelector', $templateData);
        $lfImage = new LiteralField('VisibleImagesField', $html);
        $fields->addFieldToTab('Root.Visible', $lfImage);


        $this->extend('updateCMSFields', $fields);

        $fields->addFieldToTab('Root.Batch', new TextField(
            'BatchTitle',
            'Batch Title',
        ));
        $fields->addFieldToTab('Root.Batch', new TextareaField(
            'BatchDescription',
            'Batch Description',
        ));
        $fields->addFieldToTab('Root.Batch', new TextareaField(
            'BatchTags',
            'Batch Tags',
        ));

        $htmlBatch = "<p>Click on the batch update button to update the description and title of ' .
            'all of the images, and add tags to each image</p>";
        $htmlBatch .= '<input type="button" id="batchUpdatePhotographs" value="Batch Update"></input>';
        $lf = new LiteralField('BatchUpdate', $htmlBatch);
        $fields->addFieldToTab('Root.Batch', $lf);


        // unix commands
        $forTemplate = new ArrayData([
            'ID' => $this->FlickrID,
        ]);
        $html = $forTemplate->renderWith('Includes/UNIXCommands');
        $lfCommands = new LiteralField('Commands', $html);
        $fields->addFieldToTab('Root.Commands', $lfCommands);

        return $fields;
    }


    /**
     * Get the photographs that are marked as visible
     *
     * @return \SilverStripe\ORM\DataList<\Suilven\Flickr\Model\Flickr\FlickrPhoto>
     */
    public function VisibleFlickrPhotos(): DataList
    {
        return $this->FlickrPhotos()->filter(['Visible' => true]);
    }


    /**
    Mark image as dirty upon a save
    */
    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        if ($this->KeepClean) {
            return;
        }

        $this->IsDirty = true;
    }


    /**
     * Get photos that are not part of an existing bucket
     *
     * @return \SilverStripe\ORM\DataList<\Suilven\Flickr\Model\Flickr\FlickrPhoto>
     */
    public function FlickrPhotosNotInBucket(): DataList
    {
        return $this->FlickrPhotos()->
        where('"FlickrPhoto"."ID" not in (select "FlickrPhotoID" as "ID" from ' .
                '"FlickrPhoto_FlickrBuckets")')
            ->sort($this->SortOrder);
    }


    public function FlickrBucketsByDate(): ArrayList
    {
        // adding in a sort field of TakenAt results in duplicates.  If one uses UploadUnixTimeStamp
        // the issue is worse, a unique row is being created for each unique UploadUnixTimeStamp,
        // so for now, use Title
        return FlickrBucket::get()->filter(['FlickrSetID' => $this->ID])->
            innerJoin('FlickrPhoto_FlickrBuckets', '"FlickrBucketID" = ' .
                '"FlickrBucket"."ID"')->
            innerJoin('FlickrPhoto', '"FlickrPhotoID" = "FlickrPhoto"."ID"')
            ->sort('Title')
            ;
    }


    /**
     * Count the number of non zero lat and lon points - if > 0 then we can draw a map
     *
     * @return true if the photographs are mappable
    */
    public function HasGeo()
    {
        $ct = $this->FlickrPhotos()->where('Lat != 0 OR Lon != 0')->count();

        return $ct > 0;
    }


    /*
      Render a map at the provided lat,lon, zoom from the editing functions,
      */
    /*
     @TODO Switch to silverstripe GIS package
    public function BasicMap()
    {
        $photosWithLocation = $this->FlickrPhotos()->where('Lat != 0 AND Lon !=0');
        if ($photosWithLocation->count() === 0) {
            // don't render a map
            return '';
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
            // @todo use PSR caching, this is old
            $markercache = SS_Cache::factory('mappable');

            $ck = $this->getPoiMarkersCacheKey();
            $map->MarkersCacheKey = $ck;

            // @todo the method in question does not exist
            $jsonMarkers = $markercache->test($ck);

            // If we have JSON already do not load the objects
            if (!($jsonMarkers = $markercache->test($ck))) {
                foreach ($this->owner->PointsOfInterestLayers() as $layer) {
                    $layericon = $layer->DefaultIcon();
                    if ($layericon->ID === 0) {
                        $layericon = null;
                    }
                    foreach ($layer->PointsOfInterest() as $poi) {
                        if (!$poi->MapPinEdited) {
                            continue;
                        }

                        if ($poi->MapPinIconID === 0) {
                            $poi->CachedMapPin = $layericon;
                        }
                        $map->addMarkerAsObject($poi);
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

    */

    public function writeToFlickr(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $suffix = $this->ImageFooter . "\n\n" . $siteConfig->ImageFooter;
        $imagesToUpdate = $this->FlickrPhotos()->filter(['IsDirty' => 1]);
        $ctr = 1;
        $amount = $imagesToUpdate->count();

        /** @var \Suilven\Flickr\Model\Flickr\FlickrPhoto $fp */
        foreach ($imagesToUpdate as $fp) {
            \error_log($ctr . '/' . $amount . ' [' . $fp->FlickrID . ']  - UPDATING:' . $fp->Title);
            $fp->writeToFlickr($suffix);
            $ctr++;
        }
    }
}
