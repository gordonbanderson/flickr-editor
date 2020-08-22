<?php declare(strict_types = 1);

namespace Suilven\Flickr\Helper;

class FlickrPerceptiveHashHelper extends FlickrHelper
{
    /**
     * Calculate sequences of images based on the perception hash, and create FlickrBuckets of them
     * in the database
     *
     * @return array<\Suilven\Flickr\Model\Flickr\FlickrBucket>
     */
    public function calculateSequences(FlickrSet $flickrSet): array
    {
        $hashes = [];
        \error_log($flickrSet->Title);

        foreach ($flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder) as $flickrPhoto) {
            $pair = [
                'ID' => $flickrPhoto->ID,
                'pHash' => $flickrPhoto->PerceptiveHash,

                // this matters, as it depends on what size was downloaded
                // @todo, refactor
                'URL' => $flickrPhoto->LargeURL2048,

                'SmallURL' => $flickrPhoto->SmallURL,
                'Rotated' => $flickrPhoto->Orientation === 90,
            ];
            $hashes[] = $pair;

            \print_r($pair);
        }

        $tolerance = 34;
        $minLength = 9;

        // optimal_bitrate = 50 * 25 * 2048 * 1366 / 256
        // mencoder "mf://*.JPG" -mf fps=12 -o test.avi -ovc lavc -lavcopts vcodec=mpeg4:vbitrate=13660000

        $currentBucket = [];
        $buckets = [];
        for ($i = 1; $i < \count($hashes) - 1; $i++) {
            // if the bucket is empty, start with the current image
            if (!isset($currentBucket)) {
                $currentBucket[] = [
                    'url' => $hashes[$i]['URL'],
                    'rotated' => $hashes[$i]['Rotated'],
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
                    'rotated' => $hashes[$i]['Rotated'],
                ];
            } else {
                // we need to save the current bucket if it's long enough
                if (\sizeof($currentBucket) < $minLength) {
                    \error_log('Bucket created but is too short');
                } else {
                    \error_log('Adding bucket');
                    $buckets[] = $currentBucket;
                }
                $currentBucket = [];
            }
            \error_log('H:' . $id . '    ' . $this->hammingDist($hash0, $hash1) . '     ' . $hashes[$i + 1]['URL']);
        }

        // add the last bucket if it's long enough
        if (\sizeof($currentBucket) < $minLength) {
            \error_log('Bucket created but is too short');
        } else {
            \error_log('Adding bucket');
            $buckets[] = $currentBucket;
        }

        return $buckets;
    }


    /**
     * Calculate the hamming distance
     *
     * @param string $hash1 first hash in lowercase hexidecimal
     * @param string $hash2 second hash in lowercase hexidecimal
     * @return int the number of binary bits that differ
     */
    private function hammingDist(string $hash1, string $hash2): int
    {
        $binaryHash1 = $this->hexHashToBinary($hash1);
        $binaryHash2 = $this->hexHashToBinary($hash2);

        $i = 0;
        $count = 0;
        while (isset($binaryHash1[$i]) !== '') {
            if ($binaryHash1[$i] !== $binaryHash2[$i]) {
                $count++;
            }
            $i++;
        }

        return $count;
    }


    /**
     * Convert a hex number into a binary
     *
     * @param string $hash a hash in hexadecimal
     * @return string a string of 1s and 0s
     */
    private function hexHashToBinary(string $hash): string
    {
        $binaryHash = \str_replace('0', '0000', $hash);
        $binaryHash = \str_replace('1', '0001', $binaryHash);
        $binaryHash = \str_replace('2', '0010', $binaryHash);
        $binaryHash = \str_replace('3', '0011', $binaryHash);
        $binaryHash = \str_replace('4', '0100', $binaryHash);
        $binaryHash = \str_replace('5', '0101', $binaryHash);
        $binaryHash = \str_replace('6', '0110', $binaryHash);
        $binaryHash = \str_replace('7', '0111', $binaryHash);
        $binaryHash = \str_replace('8', '1000', $binaryHash);
        $binaryHash = \str_replace('9', '1001', $binaryHash);
        $binaryHash = \str_replace('a', '1010', $binaryHash);
        $binaryHash = \str_replace('b', '1011', $binaryHash);
        $binaryHash = \str_replace('c', '1100', $binaryHash);
        $binaryHash = \str_replace('d', '1101', $binaryHash);
        $binaryHash = \str_replace('e', '1110', $binaryHash);
        $binaryHash = \str_replace('f', '1111', $binaryHash);

        return $binaryHash;
    }
}
