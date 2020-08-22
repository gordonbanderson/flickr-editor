<?php declare(strict_types = 1);

namespace Suilven\Flickr\Task;

use MatthiasMullie\Minify\CSS;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrSetHelper;
use Suilven\Flickr\Model\Site\FlickrSetPage;
use Suilven\Spriter\Spriter;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Class FlickrSetSpriteTask
 *
 * @package Suilven\Flickr\Task
 */
class FlickrSetSpriteTask extends BuildTask
{

    protected $title = "Create a CSS sprite from a set's thumbnails";

    protected $description = "After downloading thumbnails, use this to create a CSS sprite";

    protected $enabled = true;

    private static $segment = 'create-flickr-set-sprite';

    /** @inheritdoc */
    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $flickrSetID = $request->getVar('id');

        // second number essentially means all
        $imagesPerSprite = Config::inst()->get(FlickrSetPage::class, 'images_per_sprite');

        \error_log('IPS: ' . $imagesPerSprite);


        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $spritesDir = 'public/flickr/sprites';
        $this->mkdirIfRequired($spritesDir);
        $this->mkdirIfRequired($spritesDir . '/' . $flickrSetID);
        $imagesDir = 'public/flickr/images/';
        $flickrSetImagesDir = $imagesDir . $flickrSetID;
        $tmpDir = $flickrSetImagesDir . '/tmp';
        $this->mkdirIfRequired($tmpDir);

        $nPhotos = $flickrSet->FlickrPhotos()->count();
        $nPages = \abs($nPhotos / $imagesPerSprite) + 1;
        $page = 0;
        $css = '';
        while ($page < $nPages) {
            \error_log($page + 1 . '/' . $nPages);

            $photosForSprite = $flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder)->
            limit($imagesPerSprite, $imagesPerSprite * $page);

            \error_log('Moving ' . $photosForSprite->count() . ' files to temporary space');
            /** @var \Suilven\Flickr\Model\Flickr\FlickrPhoto $photo */
            foreach ($photosForSprite as $photo) {
                $srcFile = $flickrSetImagesDir . '/' . $photo->CSSSpriteFileName() . '.jpg';
                $destFile = $tmpDir . '/' . $photo->CSSSpriteFileName() . '.jpg';
                \rename($srcFile, $destFile);
            }

            // create CSS for the sprite of the paged images
            $spriteDir = "public/flickr/sprites/" . $flickrSetID;
            $spriterConfig = [
                'iconSuffix' => '-' . $page,
                // set to true if you want to force the CSS and sprite generation.
                "forceGenerate" => true,

                // folder that contains the source pictures for the sprite.
                "srcDirectory" => $tmpDir,
                // folder where you want the sprite image file to be saved (folder has to be writable by your webserver)
                "spriteDirectory" => $spriteDir,

                // path to the sprite image for CSS rule.
                "spriteFilepath" => "/flickr/sprites/" . $flickrSetID,
                // name of the generated CSS and PNG file.
                "spriteFilename" => "icon-sprite-" . $page,

                // margin in px between tiles in the highest 'retina' dimension (default is 0) -
                // //if you generate different 'retina' dimensions, take a common multiple of the
                // selected variants.
                "tileMargin" => 0,
                // defines the desired 'retina' dimensions, you want. [2,1]
                "retina" => [2, 1],
                // delimiter inside the sprite image filename.
                "retinaDelimiter" => "@",
                // namespace for your icon CSS classes
                "namespace" => "fs-",

                // set to true if you don't need hover icons
                "ignoreHover" => false,
                // set to any suffix you want.
                "hoverSuffix" => "-hover",

                "iconSuffix" => '-sprite-' . $page,

                "targets" => [
                    // you can define multiple targets that will all reference the same png sprite
                    [
                        // folder where you want the sprite CSS to be saved (folder has to be writable, too)
                        "cssDirectory" => $spriteDir,
                        // your CSS/Less/Sass target file
                        "cssFilename" => "flickr-set-sprites.css",
                    ],
                ],

            ];

            new Spriter($spriterConfig);

            $minifier = new CSS();
            $csspath = $spriteDir . '/flickr-set-sprites.css';
            $minifier->add($csspath);

            $pageCSS = $minifier->minify();

            $css .= $pageCSS;

            \error_log('Moving ' . $photosForSprite->count() .
                ' files back from temporary space');
            foreach ($photosForSprite as $photo) {
                $srcFile = $tmpDir . '/' . $photo->CSSSpriteFileName() . '.jpg';
                $destFile = $flickrSetImagesDir . '/' . $photo->CSSSpriteFileName() . '.jpg';
                \rename($srcFile, $destFile);
            }
            $page++;
        }


        //$css = str_replace('icon-sprite.png', 'icon-sprite@2x.png', $css);
        $flickrSet->SpriteCSS = $css;
        echo $flickrSet->SpriteCSS;
        $flickrSet->write();
    }


    /** @param string $dir The directory to make */
    private function mkdirIfRequired(string $dir): void
    {
        if (\file_exists($dir) || \is_dir($dir)) {
            return;
        }

        \mkdir($dir);
    }
}
