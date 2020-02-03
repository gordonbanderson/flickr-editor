<?php
/**
 * Created by PhpStorm.
 * User: gordon
 * Date: 11/4/2561
 * Time: 16:22 น.
 */

namespace Suilven\Flickr\Task;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrPerceptiveHashHelper;
use Suilven\Flickr\Helper\FlickrSetHelper;


class RoutesDump extends BuildTask
{

    protected $title = 'Dump configured routes';

    protected $description = 'Dump configured routes';

    private static $segment = 'routes-dump';

    protected $enabled = true;



    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $routes = Director::config()->get('rules');

        error_log(print_r($routes, 1));
    }



}
