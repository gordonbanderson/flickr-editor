<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Flickr;

use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use Suilven\Flickr\Helper\FlickrTagHelper;
use Suilven\Flickr\Helper\FlickrUpdateMetaHelper;
use Suilven\Flickr\Model\Site\FlickrSetPage;

/**
 * Only show a page with login when not logged in
 *
 * @property string $Title
 * @property string $FlickrID
 * @property string $Description
 * @property string $TakenAt
 * @property string $FlickrLastUpdated
 * @property bool $GeoIsPublic
 * @property bool $IsDirty
 * @property int $Orientation
 * @property int $WoeID
 * @property int $Accuracy
 * @property string $FlickrPlaceID
 * @property int $Rotation
 * @property bool $IsPublic
 * @property float $Aperture
 * @property string $ShutterSpeed
 * @property string $ImageUniqueID
 * @property int $FocalLength35mm
 * @property int $ISO
 * @property float $AspectRatio
 * @property string $SmallURL
 * @property int $SmallHeight
 * @property int $SmallWidth
 * @property string $SmallURL320
 * @property int $SmallHeight320
 * @property int $SmallWidth320
 * @property string $MediumURL
 * @property int $MediumHeight
 * @property int $MediumWidth
 * @property string $MediumURL640
 * @property int $MediumHeight640
 * @property int $MediumWidth640
 * @property string $MediumURL800
 * @property int $MediumHeight800
 * @property int $MediumWidth800
 * @property string $SquareURL
 * @property int $SquareHeight
 * @property int $SquareWidth
 * @property string $SquareURL150
 * @property int $SquareHeight150
 * @property int $SquareWidth150
 * @property string $LargeURL
 * @property int $LargeHeight
 * @property int $LargeWidth
 * @property string $LargeURL1600
 * @property int $LargeHeight1600
 * @property int $LargeWidth1600
 * @property string $LargeURL2048
 * @property int $LargeHeight2048
 * @property int $LargeWidth2048
 * @property string $ThumbnailURL
 * @property int $ThumbnailHeight
 * @property int $ThumbnailWidth
 * @property string $OriginalURL
 * @property int $OriginalHeight
 * @property int $OriginalWidth
 * @property int $TimeShiftHours
 * @property bool $PromoteToHomePage
 * @property bool $Imported
 * @property float $DigitalZoomRatio
 * @property int $UploadUnixTimeStamp
 * @property string $PerceptiveHash
 * @property int $LocalCopyOfImageID
 * @property int $PhotographerID
 * @method \SilverStripe\Assets\Image LocalCopyOfImage()
 * @method \Suilven\Flickr\Model\Flickr\FlickrAuthor Photographer()
 * @method \SilverStripe\ORM\DataList|array<\Suilven\Flickr\Model\Flickr\FlickrExif> Exifs()
 * @method \SilverStripe\ORM\ManyManyList|array<\Suilven\Flickr\Model\Flickr\FlickrTag> FlickrTags()
 * @method \SilverStripe\ORM\ManyManyList|array<\Suilven\Flickr\Model\Flickr\FlickrBucket> FlickrBuckets()
 * @method \SilverStripe\ORM\ManyManyList|array<\Suilven\Flickr\Model\Flickr\FlickrSet> FlickrSets()
 */
class FlickrPhoto extends DataObject
{
    private static $table_name = 'FlickrPhoto';

