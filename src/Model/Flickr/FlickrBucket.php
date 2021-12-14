<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Flickr;

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
use SilverStripe\ORM\FieldType\DBField;
use Smindel\GIS\Forms\MapField;
use Suilven\Flickr\Helper\FlickrTagHelper;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class \Suilven\Flickr\Model\Flickr\FlickrBucket
 *
 * @property string $Title
 * @property string $Description
 * @property float $Lat
 * @property float $Lon
 * @property int $Accuracy
 * @property int $ZoomLevel
 * @property string $TagsCSV
 * @property int $FlickrSetID
 * @method \Suilven\Flickr\Model\Flickr\FlickrSet FlickrSet()
 * @method \SilverStripe\ORM\ManyManyList FlickrTags()
 * @method \SilverStripe\ORM\ManyManyList FlickrPhotos()
 */
class FlickrBucket extends DataObject
{
    /** @var string */
    private static $table_name = 'FlickrBucket';

    /** @var array<string,string> */
    private static $db = [
        'Title' => 'Varchar(255)',
        'Description' => 'Text',

        // use precision 15 and 10 decimal places for coordinates
  //      'Lat' => 'Decimal(18,15)',
  //      'Lon' => 'Decimal(18,15)',

        'Location' => 'Geometry',


        'Accuracy' => 'Int',
        'ZoomLevel' => 'Int',
        'TagsCSV' => 'Varchar',
    ];

    /** @var array<string,string> */
    private static $has_one = ['FlickrSet' => FlickrSet::class];

    /** @var array<string> */
    private static $summary_fields = ['Title', 'ImageStrip' => 'ImageStrip'];

    /** @var array<string,string> */
    private static $belongs_many_many = [
        'FlickrPhotos' => FlickrPhoto::class,
        'FlickrTags' => FlickrTag::class,
    ];

    /** @var array<string,string> */
    private static $many_many = ['FlickrTags' => FlickrTag::class];

    /** @var bool  */
    private $virginal = true;

    public function getCMSFields(): FieldList
    {
        $fields = new FieldList();

        $fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
        $mainTab->setTitle(\_t('SiteTree.TABMAIN', "Main"));

        $lf = new LiteralField(
            '<p>Instructions',
            'All of the images in this bucket will have the same information that you ' .
            'enter here</p>'
        );
        $fields->push($lf);

        $fields->addFieldToTab('Root.Main', $lf);
        $fields->addFieldToTab('Root.Main', new TextField(
            'Title',
            'Bucket Title'
        ));
        $fields->addFieldToTab('Root.Main', new TextareaField(
            'Description',
            'Bucket Description'
        ));

        // quick tags, faster than the grid editor - these are processed prior to save to
        // create/assign tags
        $fields->addFieldToTab('Root.Main', new TextField(
            'QuickTags',
            'Quick tags - enter tags here separated by commas'
        ));

        $lf2 = new LiteralField('ImageStrip', $this->getImageStrip()->HTML());
        $fields->push($lf2);

        $lockgeo = $this->GeoLocked();

        if (!$lockgeo) {
            $mapField = MapField::create('Location')
                ->setControl('polyline', false)
                ->enableMulti(true);

            /*
            $guidePoints = [];
            foreach ($this->FlickrSet()->FlickrPhotos()->where('Lat != 0 and Lon != 0') as $fp) {
                if (($fp->Lat === 0) || ($fp->Lon === 0)) {
                    continue;
                }

                \array_push($guidePoints, [
                    'latitude' => $fp->Lat,
                    'longitude' => $fp->Lon,
                ]);
            }

            if (\count($guidePoints) > 0) {
               /$mapField->setGuidePoints($guidePoints);
            }
            */

            $fields->addFieldToTab('Root.Location', $mapField);
        }


        //->addComponent( new GridFieldSortableRows( 'Value' ) );
        $gridConfig = GridFieldConfig_RelationEditor::create();

        /** @var GridFieldAddExistingAutocompleter $autocompleter */
        $autocompleter = $gridConfig->getComponentByType(GridFieldAddExistingAutocompleter::class);
        $autocompleter->setSearchFields([
            'Value',
            'RawValue',
        ]);
        $gridField = new GridField("Tags", "List of Tags", $this->FlickrTags(), $gridConfig);

        // keep in the main tab to avoid wasting time tab switching
        $fields->addFieldToTab("Root.Main", $gridField);

        return $fields;
    }


    /** @return bool iff the bucket contains photographs from sets that are not geolocked */
    public function GeoLocked(): bool
    {
        // only show a map for editing if no sets have geolock on them
        $lockgeo = false;
        foreach ($this->FlickrPhotos() as $fp) {
            foreach ($fp->FlickrSets() as $set) {
                if ($set->LockGeo) {
                    $lockgeo = true;

                    break;
                }
            }
            if ($lockgeo) {
                break;
            }
        }

        return $lockgeo;
    }


    /** @return \SilverStripe\ORM\FieldType\DBField field containing HTML showing a strip of images */
    public function getImageStrip(): DBField
    {
        $html = '<div class="imageStrip">';
        foreach ($this->FlickrPhotos() as $photo) {
            $html .= '<img class="flickrThumbnail" ';
            $html .= 'src="' . $photo->ThumbnailURL . '" ';
            $html .= 'data-flickr-thumbnail-url="' . $photo->ThumbnailURL . '" ';
            $html .= 'data-flickr-medium-url="' . $photo->MediumURL . '"/>';
        }
        $html .= "</div>";

        return DBField::create_field('HTMLText', $html);
    }


    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        $tagHelper = new FlickrTagHelper();
        $quickTags = $tagHelper->createOrFindTags($this->QuickTags);
        $this->FlickrTags()->addMany($quickTags);

        if (($this->ID !== 0) && ($this->FlickrPhotos()->count() > 0)) {
            if ($this->Title === '') {
                // the null case has already been checked on
                // @phpstan-ignore-next-line
                $this->Title = $this->FlickrPhotos()->first()->TakenAt . ' - ' . $this->FlickrPhotos()->last()->TakenAt;
            }
        } else {
            $this->virginal = true;
        }
    }


    /*
    Update all the photographs in the bucket with the details of the bucket
    */
    public function onAfterWrite(): void
    {
        parent::onAfterWrite();

        // if the title is blank resave in order to create a time from / time to title
        // this needs checked here as on before write cannot do this when the bucket has not been saved
        if ($this->Title === '' && !isset($this->virginal)) {
            $this->write();
        }

        $lockgeo = $this->GeoLocked();

        foreach ($this->FlickrPhotos() as $fp) {
            $fp->Title = $this->Title;
            $description = $this->Description;
            //$description = $description ."\n\n".$this->FlickrSet()->ImageFooter;
            //$description = $description ."\n\n".Controller::curr()->SiteConfig()->ImageFooter;
            $year = \substr('' . $fp->TakenAt, 0, 4);
            $description = \str_replace('$Year', $year, $description);
            $fp->Description = $description;

            if (!$lockgeo) {
                $fp->Lat = $this->Lat;
                $fp->Lon = $this->Lon;

                if ($this->Lat === null) {
                    $fp->Lat = 0;
                }

                if ($this->Lon === null) {
                    $fp->Lon = 0;
                }
            }

            $fp->FlickrTags()->addMany($this->FlickrTags());
            $fp->write();
        }
    }
}
