<?php declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: gordon
 * Date: 11/4/2561
 * Time: 16:22 น.
 */

namespace Suilven\Flickr\Task;

use League\CLImate\CLImate;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

/**
 * Class RoutesDump
 *
 * @package Suilven\Flickr\Task
 */
class RoutesDump extends BuildTask
{

    /** @var string */
    protected $title = 'Dump configured routes';

    /** @var string */
    protected $description = 'Dump configured routes';

    /** @var bool */
    protected $enabled = true;

    /** @var string */
    private static $segment = 'routes-dump';


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

        $routes = Director::config()->get('rules');

        $climate = new CLImate();
        $climate->info(\print_r($routes, true));
    }
}
