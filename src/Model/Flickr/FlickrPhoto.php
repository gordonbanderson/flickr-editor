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
use Smindel\GIS\Forms\MapField;
use Suilven\Flickr\Helper\FlickrTagHelper;
use Suilven\Flickr\Helper\FlickrUpdateMetaHelper;
use Suilven\Flickr\Model\Site\FlickrSetPage;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

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
 * @method \SilverStripe\ORM\DataList Exifs()
 * @method \SilverStripe\ORM\ManyManyList FlickrTags()
 * @method \SilverStripe\ORM\ManyManyList FlickrBuckets()
 * @method \SilverStripe\ORM\ManyManyListFlickrSets()
 */
class FlickrPhoto extends DataObject
{
    /** @var string */
    private static $table_name = 'FlickrPhoto';

    /** @var array<string,string> */
    private static $db = [
        'Title' => 'Varchar(255)',
        'FlickrID' => 'Varchar',
        'Description' => 'HTMLText',
        'TakenAt' => 'Datetime',
        'FlickrLastUpdated' => DBDate::class,
        'GeoIsPublic' => DBBoolean::class,
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
        'SmallURL320' => 'Varchar(255)',
        'SmallHeight320' => 'Int',
        'SmallWidth320' => 'Int',
        'SmallURL150' => 'Varchar(255)',
        'SmallHeight150' => 'Int',
        'SmallWidth150' => 'Int',
        'MediumURL' => 'Varchar(255)',
        'MediumHeight' => 'Int',
        'MediumWidth' => 'Int',
        'MediumURL640' => 'Varchar(255)',
        'MediumHeight640' => 'Int',
        'MediumWidth640' => 'Int',
        'MediumURL800' => 'Varchar(255)',
        'MediumHeight800' => 'Int',
        'MediumWidth800' => 'Int',
        'SquareURL' => 'Varchar(255)',
        'SquareHeight' => 'Int',
        'SquareWidth' => 'Int',
        'SquareURL150' => 'Varchar(255)',
        'SquareHeight150' => 'Int',
        'SquareWidth150' => 'Int',
        'LargeURL' => 'Varchar(255)',
        'LargeHeight' => 'Int',
        'LargeWidth' => 'Int',
        'LargeURL1600' => 'Varchar(255)',
        'LargeHeight1600' => 'Int',
        'LargeWidth1600' => 'Int',
        'LargeURL2048' => 'Varchar(255)',
        'LargeHeight2048' => 'Int',
        'LargeWidth2048' => 'Int',
        'ThumbnailURL' => 'Varchar(255)',
        'ThumbnailHeight' => 'Int',
        'ThumbnailWidth' => 'Int',
        'OriginalURL' => 'Varchar(255)',
        'OriginalHeight' => 'Int',
        'OriginalWidth' => 'Int',
        'TimeShiftHours' => 'Int',
        'PromoteToHomePage' => DBBoolean::class,
        'Imported' => 'Boolean',
        'DigitalZoomRatio' => 'Float',
        'UploadUnixTimeStamp' => 'Int',
        'PerceptiveHash' => 'Varchar(64)',
        'Visible' => 'Boolean',
        'Location' => 'Geometry',


        //TODO - place id
    ];

    /** @var bool */
    private static $geojsonservice = true;

    /** @var array<string,int> */
    private static $webmaptileservice = [
        'cache_ttl' => 3600,
    ];

    /** @var array<string,string> */
    private static $belongs_many_many = [
        'FlickrSets' => FlickrSet::class,
    ];

    // this one is what created the database FlickrPhoto_FlickrTagss
    /** @var array<string,string> */
    private static $many_many = [
        'FlickrTags' => FlickrTag::class,
        'FlickrBuckets' => FlickrBucket::class,
    ];

    /** @var array<string> */
    private static $has_many = [
        'Exifs' => FlickrExif::class,
    ];

