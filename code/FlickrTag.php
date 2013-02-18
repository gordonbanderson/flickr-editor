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


    /*
    Static helper
    */
    public static function CreateOrFindTags($csv) {
      $result = new ArrayList();
      error_log("\n\n ++++ CREATING TAGS FROM ".$csv.'++++');

      if (trim($csv) == '') {
        return $result; // ie empty array
      }
      $tags = explode(',', $csv);
      foreach($tags as $tagName) {
        $tagName = trim($tagName);
        if (!$tagName) {
          continue;
        }
        error_log("Checking for tag:".$tagName);
        $ftag = DataList::create('FlickrTag')->where("Value='".strtolower($tagName)."'")->first();
        if (!$ftag) {
          $ftag = FlickrTag::create();
          $ftag->RawValue = $tagName;
          $ftag->Value  = strtolower($tagName);
          error_log("Set value to ".$tagName);
          $ftag->write();
          error_log("Created tag:".$tagName);
        } else {
          error_log("Found tag:".$tagName);
        }

        $result->add($ftag);

      }

      error_log("RESULT:".print_r($result,1));

      return $result;
    }

}


?>