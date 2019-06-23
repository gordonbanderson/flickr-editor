<?php
namespace Suilven\Flickr\Model\Site;

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

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





    /*

update FlickrSetPage set Description = FlickrSet.Description where FlickrSet.ID = FlickrSetPage.FlickrSetForPageID;

update FlickrSetPage set Description = 'wibble';
update FlickrSetPage set Description = (select Description from FlickrSet where FlickrSet.ID = FlickrSetPage.FlickrSetForPageID);

 'filterable_many_many' => '*',
    'extra_many_many' => array(
        'documents' => 'select (' . SphinxSearch::unsignedcrc('SiteTree') . '<<32) | PageID AS id, DocumentID AS Documents FROM Page_Documents')

    */


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


    public function BasicMap()
    {
        return $this->FlickrSetForPage()->BasicMap();
    }
}
