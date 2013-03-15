<?php

class FlickrExportController extends Page_Controller {

    static $allowed_actions = array(
    	'toJson'
    );

    public function index() {
    	return 'wibble';
    }


     public function toJson() {
        error_log("+++ SET TO JSON +++");
        $flickrSetID = $this->request->param( 'ID' );
        $flickrSet = DataList::create('FlickrSet')->where('FlickrID = '.$flickrSetID)->first();
        error_log("FLICKR SET:".$flickrSet->Title);
         $images = array();
        foreach ($flickrSet->FlickrPhotos() as $fp) {
            $image = array();

            $image['Lat'] = $fp->Lat;
            $image['Lon'] = $fp->Lon;

            $image['ThumbnalURL'] = $fp-> ThumbnalURL;
            $image['MediumURL'] = $fp-> MediumURL;
            $image['SmallURL'] = $fp-> SmallURL;
            $image['LargeURL'] = $fp-> LargeURL;
            $image['Title'] = $fp-> Title;
            $image['Description'] = $fp-> Title;
            array_push($images,$image);
        }

        error_log(json_encode($images));

            file_put_contents("/tmp/output.json", stripslashes(json_encode($images)));

    }
}
?>