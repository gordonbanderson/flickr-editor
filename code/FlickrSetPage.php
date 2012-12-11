<?php
 
class FlickrSetPage extends Page {
 
    static $has_one = array(
        'FlickrSetForPage' => 'FlickrSet'
    );


    static $db = array(
        'TimeShiftHours' => 'Int',
        'Description' => 'HTMLText',
            // flag to indicate requiring a flickr API update
        'IsDirty' => 'Boolean',
    );


    static $sphinx = array(
        "search_fields" => array("Title", "Description", "Content"),
        "filter_fields" => array(),
        "index_filter" => '"ShowInSearch" = 1',
        "sort_fields" => array("Title")
        
    );


    static $many_many = array(
      'MapLayers' => 'MapLayer'
   );



    /*

update FlickrSetPage set Description = FlickrSet.Description where FlickrSet.ID = FlickrSetPage.FlickrSetForPageID;

update FlickrSetPage set Description = 'wibble';
update FlickrSetPage set Description = (select Description from FlickrSet where FlickrSet.ID = FlickrSetPage.FlickrSetForPageID);

 'filterable_many_many' => '*',
    'extra_many_many' => array(
        'documents' => 'select (' . SphinxSearch::unsignedcrc('SiteTree') . '<<32) | PageID AS id, DocumentID AS Documents FROM Page_Documents')
    
    */


     function ColumnLayout() {
        return 'layout1col';
    }

    /* Get the main image of the set
    FIXME: Use flickr option, and make more efficient
    */
    function MainImage() {
        error_log("Main image");
        $resultID = $this->AllChildren()->First()->FlickrPhotoForPageID;
        $result = DataObject::get_by_id('FlickrPhoto', $resultID);
        error_log("RES:".$result);
        
        $result = DataObject::get_by_id('Image', $result->LocalCopyOfImageID);

        return $result;
    }


  



    function getCMSFields() {
        error_log("GET CMS FIELDS FLICKR SET PAGE");
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

        $gridConfig = GridFieldConfig_RelationEditor::create()->addComponent( new GridFieldSortableRows( 'SortOrder' ) );
    $gridConfig->getComponentByType( 'GridFieldAddExistingAutocompleter' )->setSearchFields( array( 'URL', 'Title', 'Description' ) );
    $gridField = new GridField( "Links", "List of Links:", $this->Links()->sort( 'SortOrder' ), $gridConfig );
    $fields->addFieldToTab( "Root.Links", $gridField );
   
   
        $fields->addFieldToTab( 'Root.FlickrSet', new TextField('TimeShiftHours', 'Time Shift') );
        //fields->addFieldToTab( 'Root.FlickrSet', $tablefield );



        error_log("T1");
        $folders = $this->getFlickrSetFolders();

       



        $bts = array(
                '-- N/A --', 'Mochit', 'Ratachewi', 'Ekemai', 'On Nut'
                );

        $fields->addFieldToTab("Root.Transport", new DropdownField("BTS", $this->owner->fieldLabel('Nearest BTS Station'), $bts));


        $oData = DataObject::get("FlickrSetFolder");
        if ($oData) { 
           /*
            $folders = $oData->toDropDownMap('ID','Title', '-- Please Select --'); 
            $dropdown = new DropdownField('ParentFolderID', 'Flickr Set Folder', $folders, $this->ParentID);
            $fields->addFieldToTab('Root.ParentGallery', 
                $dropdown
            );
        */

        }


   /*
    $map_layer_records = new ManyManyDataObjectManager(
      $this,
      'MapLayers',
      'MapLayer',
      array('Title' => 'Title'),
      'getCMSFields_forPopup'
    );


  $fields->addFieldToTab("Root.MapOverlays",$map_layer_records);
*/


        error_log("T2");




         
        return $fields;
    }



    function getFlickrSetFolders() {
        error_log("Getting flickr set folders");
        if($Pages = DataObject::get('FlickrSetFolder'))
        {
        return $Pages->map('ID', 'Title', 'Please Select');
        }
        else
        {
        return array('No Objects found');
        }
    }
    
    
    
    function onBeforeWrite() {
        parent::onBeforeWrite();

        //error_log(print_r($this->record,1));
        $parentFolderID = $this->ParentFolderID;
        if ($parentFolderID) {
            $this->ParentID = $parentFolderID;
        }

        // FIXME
        $this->Dirty = true;

       error_log("PARENT FOLDER ID:".$this->ParentFolderID);
    }

   

    /*
select * from 
    */

 
 
}
class FlickrSetPage_Controller extends Page_Controller {
    


    function FlickrPhotos() {
        if (!$this->FlickrPhotos) {
            
            $images = $this->FlickrSetForPage()->FlickrPhotos();

            error_log("T1 image size:".$images->count());
/*
            foreach ($images as $key => $fp) {
                $fpp = $images[$key];
                $fp->TakenAt = strtotime($fp->TakenAt)+3600*($this->TimeShiftHours);
                error_log("CHECKING IMAGE ".$fp->Title);
            }
*/
            $this->FlickrPhotos = $images;
            error_log("T2 image size:".$images->count());
            error_log("T3 image size:".$this->FlickrPhotos->count());


        }

        error_log("IMAGE COUNT:".$this->FlickrPhotos->count());
        return $this->FlickrPhotos;
    }


    function _HasCoordinates() {
        $hasGeo = false;
        foreach ($this->FlickrPhotos() as $position => $fp) {
            if ($fp->Latitude != 0) {
                $hasGeo = true;
                break;
            }
        }

        return $hasGeo;
    }

   
}

?>