<?php declare(strict_types = 1);

namespace Suilven\Flickr\Task;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrSetHelper;

// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

/**
 * Class ImportSetTask
 *
 * @package Suilven\Flickr\Task
 */
class ImportSetTask extends BuildTask
{

    /** @var string */
    protected $title = 'Import a Flickr set';

    /** @var string */
    protected $description = 'Import a flickr set';

    /** @var bool */
    protected $enabled = true;

    /** @var string */
    private static $segment = 'import-flickr-set';


    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @return \SilverStripe\Control\HTTPResponse | void
     */
    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || (bool) Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure();
        }

        $flickrSetID = $request->getVar('id');

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSetHelper->importSet($flickrSetID);
    }
}
