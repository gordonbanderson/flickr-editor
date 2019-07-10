<?php
namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DB;
use Suilven\Flickr\Helper\FlickrTagHelper;
use Suilven\Flickr\Helper\FlickrUpdateMetaHelper;
use Suilven\Flickr\Model\Flickr\FlickrAuthor;
use Suilven\Flickr\Model\Flickr\FlickrBucket;
use Suilven\Flickr\Model\Flickr\FlickrExif;
use Suilven\Flickr\Model\Flickr\FlickrTag;

// @todo does not work ...
// require_once "../phpFlickr.php";

/**
 * Only show a page with login when not logged in
 */
class FlickrPhoto extends DataObject
{
    private static $table_name = 'FlickrPhoto';

    private static $db = array(
        'Title' => 'Varchar(255)',
        'FlickrID' => 'Varchar',
        'Description' => 'HTMLText',
        'TakenAt' => 'Datetime',
        'FlickrLastUpdated' => DBDate::class,
        'GeoIsPublic' => DBBoolean::class,

        // flag to indicate requiring a flickr API update
        'IsDirty' => DBBoolean::class,

        'Orientation' => 'Int',
        'WoeID' => 'Int',
        'Accuracy' => 'Int',
        'FlickrPlaceID' => 'Varchar(255)',
        'Rotation' => 'Int',
        'IsPublic' => DBBoolean::class,
        'Aperture' => 'Float',
        'ShutterSpeed' => 'Varchar',
        'ImageUniqueID' => 'Varchar',
        'FocalLength35mm' => 'Int',
        'ISO' => 'Int',

        'AspectRatio' => 'Float',

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
        'PromoteToHomePage' => DBBoolean::class
        //TODO - place id
    );


    private static $belongs_many_many = array(
        'FlickrSets' => FlickrSet::class
    );


    // this one is what created the database FlickrPhoto_FlickrTagss
    private static $many_many = array(
        'FlickrTags' => FlickrTag::class,
        'FlickrBuckets' => FlickrBucket::class
    );


    private static $has_many = array(
        'Exifs' => FlickrExif::class
    );


    private static $has_one = array(
        'LocalCopyOfImage' => Image::class,
        'Photographer' => FlickrAuthor::class
    );


    private static $summary_fields = array(
        'Thumbnail' => 'Thumbnail',
        'Title' => 'Title',
 //       'TakenAt' => 'TakenAt',
 //       'HasGeoEng' => 'Geolocated?'
    );


    private static $sphinx = array(
        "search_fields" => array( "Title", "Description", 'FocalLength35mm', 'Aperture', 'ISO', 'ShutterSpeed' ),
        "filter_fields" => array(),
        "index_filter" => '"ID" != 0',
        "sort_fields" => array( "Title" )

    );

    // -- helper methods to ensure that URLs are of the form //path/to/image so that http and https work with console warnings
    public function ProtocolAgnosticLargeURL()
    {
        return $this->stripProtocol($this->LargeURL);
    }

    public function ProtocolAgnosticSmallURL()
    {
        return $this->stripProtocol($this->SmallURL);
    }


    public function ProtocolAgnosticMediumURL()
    {
        return $this->stripProtocol($this->MediumURL);
    }

    public function ProtocolAgnosticThumbnailURL()
    {
        return $this->stripProtocol($this->ThumbnailURL);
    }

    public function ProtocolAgnosticOriginalURL()
    {
        return $this->stripProtocol($this->OriginalURL);
    }



    private function stripProtocol($url)
    {
        $url = str_replace('http:', '', $url);
        $url = str_replace('https:', '', $url);
        return $url;
    }


    // thumbnail related

    public function HorizontalMargin($intendedWidth)
    {
        //FIXME - is there a way to avoid a database call here?
        $fp = DataObject::get_by_id(FlickrPhoto::class, $this->ID);
        return ($intendedWidth-$fp->ThumbnailWidth)/2;
    }


    public function InfoWindow()
    {
        return GoogleMapUtil::sanitize($this->renderWith('FlickrPhotoInfoWindow'));
    }


    public function VerticalMargin($intendedHeight)
    {
        //FIXME - is there a way to avoid a database call here?
        $fp = DataObject::get_by_id(FlickrPhoto::class, $this->ID);
        return ($intendedHeight-$fp->ThumbnailHeight)/2;
    }


    public function Link()
    {
        $link = "http://www.flickr.com/photos/{$this->Photographer()->PathAlias}/{$this->FlickrID}/";
        return $link;
    }


    public function AbsoluteLink()
    {
        return $this->Link();
    }


    /*
        Mark image as dirty upon a save
        */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $tagHelper = new FlickrTagHelper();
        $quickTags = $tagHelper->createOrFindTags($this->QuickTags);

