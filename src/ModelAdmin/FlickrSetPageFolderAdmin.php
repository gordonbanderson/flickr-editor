<?php declare(strict_types = 1);

namespace Suilven\Flickr\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataList;
use SilverStripe\View\Requirements;
use Suilven\Flickr\Model\Site\FlickrSetPage;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class \Suilven\Flickr\ModelAdmin\FlickrSetPageFolderAdmin
 */
class FlickrSetPageFolderAdmin extends ModelAdmin
{
    /** @var \Suilven\Flickr\ModelAdmin\QueuedJobService */
    public $jobQueue;

    private static $url_segment = 'flickrsetpagefolders';
    private static $menu_title = 'Flickr Set Folders';

    private static $managed_models = [FlickrSetPage::class];

    private static $menu_icon = 'weboftalent/flickr:icons/folders.png';

    /** @param \SilverStripe\Control\HTTPRequest|null $request */
    public function EditForm(?HTTPRequest $request = null): ?\SilverStripe\Forms\Form
    {
        $form = parent::EditForm($request);

        Requirements::javascript('weboftalent/flickr:dist/admin/client/js/flickredit.js');
        Requirements::css('weboftalent/flickr:dist/admin/client/css/flickredit.css');


        //->sort('Title desc');
        $flickrSetPages = DataList::create('FlickrSetPage')->where('ParentID = 0');
        $flickrSetFolders = DataList::create('FlickrSetFolder')->sort('Title');

        $html ='<h2>Flickr Set Folders</h2><div id="flickrFolders">';
        foreach ($flickrSetFolders as $folder) {
            $html.='<div class="flickrSetFolderDroppable" data-id="'.$folder->ID.'">'.$folder->Title.'</div>';
        }

        $html .= '</div><h2>Flickr Set Pages</h2><div id="flickrSetPages">';
        foreach ($flickrSetPages as $fspage) {
            $html.='<div class="flickrSetDraggable" data-id="'.$fspage->ID.'">'.$fspage->Title.'</div>';
        }

        $html .= '</div>';


        $lf = new LiteralField('FlickrSetOrganizer', $html);
        //$form->Fields()->push($lf);
        $fl = new FieldList();
        $fl->push($lf);
        $form->setFields($fl);

        return $form;
    }


    public function Tools(): string
    {
        return '';
    }
}
