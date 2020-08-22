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

    public function postProcessFacetTitle(&$facetTitle): void
    {

        $titles = [
          'aperture' => 'Aperture',
          'shutterspeed' => 'Shutter Speed',
          'iso' => 'ISO',
          'flickrtagid' => 'Tags',
        ];

        if (!\in_array($facetTitle, \array_keys($titles))) {
            return;
        }

        $facetTitle = $titles[$facetTitle];
    }


    /**
     * @param $token - the name of the facet, e.g. aperture
     * @param $tokenFacets - the facets for this token
     * @return array - massaged title and facets
     */
    public function postProcessFacetResults($token, $tokenFacets): array
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

                    $facet['Value'] = empty($value)
                        ? 'Unknown'
                        : 'f' . $value;

                    break;
                case 'Shutter Speed':
                    if (empty($value)) {
                        $facet['Value'] = 'Unknown';
                    }

                case 'ISO':
                    if (empty($value)) {
                        $facet['Value'] = 'Unknown';
                    }
                default:
                    // do nothing
                    break;
            }
            \array_push($result, $facet);
        }

        return $result;
    }
}
