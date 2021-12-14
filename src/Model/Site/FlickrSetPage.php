<?php declare(strict_types = 1);

namespace Suilven\Flickr\Model\Site;

use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\ORM\FieldType\DBBoolean;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class \Suilven\Flickr\Model\Site\FlickrSetPage
 *
 * @property int $TimeShiftHours
 * @property string $Description
 * @property bool $IsDirty
 * @property string $FirstPictureTakenAt
 * @property int $FlickrSetForPageID
 * @method \Suilven\Flickr\Model\Flickr\FlickrSet FlickrSetForPage()
 */
class FlickrSetPage extends \Page
{
    /** @var string */
    private static $table_name = 'FlickrSetPage';

    /** @var array<string> */
    private static $has_one = [
        'FlickrSetForPage' => FlickrSet::class,
    ];

    /** @var array<string,string> */
    private static $db = [
        'TimeShiftHours' => 'Int',
        'Description' => 'HTMLText',
            // flag to indicate requiring a flickr API update
        'IsDirty' => DBBoolean::class,

        //FIXME This is duplicated data, but problems wtih the join for ordering flickr set pages via flickr sets
        'FirstPictureTakenAt' => 'Datetime',
    ];

    public function getFlickrImageCollectionForPage(): FlickrSet
    {
        return $this->FlickrSetForPage();
    }


    public function getPortletTitle(): string
    {
        return $this->Title;
    }


    /**
     * An accessor method for an image for a portlet
     *
     * @example
     * <code>
     *  return $this->NewsItemImage;
     * </code>
     */
    public function getPortletImage(): string
    {
        return $this->FlickrSetForPage()->PrimaryFlickrPhoto()->ThumbnailURL;
    }


    /**
     * An accessor for text associated with the portlet
     *
     * @example
     * <code>
     * return $this->Summary
     * </code>
     */
    public function getPortletCaption(): string
    {
        // this looks like a PHPStan bug
        // @phpstan-ignore-next-line
        return $this->FlickrSetForPage()->Descripton;
    }


    public function ColumnLayout(): string
    {
        return 'layout1col';
    }

/*
    public function MainImage(): ?Image
    {
        $resultID = $this->AllChildren()->first()->FlickrPhotoForPageID;
        $result = DataObject::get_by_id(FlickrPhoto::class, $resultID);

        // @todo this does not exist - return DataObject::get_by_id(Image::class, $result->LocalCopyOfImageID);
    }
*/

    public function getCMSFields(): \SilverStripe\Forms\FieldList
    {
        $fields = parent::getCMSFields();

        $gridConfig = GridFieldConfig_RelationEditor::create()->addComponent(
            new GridFieldSortableRows('SortOrder')
        );

        /** @var \SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter $autocompleter */
        $autocompleter = $gridConfig->getComponentByType(GridFieldAddExistingAutocompleter::class);
        $autocompleter->setSearchFields(['URL', 'Title', 'Description']);

        $fields->addFieldToTab('Root.Main', new HTMLEditorField(
            'Description',
            'Description'
        ), 'Content');

        return $fields;
    }


    // @todo This method refers to ParentFolderID which does not exist.  Is this method still needed?
    /*
    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        $parentFolderID = $this->ParentFolderID;
        if ($parentFolderID) {
            $this->ParentID = $parentFolderID;
        }

        $this->IsDirty = true;
    }
    */
}
