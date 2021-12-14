<?php declare(strict_types = 1);

namespace Suilven\Flickr\Extension;

use SilverStripe\Core\Extension;

/**
 * Class \Suilven\Flickr\Extension\FacetedPhotoSearchExtension
 *
 * @property \Suilven\Flickr\Extension\FacetedPhotoSearchExtension $owner
 */
class FacetedPhotoSearchExtension extends Extension
{

    public function postProcessFacetTitle(string &$facetTitle): void
    {

        $titles = [
          'aperture' => 'Aperture',
          'shutterspeed' => 'Shutter Speed',
          'iso' => 'ISO',
          'flickrtagid' => 'Tags',
        ];

        if (!\in_array($facetTitle, \array_keys($titles), true)) {
            return;
        }

        $facetTitle = $titles[$facetTitle];
    }


    /**
     * @param string $token - the name of the facet, e.g. aperture
     * @param array<string> $tokenFacets - the facets for this token
     * @return array<string> - massaged title and facets @TODO is this correct?
     */
    public function postProcessFacetResults(string $token, array $tokenFacets): array
    {
        $result = [];

        foreach ($tokenFacets as $facet) {
            $value = $facet['Value'];

            switch ($token) {
                case 'Aperture':
                    //$value = $facet['Value'];

                    $value = \str_replace('.0', '', $value);
                    //$aperture = str_replace('000000', '', $aperture);
                    $value = \str_replace('00000', '', $value);

                    $facet['Value'] = !isset($value)
                        ? 'Unknown'
                        : 'f' . $value;

                    break;
                case 'Shutter Speed':
                    if (!isset($value)) {
                        $facet['Value'] = 'Unknown';
                    }

                    break;
                case 'ISO':
                    if (!isset($value)) {
                        $facet['Value'] = 'Unknown';
                    }

                    break;
                default:
                    // do nothing
                    break;
            }
            \array_push($result, $facet);
        }

        return $result;
    }
}