    private static $db = [
        'Title' => 'Varchar(255)';
    private 'FlickrID' => 'Varchar';
    private 'Description' => 'HTMLText';
    private 'TakenAt' => 'Datetime';
    private 'FlickrLastUpdated' => DBDate::class;
    private 'GeoIsPublic' => DBBoolean::class;
    private 'IsDirty' => DBBoolean::class;
    private 'Orientation' => 'Int';
    private 'WoeID' => 'Int';
    private 'Accuracy' => 'Int';
    private 'FlickrPlaceID' => 'Varchar(255)';
    private 'Rotation' => 'Int';
    private 'IsPublic' => DBBoolean::class;
    private 'Aperture' => 'Float';
    private 'ShutterSpeed' => 'Varchar';
    private 'ImageUniqueID' => 'Varchar';
    private 'FocalLength35mm' => 'Int';
    private 'ISO' => 'Int';
    private 'AspectRatio' => 'Float';
    private 'SmallURL' => 'Varchar(255)';
    private 'SmallHeight' => 'Int';
    private 'SmallWidth' => 'Int';
    private 'SmallURL320' => 'Varchar(255)';
    private 'SmallHeight320' => 'Int';
    private 'SmallWidth320' => 'Int';
    private 'MediumURL' => 'Varchar(255)';
    private 'MediumHeight' => 'Int';
    private 'MediumWidth' => 'Int';
    private 'MediumURL640' => 'Varchar(255)';
    private 'MediumHeight640' => 'Int';
    private 'MediumWidth640' => 'Int';
    private 'MediumURL800' => 'Varchar(255)';
    private 'MediumHeight800' => 'Int';
    private 'MediumWidth800' => 'Int';
    private 'SquareURL' => 'Varchar(255)';
    private 'SquareHeight' => 'Int';
    private 'SquareWidth' => 'Int';
    private 'SquareURL150' => 'Varchar(255)';
    private 'SquareHeight150' => 'Int';
    private 'SquareWidth150' => 'Int';
    private 'LargeURL' => 'Varchar(255)';
    private 'LargeHeight' => 'Int';
    private 'LargeWidth' => 'Int';
    private 'LargeURL1600' => 'Varchar(255)';
    private 'LargeHeight1600' => 'Int';
    private 'LargeWidth1600' => 'Int';
    private 'LargeURL2048' => 'Varchar(255)';
    private 'LargeHeight2048' => 'Int';
    private 'LargeWidth2048' => 'Int';
    private 'ThumbnailURL' => 'Varchar(255)';
    private 'ThumbnailHeight' => 'Int';
    private 'ThumbnailWidth' => 'Int';
    private 'OriginalURL' => 'Varchar(255)';
    private 'OriginalHeight' => 'Int';
    private 'OriginalWidth' => 'Int';
    private 'TimeShiftHours' => 'Int';
    private 'PromoteToHomePage' => DBBoolean::class ;
    private 'Imported' => 'Boolean';
    private 'DigitalZoomRatio' => 'Float';
    private 'UploadUnixTimeStamp' => 'Int';
    private 'PerceptiveHash' => 'Varchar(64)';
    private 'Visible' => 'Boolean'


        //TODO - place id
    ];

    /*
 * s	small square 75x75
q   large square 150x150
t   thumbnail, 100 on longest side
m   small, 240 on longest side
n   small, 320 on longest side
-   medium, 500 on longest side
z   medium 640, 640 on longest side
c   medium 800, 800 on longest side†
b   large, 1024 on longest side*
h   large 1600, 1600 on longest side†
k   large 2048, 2048 on longest side†
o   original image, either a jpg, gif or png, depending on source format
 */


    private static $belongs_many_many = [
        'FlickrSets' => FlickrSet::class
    ];

    // this one is what created the database FlickrPhoto_FlickrTagss
    private static $many_many = [
        'FlickrTags' => FlickrTag::class;
    private 'FlickrBuckets' => FlickrBucket::class ];

    private static $has_many = [
        'Exifs' => FlickrExif::class
    ];

    private static $has_one = [
        'LocalCopyOfImage' => Image::class;
    private 'Photographer' => FlickrAuthor::class ];

    private static $summary_fields = [
        'Thumbnail' => 'Thumbnail';
    private 'Title' => 'Title';
    private 'Visible' => 'Visible'
    ];

