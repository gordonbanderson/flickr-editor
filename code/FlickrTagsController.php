<?php
/**
 *  // _config.php
 *	Director::addRules(10, array(
 *	'emptycache' => 'EmptyCacheController',
 *	));
 */



class FlickrTagsController extends Page_Controller {


	static $allowed_actions = array(
		'index',
		'photo',
		'photos'
	);


	function ColumnLayout() {
		return 'layout1col';
	}

	public function init() {
		parent::init();

		// Requirements, etc. here
	}

	public function index() {
		return array();
	}


	/*
	Show photos for a given tag
	*/
	public function photo() {
		error_log("TAG/PHOTO");
		$tagValue = Director::URLParam('ID');
		error_log("TAG VALUE:".$tagValue);
		$this->Title = "Photos tagged '".$tagValue."'";
		$tag = DataObject::get_one('Tag', "Value='".$tagValue."'");
		error_log("TAG:".$tag);
		$this->TagValue = $tagValue;
		$this->Tag = $tag;

		$result = array();
		if ($tag) {
			error_log("Getting photos");
			$result = $tag->FlickrPhotos();
			$this->FlickrPhotos = $tag->FlickrPhotos();

		}

		error_log("PHOTOS:".$result);
		return array();

	}

	public function PhotoKey() {
		$key ='tagphoto_'.$ID;
		error_log("KEY:".$key);
		return $key;
	}



	/* Return all tags for rendering in a cloud */
	public function photos() {
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

		//error_log(print_r($result->first(),1));



		$tagCloud = singleton('Tag')->buildDataObjectSet($result);
		foreach ($tagCloud as $tagV) {
			// font size in pixels
			$tagV->Amount= 10 + round(32*$tagV->Amount / $maxCount);
		}

		$this->TagCloud = $tagCloud;


		return array();
	}
}
