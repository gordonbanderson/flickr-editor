<?php declare(strict_types = 1);


namespace Suilven\Flickr\Task;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Suilven\Flickr\Helper\FlickrPerceptiveHashHelper;
use Suilven\Flickr\Helper\FlickrSetHelper;
use Suilven\Flickr\Model\Flickr\FlickrBucket;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Suilven\Sluggable\Helper\SluggableHelper;

// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

/**
 * Class CreateVideoFromPerceptiveHashes
 * @package Suilven\Flickr\Task
 */
class CreateVideoFromPerceptiveHashes extends BuildTask
{

    protected $title = 'Calculate perceptive hashes for a Flickr Set';

    protected $description = 'Calculate perceptive hashes and store in the database for a given Flickr set';

    protected $enabled = true;

    /** @var string  */
    private static $segment = 'create-video-from-perceptive-hash';

    // @phpstan-ignore-next-line
    public function findSequences(FlickrSet $flickrSet, string $srcDir, string $targetDir): void
    {
        $helper = new FlickrPerceptiveHashHelper();
        $buckets = $helper->calculateSequences($flickrSet);

        $html = '';

        $ctr = 0;

        $bucketSize = \count($buckets);

        \error_log('BS: ' . $bucketSize);
        for ($j=0; $j< $bucketSize; $j++) {
            /** @var FlickrBucket $bucket */
            $bucket = $buckets[$j];
            \error_log('BUCKET, OF SIZE ' . \count($buckets));

            \error_log(\print_r($buckets, true));

            $currentBucketSize = \count($buckets);
            // HACK, had to -1 to get it to work
            foreach ($bucket->FlickrPhotos() as $photo) {
                $html .= "\n<img src='". $bucket[$i]['url']."'/>";

                $filename = \basename($bucket[$i]['url']);
                $from = \trim($srcDir) .'/' . \trim($filename);

                $paddedCtr = \str_pad($ctr, 8, '0', \STR_PAD_LEFT);
                $to = \trim($targetDir) .'/' . $paddedCtr . '.JPG';
                \error_log($from . ' --> ' . $to);
                \copy($from, $to);
                $rotated = $bucket[$i]['rotated'];
                \error_log('>>>> ROTATED: ' . $rotated);
                if ($rotated) {
                    $dimensions = '1365x2048';
                    $cmd = ('/usr/bin/convert ' . $to .' -gravity center -background black -extent ' .
                        $dimensions .' ' . $to);
                    \error_log('CMD:' . $cmd);
                    \exec($cmd);
                }

                $ctr++;
            }


            $html .= '<br/><hr/><br/>';
        }

        \file_put_contents('/var/www/buckets.html', $html);
    }


    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @return \SilverStripe\Control\HTTPResponse | void
     */
    public function run($request)
    {
        // check this script is being run by admin
        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $size = 'small';

        $flickrSetID = $request->getVar('id');

        $flickrSetHelper = new FlickrSetHelper();
        $flickrSet = $flickrSetHelper->getOrCreateFlickrSet($flickrSetID);

        $this->mkdirIfRequired('/tmp/flickr');
        $targetDir = '/tmp/flickr/' . $flickrSetID;
        $this->mkdirIfRequired($targetDir);
        $movieDir = $targetDir . '/movie';
        $this->mkdirIfRequired($movieDir);

        $srcDir = 'public/flickr/images/' . $flickrSetID;

        $this->calculatePerceptiveHashes($flickrSet, $targetDir, $size);
        $this->findSequences($flickrSet, $srcDir, $movieDir);

        $slugHelper = new SluggableHelper();
        $slug = $slugHelper->getSlug($flickrSet->Title);

        $cmd = 'cd /tmp/flickr/' . $flickrSetID . '/movie && ';
        $cmd .= 'mencoder "mf://*.JPG" -mf fps=12 -o /var/www/' . $slug;
        $cmd .= '.avi -ovc lavc -lavcopts vcodec=mpeg4:vbitrate=13660000 && cd -';

        \error_log($cmd);
    }


    private function mkdirIfRequired(string $dir): void
    {
        if (\file_exists($dir) || \is_dir($dir)) {
            return;
        }

        \mkdir($dir);
    }


    // @phpstan-ignore-next-line
    private function calculatePerceptiveHashes(FlickrSet $flickrSet, string $targetDir, string $size): void
    {
        \error_log('---- new image ----');
        \error_log('SIZE: ' . $size);

        // @phpstan-ignore-next-line
        foreach ($flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder) as $flickrPhoto) {
            $oldHash = $flickrPhoto->PerceptiveHash;


            \error_log('START: hash = ' . $oldHash);
            \error_log('ID: ' . $flickrPhoto->ID);
            if ($flickrPhoto->PerceptiveHash) {
                echo '>>>>> Already calculated hash';

                continue;
            }
            $imageURL = $flickrPhoto->SmallURL;
            switch ($size) {
                case 'original':
                    $imageURL = $flickrPhoto->OriginalURL;

                    break;
                case 'small':
                    $imageURL = $flickrPhoto->SmallURL;

                    break;
                case 'medium':
                    $imageURL = $flickrPhoto->MediumURL;

                    break;
                case 'large':
                    $imageURL = $flickrPhoto->LargeURL;

                    break;
                case 'large1600':
                    $imageURL = $flickrPhoto->Large1600;

                    break;
                default:
                    // url already defaulted
            }
            \error_log('Downloading ' . $imageURL . ' of size ' . $size);

            /** @var resource $ch */
            $ch = \curl_init($imageURL);

            $filename = 'tohash.jpg';
            $complete_hash_file_path = \trim($targetDir) . '/' . \trim($filename);
            $complete_hash_file_path = \str_replace(' ', '', $complete_hash_file_path);

            \error_log('CSL: ' . $complete_hash_file_path);

            // @todo This fails if public/flickr/images/FLICKR_SET_ID is missing
            \error_log('TARGET DIR: ' . $targetDir);

            /** @var resource $fp */
            $fp = \fopen($complete_hash_file_path, 'wb');

            \curl_setopt($ch, \CURLOPT_FILE, $fp);
            \curl_setopt($ch, \CURLOPT_HEADER, 0);
            \curl_exec($ch);
            \curl_close($ch);
            \fclose($fp);

            // @todo Make this configurable
            // tool is avail from https://github.com/commonsmachinery/blockhash-python4, just clone it
            // also required is python-pil

            $hashCMD = 'python3 /var/www/blockhash-python/blockhash.py ' . $complete_hash_file_path;
            $o = \exec($hashCMD, $output);
            $splits = \explode(' ', $o);
            $hash = $splits[0];

/*
            $hash = $hasher->hash($complete_hash_file_path);
*/
            \error_log('Saving hash ' . $hash);

            $flickrPhoto->PerceptiveHash = $hash;
            $flickrPhoto->write();
        }
    }
}
