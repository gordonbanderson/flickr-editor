<?php declare(strict_types = 1);

namespace Suilven\Flickr\Task;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Model\Flickr\FlickrSet;

// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

/**
 * Class UpdateSetMetaTask
 *
 * @package Suilven\Flickr\Task
 */
class UpdateSetMetaTask extends BuildTask
{
    /** @var string */
    protected $title = 'Update Flickr metadata';

    /** @var string */
    protected $description = 'Updates Flickr metadata from edits made in SilverStripe';

    /** @var bool */
    protected $enabled = true;

    /** @var string */
    private static $segment = 'update-flickr-set-metadata';


    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @return \SilverStripe\Control\HTTPResponse | void
     */
    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() ||
            (bool) Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure();
        }

        $flickrSetID = $request->getVar('id');

        /** @var \Suilven\Flickr\Model\Flickr\FlickrSet<\Suilven\Flickr\Model\Flickr\FlickrPhoto> $flickrSet */
        $flickrSet = FlickrSet::get()->filter(['FlickrID' => $flickrSetID])->first();
        $flickrSet->writeToFlickr();
    }
}
