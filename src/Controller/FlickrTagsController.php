<?php
namespace Suilven\Flickr\Controller;

use SilverStripe\Control\Director;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;

class FlickrTagsController extends \PageController
{
    private static $allowed_actions = array(
        'index',
        'photo',
        'photos'
    );


    public function ColumnLayout()
    {
        return 'layout1col';
    }

    public function init()
    {
        parent::init();

        // Requirements, etc. here
    }

    public function index()
    {
        return array();
    }


    /*
    Show photos for a given tag
    */
    public function photo()
    {
        $tagValue = Director::URLParam('ID');
        $this->Title = "Photos tagged '".$tagValue."'";
        $tag = DataObject::get_one('Tag', "Value='".$tagValue."'");
        $this->TagValue = $tagValue;
        $this->Tag = $tag;

        $result = array();
        if ($tag) {
            $result = $tag->FlickrPhotos();
            $this->FlickrPhotos = $tag->FlickrPhotos();
        }

        return array();
    }

    public function PhotoKey()
    {
        $key ='tagphoto_'.$ID;
        return $key;
    }



    /* Return all tags for rendering in a cloud */
    public function photos()
    {
        $this->Tags = DataObject::get('Tag');
        $this->Title = 'Tags for photos';

        $maxCount  = DB::query("SELECT COUNT(TagID) as ct FROM FlickrPhoto_FlickrTags Group by TagID Order by ct desc limit 1")->value();

        $sql = "select t.ID, t.ClassName, count(TagID) as Amount, t.Value
				From FlickrPhoto_FlickrTags ft
				inner join Tag t
				on t.ID = ft.TagID
				group by TagID
				order by t.Value
		;";

        $result = DB::query($sql);

        $tagCloud = singleton('Tag')->buildDataObjectSet($result);
        foreach ($tagCloud as $tagV) {
            // font size in pixels
            $tagV->Amount= 10 + round(32*$tagV->Amount / $maxCount);
        }

        $this->TagCloud = $tagCloud;
        return array();
    }
}
