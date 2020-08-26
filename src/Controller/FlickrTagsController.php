<?php declare(strict_types = 1);

namespace Suilven\Flickr\Controller;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DB;
use Suilven\Flickr\Model\Flickr\FlickrTag;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class FlickrTagsController
 *
 * @package Suilven\Flickr\Controller
 */
class FlickrTagsController extends \PageController
{
    /** @var \SilverStripe\ORM\DataList|null */
    private $FlickrPhotos;

    /** @var \SilverStripe\ORM\DataList|null */
    private $Tags;

    /** @var string */
    private $Title;

    /** @var string|null */
    private $TagValue;

    /** @var \Suilven\Flickr\Model\Flickr\FlickrTag|null */
    private $Tag;

    /** @var array<string> */
    private static $allowed_actions = [
        'index',
        'photo',
        'photos',
    ];


    public function ColumnLayout(): string
    {
        return 'layout1col';
    }


    public function init(): void
    {
        parent::init();

        // Requirements, etc. here
    }


    public function photo(): DataList
    {
        $tagValue = Controller::curr()->getRequest()->getVar('ID');
        $this->Title = "Photos tagged '" . $tagValue . "'";

        /** @var \Suilven\Flickr\Model\Flickr\FlickrTag $tag */
        $tag = FlickrTag::get()->filter('Value', $tagValue)->first();
        $this->TagValue = $tagValue;
        $this->Tag = $tag;

        // @TODO what is this value when tag is undefined
        if (isset($tag)) {
            $this->FlickrPhotos = $tag->FlickrPhotos();
        }

        return $this->FlickrPhotos;
    }


    // @TODO check this method, old as

    /** @return \SilverStripe\ORM\DataList<\Suilven\Flickr\Model\Flickr\FlickrPhoto> */
    public function photos(): \SilverStripe\ORM\DataList
    {
        $this->Tags = FlickrTag::get();
        $this->Title = 'Tags for photos';

        $maxCount = DB::query("SELECT COUNT(TagID) as ct FROM FlickrPhoto_FlickrTags Group by' .'
            ' TagID Order by ct desc limit 1")->value();

        $sql = "select t.ID, t.ClassName, count(TagID) as Amount, t.Value
				From FlickrPhoto_FlickrTags ft
				inner join Tag t
				on t.ID = ft.TagID
				group by TagID
				order by t.Value
		;";

        $result = DB::query($sql);

        $tagCloud = \singleton('Tag')->buildDataObjectSet($result);
        foreach ($tagCloud as $tagV) {
            // font size in pixels
            $tagV->Amount = 10 + \round(32 * $tagV->Amount / $maxCount);
        }

        $this->TagCloud = $tagCloud;
    }
}
