<?php
/**
 * Defines the FlickrSetFolder page type
 */
class FlickrSetFolder extends Page {

   
   static $allowed_children = array('FlickrSetPage', 'FlickrSetFolder');

   static $db = array(
    'PromoteToHomePage' => 'Boolean'
   );

   static $has_one = array(
      'CoverPhoto' => 'Image',
   );


   function getCMSFields() {
    $fields = parent::getCMSFields();
    $fields->addFieldToTab("Root.CoverPhoto", new UploadField('CoverPhoto'));

    
    $fields->renameField("Content", "Brief Description");
    $fields->addFieldToTab("Root.HomePage", new CheckboxField('PromoteToHomePage', 'Promote to Home Page'));

   return $fields;
  }
  
}
 
class FlickrSetFolder_Controller extends Page_Controller {
  public function FlickrSetsNewestFirst() {
    return DataList::create('FlickrSetPage')->where('ParentID = '.$this->ID)->sort('FirstPictureTakenAt desc');
  }

  public function FlickrSetFoldersNewestFirst() {
    return DataList::create('FlickrSetFolder')->where('ParentID = '.$this->ID)->sort('Created desc');
  }
}
 
?>