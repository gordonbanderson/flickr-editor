<?php
/**
 * Created by PhpStorm.
 * User: gordon
 * Date: 11/4/2561
 * Time: 16:22 น.
 */

namespace Suilven\Flickr\Task;

use Samwilson\PhpFlickr\PhotosetsApi;
use Samwilson\PhpFlickr\PhpFlickr;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrHelper;
use Suilven\Flickr\Helper\FlickrSetHelper;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Suilven\Flickr\Model\Site\FlickrSetPage;
use Suilven\Spriter\Spriter;


class FlickrSetSpriteTask extends BuildTask
{

    protected $title = "Create a CSS sprite from a set's thumbnails";

    protected $description = "After downloading thumbnails, use this to create a CSS sprite";

    private static $segment = 'create-flickr-set-sprite';

    protected $enabled = true;

    private function mkdir_if_required($dir)
    {
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir);
        }
    }


    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $flickrSetID = $_GET['id'];

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $this->mkdir_if_required('public/flickr/sprites');
        $targetDir = 'public/flickr/' . $flickrSetID;

        $spriterConfig = [
            "forceGenerate" => false,                 // set to true if you want to force the CSS and sprite generation.

            "srcDirectory" => $targetDir, // folder that contains the source pictures for the sprite.
            "spriteDirectory" => "public/flickr/sprites",   // folder where you want the sprite image file to be saved (folder has to be writable by your webserver)

            "spriteFilepath" => "/flickr/sprite",     // path to the sprite image for CSS rule.
            "spriteFilename" => "icon-sprite-" . $flickrSetID,        // name of the generated CSS and PNG file.

            "tileMargin" => 0,                        // margin in px between tiles in the highest 'retina' dimension (default is 0) - if you generate different 'retina' dimensions, take a common multiple of the selected variants.
            "retina" => [2, 1],                  // defines the desired 'retina' dimensions, you want.
            "retinaDelimiter" => "@",                 // delimiter inside the sprite image filename.
            "namespace" => "icon-",                   // namespace for your icon CSS classes

            "ignoreHover" => false,                   // set to true if you don't need hover icons
            "hoverSuffix" => "-hover",                // set to any suffix you want.

            "targets" => [
                // you can define multiple targets that will all reference the same png sprite
                [
                    "cssDirectory" => "public/flickr/sprites",         // folder where you want the sprite CSS to be saved (folder has to be writable, too)
                    "cssFilename" => "icon-sprite.sass",      // your CSS/Less/Sass target file
                    "globalTemplate" => "...",                // global template, which contains general CSS styles for all icons (remove line for default)
                    "eachTemplate" => "...",                  // template for each CSS icon class (remove line for default)
                    "eachHoverTemplate" => "...",             // template for each CSS icon hover class (remove line for default)
                    "ratioTemplate" => "..."                  // template for each retina media query (remove line for default)
                ]
            ]

        ];

        $spriter = new Spriter($spriterConfig);

    }








}
