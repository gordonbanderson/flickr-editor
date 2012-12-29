<?php
/**
 * Defines the FlickrSetFolder page type
 */
class FlickrSetFolder extends Page {

   
   static $allowed_children = array('FlickrSetPage', 'FlickrSetFolder');

   static $has_one = array(
      'CoverPhoto' => 'Image',
   );


   function getCMSFields() {
    $fields = parent::getCMSFields();
    $fields->addFieldToTab("Root.CoverPhoto", new UploadField('CoverPhoto'));

    
    $fields->renameField("Content", "Brief Description");
   

    /*
    $fields->addFieldToTab('Root.Content.Main', new CalendarDateField('Date'), 'Content');
    $fields->addFieldToTab('Root.Content.Main', new TextField('Author'), 'Content');
    */
    return $fields;
  }
  
}
 
class FlickrSetFolder_Controller extends Page_Controller {
 
}
 
?>