        $this->FlickrTags()->addMany($quickTags);
        if ($this->LargeWidth > 0) {
            $this->AspectRatio = ($this->LargeHeight) / ($this->LargeWidth);
        }

        if (!$this->KeepClean) {
            $this->IsDirty = true;
        } else {
            $this->IsDirty = false;
        }
    }


    public function getCMSFields()
    {
        Requirements::css('weboftalent/flickr:css/flickredit.css');
        Requirements::javascript('weboftalent/flickr:javascript/flickredit.js');

        $flickrSetID = Controller::curr()->request->param('ID');



        $fields = new FieldList();


        $fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
        $mainTab->setTitle(_t('SiteTree.TABMAIN', "Main"));


        $forTemplate = new ArrayData(array(
                'FlickrPhoto' => $this,
                'FlickrSetID' => $flickrSetID // not sure if this is Flickr ID or SS ID
            ));
        $imageHtml = $forTemplate->renderWith('Includes/FlickrImageEditing');


        $lfImage = new LiteralField('FlickrImage', $imageHtml);
        $fields->addFieldToTab('Root.Main', $lfImage);
        $fields->addFieldToTab('Root.Main', new TextField('Title', 'Title'));
        $fields->addFieldToTab('Root.Main', new TextareaField('Description', 'Description'));

        // only show a map for editing if no sets have geolock on them
        $lockgeo = false;
        foreach ($this->FlickrSets() as $set) {
            if ($set->LockGeo == true) {
                $lockgeo = true;
                break;
            }
        }

        if (!$lockgeo) {
            $fields->addFieldToTab(
                 "Root.Location",
                 $mapField = new LatLongField(
                 array(
                    new TextField('Lat', 'Latitude'),
                    new TextField('Lon', 'Longitude'),
                    new TextField('ZoomLevel', 'Zoom')
                ),
                 array( 'Address' )
                    )
             );


            $guidePoints = array();

            foreach ($this->FlickrSets() as $set) {
                foreach ($set->FlickrPhotos()->where('Lat != 0 and Lon != 0') as $fp) {
                    if (($fp->Lat != 0) && ($fp->Lon != 0)) {
                        array_push($guidePoints, array(
                            'latitude' => $fp->Lat,
                            'longitude' => $fp->Lon
                        ));
                    }
                }
            }

            if (count($guidePoints) > 0) {
                $mapField->setGuidePoints($guidePoints);
            }
        }

        // quick tags, faster than the grid editor - these are processed prior to save to create/assign tags
        $fields->addFieldToTab('Root.Main', new TextField('QuickTags', 'Enter tags here separated by commas'));

        $gridConfig = GridFieldConfig_RelationEditor::create();//->addComponent( new GridFieldSortableRows( 'Value' ) );
        $gridConfig->getComponentByType(GridFieldAddExistingAutocompleter::class)->setSearchFields(array( 'Value','RawValue' ));
        $gridField = new GridField("Tags", "List of Tags", $this->FlickrTags(), $gridConfig);
        $fields->addFieldToTab("Root.Main", $gridField);

        $fields->addFieldToTab("Root.Main", new CheckboxField('PromoteToHomePage', 'Promote to Home Page'));


        return $fields;
    }


    public function AdjustedTime()
    {
        return 'FP ADJ TIME '.$this->TimeShiftHours;
    }


    public function getThumbnail()
    {
        return DBField::create_field(
            'HTMLVarchar',
            '<img class="flickrThumbnail" data-flickr-medium-url="'.$this->MediumURL.'" src="'.$this->ThumbnailURL.'"  data-flickr-thumbnail-url="'.$this->ThumbnailURL.'"/>'
        );
    }


    private function initialiseFlickr()
    {
        if (!isset($this->f)) {
            // get flickr details from config
            $key = Config::inst()->get('FlickrController', 'api_key');
            $secret = Config::inst()->get('FlickrController', 'secret');
            $access_token = Config::inst()->get('FlickrController', 'access_token');

            $this->f = new phpFlickr($key, $secret);

            //Fleakr.auth_token    = ''
            $this->f->setToken($access_token);
        }
    }


    public function HasGeo()
    {
        return $this->Lat != 0 || $this->Lon != 0;
    }


    public function HasGeoEng()
    {
        return $this->HasGeo() ? 'Yes': 'No';
    }





    /*
    Update Flickr with details held in SilverStripe
    @param $descriptionSuffix The suffix to be appended to the photographic description
    */
    public function writeToFlickr($descriptionSuffix)
    {
        $helper = new FlickrUpdateMetaHelper();
        $helper->writePhotoToFlickr($this, $descriptionSuffix);
    }
}
