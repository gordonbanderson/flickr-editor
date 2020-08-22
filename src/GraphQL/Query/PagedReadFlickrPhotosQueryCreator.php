<?php declare(strict_types = 1);

namespace Suilven\Flickr\GraphQL\Query;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use SilverStripe\GraphQL\Pagination\Connection;
use SilverStripe\GraphQL\Pagination\PaginatedQueryCreator;
use SilverStripe\ORM\DataObject;
use Suilven\Flickr\Model\Flickr\FlickrSet;

// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * Class PagedReadFlickrPhotosQueryCreator
 *
 * @package Suilven\Flickr\GraphQL\Query
 */
class PagedReadFlickrPhotosQueryCreator extends PaginatedQueryCreator
{

    /** @return array<string, array<string,int>> */
    public function args(): array
    {
        return [
            'FlickrSetID' => ['type' => Type::int()],
        ];
    }


    public function createConnection(): Connection
    {
        return Connection::create('paginatedReadFlickrPhotos')
            ->setConnectionType($this->manager->getType('flickrphoto'))
            ->setArgs([
                'FlickrSet' => [
                    'type' => Type::string(),
                ],
            ])
            ->setSortableFields(['ID', 'Title', 'FlickrID'])
            ->setConnectionResolver(static function ($object, array $args, $context, ResolveInfo $info) {
                if (!isset($args['FlickrSetID'])) {
                    throw new \InvalidArgumentException('FlickrSetID parameter is required');
                }

                $member = Member::singleton();
                if (!$member->canView($context['currentUser'])) {
                    throw new \InvalidArgumentException(\sprintf(
                        '%s view access not permitted',
                        Member::class,
                    ));
                }

                $photos = null;
                if (isset($args['FlickrSetID'])) {
                    $flickrSet = DataObject::get_by_id(FlickrSet::class, $args['FlickrSetID']);
                    $photos = $flickrSet->FlickrPhotos()->sort($flickrSet->SortOrder);
                }

                return $photos;
            });
    }
}