    /** @var array<string,string> */
    private static $has_one = [
        'LocalCopyOfImage' => Image::class,
        'Photographer' => FlickrAuthor::class,
    ];

    /** @var array<string,string> */
    private static $summary_fields = [
        'Thumbnail' => 'Thumbnail',
        'Title' => 'Title',
        'Visible' => 'Visible',
    ];

    /** @var string */
    private static $default_sort = 'TakenAt';

    //helper methods to ensure that URLs are of the form //path/to/image so that http and https
    //  work with console warnings

    /** @return string|array<string> */
    public function ProtocolAgnosticLargeURL()
    {
        return $this->stripProtocol($this->LargeURL);
    }


    public function ProtocolAgnosticSmallURL(): string
    {
        return $this->stripProtocol($this->SmallURL);
    }


    public function ProtocolAgnosticMediumURL(): string
    {
        return $this->stripProtocol($this->MediumURL);
    }


    public function ProtocolAgnosticThumbnailURL(): string
    {
        return $this->stripProtocol($this->ThumbnailURL);
    }


    public function ProtocolAgnosticOriginalURL(): string
    {
        return $this->stripProtocol($this->OriginalURL);
    }


    public function HorizontalMargin(int $intendedWidth): int
    {
        //FIXME - is there a way to avoid a database call here?
        /** @var \Suilven\Flickr\Model\Flickr\FlickrPhoto $fp */
        $fp = DataObject::get_by_id(FlickrPhoto::class, $this->ID);

        $vh = ($intendedWidth - $fp->ThumbnailWidth) / 2;
        $vh = \intval(\round($vh));

        return $vh;
    }


    public function InfoWindow(): void
    {
        //return GoogleMapUtil::sanitize($this->renderWith('FlickrPhotoInfoWindow'));
    }


    public function VerticalMargin(int $intendedHeight): int
    {
        //FIXME - is there a way to avoid a database call here?
        $fp = DataObject::get_by_id(FlickrPhoto::class, $this->ID);

        $vm = ($intendedHeight - $fp->ThumbnailHeight) / 2;
        $vm = \intval(\round($vm));

        return $vm;
    }


    public function Link(): string
    {
        return "http://www.flickr.com/photos/{$this->Photographer()->PathAlias}/{$this->FlickrID}/";
    }


    public function AbsoluteLink(): string
    {
        return $this->Link();
    }


    /**
     * Mark an image as dirty when saving it, meaning it is out of sync with Flickr
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

        $this->IsDirty = !$this->KeepClean;
    }


    public function getCMSFields(): FieldList
    {
        Requirements::css('weboftalent/flickr:dist/admin/client/css/flickredit.css');
        Requirements::javascript('weboftalent/flickr:dist/admin/client/js/flickredit.js');

        // this worked in SS3, but not SS4
        // @todo Figure out how to get the ID of set, other than URL hacking
        $flickrSetID = Controller::curr()->getRequest()->param('ID');

        $fields = new FieldList();

        $fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
        $mainTab->setTitle(\_t('SiteTree.TABMAIN', "Main"));


        $forTemplate = new ArrayData([
            'FlickrPhoto' => $this,
            //SS ID
            'FlickrSetID' => $flickrSetID,
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

        /** @var \Smindel\GIS\Forms\MapField|null $mapField */
        $mapField = null;
        if (!$lockgeo) {
            $fields->addFieldToTab(
                'Root.Location',
                MapField::create('Location')
                    ->setControl('polyline', false)
                    ->enableMulti(true),
                'Content'
            );


            $guidePoints = [];

            foreach ($this->FlickrSets() as $set) {
                foreach ($set->FlickrPhotos()->where('Lat != 0 and Lon != 0') as $fp) {
                    if (($fp->Lat === 0) || ($fp->Lon === 0)) {
                        continue;
                    }

                    \array_push($guidePoints, [
                        'latitude' => $fp->Lat,
                        'longitude' => $fp->Lon,
                    ]);
                }
            }

            if (\count($guidePoints) > 0) {
                // @TODO Show guidepoints
               // $mapField->setGuidePoints($guidePoints);
            }
        }

