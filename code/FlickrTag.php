<?php
/**
* Only show a page with login when not logged in
*/
class FlickrTag extends DataObject {  


  static $db = array(
  'Value' => 'Varchar',
  'FlickrID' => 'Varchar',
  'RawValue' => 'HTMLText'
  );

  static $display_fields = array(
    'RawValue'
  );


  static $searchable_fields = array(
    'Value',
    'RawValue',
    'FlickrID'
  );

  static $summary_fields = array(
    'Value',
    'RawValue',
    'FlickrID'
  );


  


   static $belongs_many_many = array(
      'FlickrPhotos' => 'FlickrPhoto'
   );



 public function NormaliseCount($c) {
    error_log("normalise ".$c);
        return log(doubleval($c),2);
    }



   function getCMSFields() {
        $fields = new FieldList();
         
        $fields->push( new TextField( 'Value' ) );
        $fields->push( new TextField( 'RawValue' ) );
         
        return $fields;
    }

    // this is required so the grid field autocompleter returns readable entries after searching
    function Title() {
      return $this->RawValue;
    }

}


?>