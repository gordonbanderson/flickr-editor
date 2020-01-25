<?php
namespace Suilven\Flickr\Helper;

use SilverStripe\ORM\DataList;
use Suilven\Flickr\Model\Flickr\FlickrSet;
use Suilven\Flickr\Model\Flickr\FlickrTag;

class FlickrPerceptiveHashHelper extends FlickrHelper
{
    /**
     * @param FlickrSet $flickrSet
     */
    public function findSequences($flickrSet)
    {
        $hashes = [];
        error_log($flickrSet->Title);

        foreach ($flickrSet->FlickrPhotos()->sort('UploadUnixTimeStamp') as $flickrPhoto) {
            $pair = [
                'ID' => $flickrPhoto->ID,
                'pHash' => $flickrPhoto->PerceptiveHash,

                // this matters, as it depends on what size was downloaded
                // @todo, refactor
                'URL' => $flickrPhoto->LargeURL2048,

                'SmallURL' => $flickrPhoto->SmallURL,
                'Rotated' => $flickrPhoto->Orientation == 90
            ];
            $hashes[] = $pair;

            print_r($pair);
        }

        $tolerance = 34;
        $minLength = 10;

        $currentBucket = [];
        $buckets = [];
        for ($i = 1; $i < count($hashes) - 1; $i++) {
            // if the bucket is empty, start with the current image
            if (empty($currentBucket)) {
                $currentBucket[] = [
                    'url' => $hashes[$i]['URL'],
                    'rotated' => $hashes[$i]['Rotated']
                ];
            }
            $hash0 = $hashes[$i]['pHash'];
            $hash1 = $hashes[$i + 1]['pHash'];
            $id = $hashes[$i]['ID'];
            $distance = $this->hammingDist($hash0, $hash1);

            // if we are within tolerance, add to the bucket
            if ($distance < $tolerance) {
                $currentBucket[] = [
                    'url' => $hashes[$i]['URL'],
                    'rotated' => $hashes[$i]['Rotated']
                ];
            } else {
                // we need to save the current bucket if it's long enough
                if (sizeof($currentBucket) < $minLength) {
                    error_log('Bucket created but is too short');
                } else {
                    error_log('Adding bucket');
                    $buckets[] = $currentBucket;
                }
                $currentBucket = [];
            }
            error_log('H:' . $id . '    ' . $this->hammingDist($hash0, $hash1) . '     ' . $hashes[$i + 1]['URL']);
        }

        // add the last bucket if it's long enough
        if (sizeof($currentBucket) < $minLength) {
            error_log('Bucket created but is too short');
        } else {
            error_log('Adding bucket');
            $buckets[] = $currentBucket;
        }

        return $buckets;
    }

    /*
     * Convert hex strings to binary and then calculate hamming distance
     * @param $hash1 hex string for perceptive hash
     * @param $hash2 hex string for perceptive hash
     */
    private function hammingDist($hash1, $hash2)
    {
        $binaryHash1 = $this->hexHashToBinary($hash1);
        $binaryHash2 = $this->hexHashToBinary($hash2);

        $i = 0;
        $count = 0;
        while (isset($binaryHash1[$i]) != '') {
            if ($binaryHash1[$i] != $binaryHash2[$i])
                $count++;
            $i++;
        }
        return $count;
    }

    /**
     * @param $hash
     * @return mixed
     */
    private function hexHashToBinary($hash)
    {
        $binaryHash = str_replace('0', '0000', $hash);
        $binaryHash = str_replace('1', '0001', $binaryHash);
        $binaryHash = str_replace('2', '0010', $binaryHash);
        $binaryHash = str_replace('3', '0011', $binaryHash);
        $binaryHash = str_replace('4', '0100', $binaryHash);
        $binaryHash = str_replace('5', '0101', $binaryHash);
        $binaryHash = str_replace('6', '0110', $binaryHash);
        $binaryHash = str_replace('7', '0111', $binaryHash);
        $binaryHash = str_replace('8', '1000', $binaryHash);
        $binaryHash = str_replace('9', '1001', $binaryHash);
        $binaryHash = str_replace('a', '1010', $binaryHash);
        $binaryHash = str_replace('b', '1011', $binaryHash);
        $binaryHash = str_replace('c', '1100', $binaryHash);
        $binaryHash = str_replace('d', '1101', $binaryHash);
        $binaryHash = str_replace('e', '1110', $binaryHash);
        $binaryHash = str_replace('f', '1111', $binaryHash);
        return $binaryHash;
    }

}
