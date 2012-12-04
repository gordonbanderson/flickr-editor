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


  


   static $belongs_many_many = array(
      'FlickrPhotos' => 'FlickrPhoto'
   );



 public function NormaliseCount($c) {
    error_log("normalise ".$c);
        return log(doubleval($c),2);
    }



   function getCMSFields_forPopup() {
        $fields = new FieldSet();
         
        $fields->push( new TextField( 'Value' ) );
        $fields->push( new TextField( 'RawValue' ) );
         
        return $fields;
    }

}


?>