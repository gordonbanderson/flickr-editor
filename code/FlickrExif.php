<?php
/**
* Only show a page with login when not logged in
*/
class FlickrExif extends DataObject {  


  static $db = array(
  'TagSpace' => 'Varchar',
  'Tag' => 'Varchar',
  'Label' => 'Varchar',
  'Raw' => 'Varchar',
  'TagSpaceID' => 'Int'



  );


   static $belongs_many_many = array(
      'FlickrPhotos' => 'FlickrPhoto'
   );

   static $has_one = array(
        'FlickrPhoto' => 'FlickrPhoto'
    );



   function getCMSFields_forPopup() {
        $fields = new FieldSet();
         
        $fields->push( new TextField( 'Title', 'Title' ) );
        $fields->push( new TextField( 'Description' ) );
         
        return $fields;
    }



  /*
--------------------+----------------+------+-----+---------+----------------+
| Field               | Type           | Null | Key | Default | Extra          |
+---------------------+----------------+------+-----+---------+----------------+
| id                  | int(11)        | NO   | PRI | NULL    | auto_increment |
| flickr_id           | varchar(255)   | YES  | UNI | NULL    |                |
| title               | varchar(255)   | YES  |     | NULL    |                |
| description         | text           | YES  |     | NULL    |                |
| taken_at            | datetime       | YES  | MUL | NULL    |                |
| position            | int(11)        | YES  |     | NULL    |                |
| created_at          | datetime       | YES  |     | NULL    |                |
| updated_at          | datetime       | YES  |     | NULL    |                |
| flickr_last_updated | datetime       | YES  |     | NULL    |                |
| latitude            | decimal(15,10) | YES  |     | NULL    |                |
| longitude           | decimal(15,10) | YES  |     | NULL    |                |
| zoom_level          | int(11)        | YES  |     | NULL    |                |
| timezone_name_id    | int(11)        | YES  |     | NULL    |                |
| permalink           | varchar(255)   | YES  | UNI | NULL    |                |
| orientation_id      | int(11)        | YES  |     | NULL    |      
  */
}



?>