        // quick tags, faster than the grid editor - these are processed prior to save to create/assign tags
        $fields->addFieldToTab('Root.Main', new TextField(
            'QuickTags',
            'Enter tags here separated by commas'
        ));

        //->addComponent( new GridFieldSortableRows( 'Value' ) );
        $gridConfig = GridFieldConfig_RelationEditor::create();

        /** @var \SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter $autocompleter */
        $autocompleter = $gridConfig->getComponentByType(GridFieldAddExistingAutocompleter::class);
        $autocompleter->setSearchFields(['Value', 'RawValue']);
        $gridField = new GridField("Tags", "List of Tags", $this->FlickrTags(), $gridConfig);
        $fields->addFieldToTab("Root.Main", $gridField);

        $fields->addFieldToTab("Root.Main", new CheckboxField(
            'PromoteToHomePage',
            'Promote to Home Page'
        ));

        return $fields;
    }


    /**
     * Get a thumbnail for CMS rendering
     */
    public function getThumbnail(): DBField
    {
        $width = $this->LargeWidth;
        $height = $this->LargeHeight;

        if ($width < $height) {
            $width = \round($width * 683 / $height);
            $height = 683;
        }

        $value = '<img class="flickrThumbnail" data-flickr-preview-url="' .
            $this->ProtocolAgnosticLargeURL() .
            '" data-flickr-preview-width=' . $width . ' ' .
            ' data-flickr-preview-height=' . $height . ' ' .
            ' src="' . $this->ThumbnailURL . '"  data-flickr-thumbnail-url="' .
            $this->ThumbnailURL . '"/>';

        return DBField::create_field(
            'HTMLVarchar',
            $value
        );
    }


    public function EffectiveFocalLength35mm(): int
    {
        $fl = $this->FocalLength35mm;
        if (isset($this->DigitalZoomRatio)) {
            $fl = \round($fl * $this->DigitalZoomRatio);
        }

        $fl = \intval($fl);

        return $fl;
    }


    /** @return bool true if the photo has geography */
    public function HasGeo(): bool
    {
        // @TODO These fields came from Mappable, test with altnerative GIS module
        // @phpstan-ignore-next-line
        return $this->Lat !== 0 || $this->Lon !== 0;
    }


    /**
     * Return Yes or No depending on whether the photo has geographic info
     *
     * @return string Either Yes or No
     */
    public function HasGeoEng(): string
    {
        return $this->HasGeo()
            ? 'Yes'
            : 'No';
    }


    /**
     * Convert URLs of the form https://live.staticflickr.com/65535/48204433551_63a99226e7_t.jpg to
     * 48204433551_63a99226e7_t, as this used for sprite CSS purposes
     */
    public function CSSSpriteFileName(): string
    {
        $splits = \explode('/', $this->SmallURL);
        $filename = (string) \end($splits);
        $filename = \str_replace('.jpg', '', $filename);

        return $filename;
    }


    public function SpriteNumber(int $position): int
    {
        $imagesPerSprite = Config::inst()->get(FlickrSetPage::class, 'images_per_sprite');

        return (int) \floor($position / $imagesPerSprite);
    }


    /**
     * Write data such as photo descriptions / titles back to Flickr
     *
     * @param string $descriptionSuffix A string to add to each description, e.g. Copyright info
     */
    public function writeToFlickr(string $descriptionSuffix): void
    {
        $helper = new FlickrUpdateMetaHelper();
        $helper->writePhotoToFlickr($this, $descriptionSuffix);
    }


    /**
     * Remove http and https from the begging of a URL
     */
    private function stripProtocol(string $url): string
    {
        $url = \str_replace('https:', '', $url);
        $url = \str_replace('http:', '', $url);

        return $url;
    }
}