    private static $default_sort = 'TakenAt';

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
        return "http://www.flickr.com/photos/{$this->Photographer()->PathAlias}/{$this->FlickrID}/";
    }


    public function AbsoluteLink()
    {
        return $this->Link();
    }


    /*
        Mark image as dirty upon a save
        */
    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        $tagHelper = new FlickrTagHelper();
        $quickTags = $tagHelper->createOrFindTags($this->QuickTags);

        $this->FlickrTags()->addMany($quickTags);
        if ($this->LargeWidth > 0) {
            $this->AspectRatio = ($this->LargeHeight) / ($this->LargeWidth);
        }

        $this->IsDirty = !$this->KeepClean
            ? true
            : false;
    }


    public function getCMSFields()
    {
        Requirements::css('weboftalent/flickr:dist/admin/client/css/flickredit.css');
        Requirements::javascript('weboftalent/flickr:dist/admin/client/js/flickredit.js');

        // this worked in SS3, but not SS4
        // @todo Figure out how to get the ID of set, other than URL hacking
        $flickrSetID = Controller::curr()->request->param('ID');

        $fields = new FieldList();


        $fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
        $mainTab->setTitle(\_t('SiteTree.TABMAIN', "Main"));


        $forTemplate = new ArrayData([
                'FlickrPhoto' => $this,
                //SS ID
                'FlickrSetID' => $flickrSetID
            ]);
        $imageHtml = $forTemplate->renderWith('Includes/FlickrImageEditing');


        $lfImage = new LiteralField('FlickrImage', $imageHtml);
        $fields->addFieldToTab('Root.Main', $lfImage);
        $fields->addFieldToTab('Root.Main', new TextField('Title', 'Title'));
        $fields->addFieldToTab('Root.Main', new TextareaField('Description', 'Description'));
        $fields->addFieldToTab('Root.Main', new CheckboxField('Visible', 'Visible?'));

        // only show a map for editing if no sets have geolock on them
        $lockgeo = false;
        foreach ($this->FlickrSets() as $set) {
            if ($set->LockGeo === true) {
                $lockgeo = true;

                break;
            }
        }

        if (!$lockgeo) {
            $fields->addFieldToTab(
                "Root.Location",
                $mapField = new LatLongField(
                    [
                     new TextField('Lat', 'Latitude'),
                     new TextField('Lon', 'Longitude'),
                     new TextField('ZoomLevel', 'Zoom')
                    ],
                    [ 'Address' ],
                ),
            );


            $guidePoints = [];

            foreach ($this->FlickrSets() as $set) {
                foreach ($set->FlickrPhotos()->where('Lat != 0 and Lon != 0') as $fp) {
                    if (($fp->Lat === 0) || ($fp->Lon === 0)) {
                        continue;
                    }

                    \array_push($guidePoints, [
                        'latitude' => $fp->Lat,
                        'longitude' => $fp->Lon
                    ]);
                }
            }

            if (\count($guidePoints) > 0) {
                $mapField->setGuidePoints($guidePoints);
            }
        }

        // quick tags, faster than the grid editor - these are processed prior to save to create/assign tags
        $fields->addFieldToTab('Root.Main', new TextField('QuickTags', 'Enter tags here separated by commas'));

        //->addComponent( new GridFieldSortableRows( 'Value' ) );
        $gridConfig = GridFieldConfig_RelationEditor::create();
        $gridConfig->getComponentByType(GridFieldAddExistingAutocompleter::class)->setSearchFields([ 'Value','RawValue' ]);
        $gridField = new GridField("Tags", "List of Tags", $this->FlickrTags(), $gridConfig);
        $fields->addFieldToTab("Root.Main", $gridField);

        $fields->addFieldToTab("Root.Main", new CheckboxField('PromoteToHomePage', 'Promote to Home Page'));

        return $fields;
    }


    public function AdjustedTime()
    {
        // @todo Is this required?
        return 'FP ADJ TIME '.$this->TimeShiftHours;
    }


    public function getThumbnail()
    {
        $width = $this->LargeWidth;
        $height = $this->LargeHeight;

        if ($width < $height) {
            $width = \round($width*683/$height);
            $height = 683;
        }

        return DBField::create_field(
            'HTMLVarchar',
            '<img class="flickrThumbnail" data-flickr-preview-url="' . $this->ProtocolAgnosticLargeURL() .
            '" data-flickr-preview-width=' . $width . ' ' .
            ' data-flickr-preview-height=' . $height . ' ' .
            ' src="' . $this->ThumbnailURL . '"  data-flickr-thumbnail-url="' .
            $this->ThumbnailURL . '"/>',
        );
    }


    public function EffectiveFocalLength35mm()
    {
        $fl = $this->FocalLength35mm;
        if ($this->DigitalZoomRatio) {
            $fl = \round($fl * $this->DigitalZoomRatio);
        }

        return $fl;
    }


    public function HasGeo()
    {
        return $this->Lat !== 0 || $this->Lon !== 0;
    }


    public function HasGeoEng()
    {
        return $this->HasGeo()
            ? 'Yes'
            : 'No';
    }


    /**
     * Convert URLs of the form https://live.staticflickr.com/65535/48204433551_63a99226e7_t.jpg to
     * 48204433551_63a99226e7_t, as this used for sprite CSS purposes
     */
    public function CSSSpriteFileName()
    {
        $splits = \explode('/', $this->SmallURL);
        $filename = \end($splits);
        $filename = \str_replace('.jpg', '', $filename);

        return $filename;
    }


    public function SpriteNumber($position)
    {
        $imagesPerSprite = Config::inst()->get(FlickrSetPage::class, 'images_per_sprite');

        return \floor($position/$imagesPerSprite);
    }





    /*
    Update Flickr with details held in SilverStripe
    @param $descriptionSuffix The suffix to be appended to the photographic description
    */
    public function writeToFlickr($descriptionSuffix): void
    {
        $helper = new FlickrUpdateMetaHelper();
        $helper->writePhotoToFlickr($this, $descriptionSuffix);
    }


    private function stripProtocol($url)
    {
        $url = \str_replace('http:', '', $url);
        $url = \str_replace('https:', '', $url);

        return $url;
    }
    // thumbnail related



    private function initialiseFlickrOBSOLE(): void
    {
        if (isset($this->f)) {
            return;
        }

        // get flickr details from config
        $key = Config::inst()->get('FlickrController', 'api_key');
        $secret = Config::inst()->get('FlickrController', 'secret');
        $access_token = Config::inst()->get('FlickrController', 'access_token');

        $this->f = new phpFlickr($key, $secret);

        //Fleakr.auth_token    = ''
        $this->f->setToken($access_token);
    }
}
