<?php
/**
 * Created by PhpStorm.
 * User: gordon
 * Date: 11/4/2561
 * Time: 16:22 น.
 */

namespace Suilven\Flickr\Task;

use MatthiasMullie\Minify\CSS;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrSetHelper;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;
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

        // second number essentially means all
        $imagesPerSprite = Config::inst()->get(FlickrSetPage::class, 'images_per_sprite');

        error_log('IPS: ' . $imagesPerSprite);


        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $spritesDir = 'public/flickr/sprites';
        $this->mkdir_if_required($spritesDir);
        $this->mkdir_if_required($spritesDir . '/' . $flickrSetID);
        $imagesDir = 'public/flickr/images/';
        $flickrSetImagesDir = $imagesDir . $flickrSetID;
        $tmpDir = $flickrSetImagesDir . '/tmp';
        $this->mkdir_if_required($tmpDir);
        $sourceImageDir = $tmpDir;

        $nPhotos = $flickrSet->FlickrPhotos()->count();
        $nPages = abs($nPhotos / $imagesPerSprite) + 1;
        $page = 0;
        $css = '';
        while ($page < $nPages) {
            error_log($page + 1 . '/' . $nPages);

            $photosForSprite = $flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder)->
            limit($imagesPerSprite, $imagesPerSprite * $page);

            error_log('Moving ' . $photosForSprite->count() . ' files to temporary space');
            /** @var FlickrPhoto $photo */
            foreach ($photosForSprite as $photo) {
                $srcFile = $flickrSetImagesDir . '/' . $photo->CSSSpriteFileName() . '.jpg';
                $destFile = $tmpDir . '/' . $photo->CSSSpriteFileName() . '.jpg';
                rename($srcFile, $destFile);
            }

            // create CSS for the sprite of the paged images
            $spriteDir = "public/flickr/sprites/" . $flickrSetID;
            $spriterConfig = [
                'iconSuffix' => '-' . $page,
                "forceGenerate" => true,                 // set to true if you want to force the CSS and sprite generation.

                "srcDirectory" => $tmpDir, // folder that contains the source pictures for the sprite.
                "spriteDirectory" => $spriteDir,   // folder where you want the sprite image file to be saved (folder has to be writable by your webserver)

                "spriteFilepath" => "/flickr/sprites/" . $flickrSetID,     // path to the sprite image for CSS rule.
                "spriteFilename" => "icon-sprite-" . $page,        // name of the generated CSS and PNG file.

                "tileMargin" => 0,                        // margin in px between tiles in the highest 'retina' dimension (default is 0) - if you generate different 'retina' dimensions, take a common multiple of the selected variants.
                "retina" => [2, 1],                  // defines the desired 'retina' dimensions, you want. [2,1]
                "retinaDelimiter" => "@",                 // delimiter inside the sprite image filename.
                "namespace" => "fs-",                   // namespace for your icon CSS classes

                "ignoreHover" => false,                   // set to true if you don't need hover icons
                "hoverSuffix" => "-hover",                // set to any suffix you want.

                "iconSuffix" => '-sprite-' . $page,

                "targets" => [
                    // you can define multiple targets that will all reference the same png sprite
                    [
                        "cssDirectory" => $spriteDir,         // folder where you want the sprite CSS to be saved (folder has to be writable, too)
                        "cssFilename" => "flickr-set-sprites.css",      // your CSS/Less/Sass target file
                        // "globalTemplate" => "vendor/suilven/php-spriter/src/templates/",                // global template, which contains general CSS css for all icons (remove line for default)
                        // "eachTemplate" => "vendor/suilven/php-spriter/src/templates/",                  // template for each CSS icon class (remove line for default)
                        // "eachHoverTemplate" => "vendor/suilven/php-spriter/src/templates/",             // template for each CSS icon hover class (remove line for default)
                        // "ratioTemplate" => "vendor/suilven/php-spriter/src/templates/"                  // template for each retina media query (remove line for default)
                    ]
                ]

            ];

            $spriter = new Spriter($spriterConfig);


            $minifier = new CSS();
            $csspath = $spriteDir . '/flickr-set-sprites.css';
            $minifier->add($csspath);

            $pageCSS = $minifier->minify();

            $css .=  $pageCSS;

            error_log('Moving ' . $photosForSprite->count() . ' files back from temporary space');
            foreach ($photosForSprite as $photo) {
                $srcFile = $tmpDir . '/' . $photo->CSSSpriteFileName() . '.jpg';
                $destFile = $flickrSetImagesDir . '/' . $photo->CSSSpriteFileName() . '.jpg';
                rename($srcFile, $destFile);
            }
            $page++;
        }


        //$css = str_replace('icon-sprite.png', 'icon-sprite@2x.png', $css);
        $flickrSet->SpriteCSS = $css;
        echo $flickrSet->SpriteCSS;
        $flickrSet->write();
    }

}
