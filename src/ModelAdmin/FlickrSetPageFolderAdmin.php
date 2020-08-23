<?php declare(strict_types = 1);

namespace Suilven\Flickr\ModelAdmin;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
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
    /** @var \Symbiote\QueuedJobs\Services\QueuedJobService */
    public $jobQueue;

    /** @var string */
    private static $url_segment = 'flickrsetpagefolders';

    /** @var string */
    private static $menu_title = 'Flickr Set Folders';

    /** @var array<string> */
    private static $managed_models = [FlickrSetPage::class];

    /** @var string */
    private static $menu_icon = 'weboftalent/flickr:icons/folders.png';

    /**
     * Retrieves an edit form, either for display, or to process submitted data.
     * Also used in the template rendered through {@link Right()} in the $EditForm placeholder.
     *
     * This is a "pseudo-abstract" methoed, usually connected to a {@link getEditForm()}
     * method in an entwine subclass. This method can accept a record identifier,
     * selected either in custom logic, or through {@link currentPageID()}.
     * The form usually construct itself from {@link DataObject->getCMSFields()}
     * for the specific managed subclass defined in {@link LeftAndMain::$tree_class}.
     *
     * @param HTTPRequest $request Passed if executing a HTTPRequest directly on the form.
     * If empty, this is invoked as $EditForm in the template
     * @return Form Should return a form regardless wether a record has been found.
     *  Form might be readonly if the current user doesn't have the permission to edit
     *  the record.
     */
    public function EditForm($request = null)
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
