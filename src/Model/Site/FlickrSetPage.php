<?php
namespace Suilven\Flickr\Model\Site;

use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

/**
 * Class \Suilven\Flickr\Model\Site\FlickrSetPage
 *
 * @property int $TimeShiftHours
 * @property string $Description
 * @property boolean $IsDirty
 * @property string $FirstPictureTakenAt
 * @property int $FlickrSetForPageID
 * @method \Suilven\Flickr\Model\Flickr\FlickrSet FlickrSetForPage()
 */
class FlickrSetPage extends \Page
{
    private static $table_name = 'FlickrSetPage';

    private static $has_one = [
        'FlickrSetForPage' => FlickrSet::class
    ];

    private static $db = [
        'TimeShiftHours' => 'Int',
        'Description' => 'HTMLText',
            // flag to indicate requiring a flickr API update
        'IsDirty' => DBBoolean::class,

        //FIXME This is duplicated data, but problems wtih the join for ordering flickr set pages via flickr sets
        'FirstPictureTakenAt' => 'Datetime'
    ];

    public function getFlickrImageCollectionForPage()
    {
        return $this->FlickrSetForPage();
    }

    public function getPortletTitle()
    {
        return $this->Title;
    }


    /**
     * An accessor method for an image for a portlet
     * @example
     * <code>
     *  return $this->NewsItemImage;
     * </code>
     *
     * @return string
     */
    public function getPortletImage()
    {
        return $this->FlickrSetForPage()->PrimaryFlickrPhoto();
    }


    /**
     * An accessor for text associated with the portlet
     * @example
     * <code>
     * return $this->Summary
     * </code>
     *
     * @return string
     */
    public function getPortletCaption()
    {
        return $this->Descripton;
    }

    public function ColumnLayout()
    {
        return 'layout1col';
    }


    /* Get the main image of the set
    FIXME: Use flickr option, and make more efficient
    */
    public function MainImage()
    {
        $resultID = $this->AllChildren()->First()->FlickrPhotoForPageID;
        $result = DataObject::get_by_id('FlickrPhoto', $resultID);
        return DataObject::get_by_id(Image::class, $result->LocalCopyOfImageID);
    }


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();


        // this is what shows int he tab with the table in it

        /*
        $tablefield = new HasOneComplexTableField(
            $this,
            'FlickrSetForPage',
            'FlickrSet',
            array(
                'Title' => 'Title'
            ),
            'getCMSFields_forPopup'
        );

        $tablefield->setParentClass('FLickrSetPage');
        */

        $gridConfig = GridFieldConfig_RelationEditor::create()->addComponent(new GridFieldSortableRows('SortOrder'));
        $gridConfig->getComponentByType(GridFieldAddExistingAutocompleter::class)->setSearchFields(array( 'URL', 'Title', 'Description' ));
        //$gridField = new GridField( "Links", "List of Links:", $this->Links()->sort( 'SortOrder' ), $gridConfig );
        //$fields->addFieldToTab( "Root.Links", $gridField );


        $fields->addFieldToTab('Root.Main', new HTMLEditorField('Description', 'Description'), 'Content');
        //fields->addFieldToTab( 'Root.FlickrSet', $tablefield );


        //$dropdown = new DropdownField('FlickrSetFolderID', 'Flickr Set Folder', FlickrSetFolder::get()->map('ID','Title');
        /*
        $dropdown->setEmptyString('-- Please Select One --');
        $fields->addFieldToTab('Root.ParentGallery',
            $dropdown
        );
        */
        return $fields;
    }


    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $parentFolderID = $this->ParentFolderID;
        if ($parentFolderID) {
            $this->ParentID = $parentFolderID;
        }

        // FIXME
        $this->Dirty = true;
    }

}
