<?php declare(strict_types = 1);

namespace Suilven\Flickr;

use SilverStripe\Forms\HiddenField;
use SilverStripe\View\Requirements;

// These are for the FieldHolder method
// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
// @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

/**
 * Text input field.
 *
 * @package forms
 * @subpackage fields-basic
 */
class FlickrPhotoSelectionField extends HiddenField
{

    /** @var int */
    protected $maxLength = 30;

    /** @var string */
    protected $flickrTitle;

    /** @var string */
    protected $flickrID;

    /** @var string */
    protected $mediumURL;

    /**
     * FlickrPhotoSelectionField constructor.
     *
     * @param string $name The internal field name, passed to forms.
     * @param string|\SilverStripe\View\ViewableData|null $title The human-readable field label.
     * @param mixed $value A FlickrPhoto, if one has previously been chosen
     */
    public function __construct(string $name, $title = null, $value = null)
    {
        parent::setTemplate('FLickrPhotoSelectionField');

        if (!\is_null($value)) {
            /** @var \Suilven\Flickr\Model\Flickr\FlickrPhoto $flickrPhoto */
            $flickrPhoto = $value;
            $value = $flickrPhoto->ID;
            $this->flickrTitle = $flickrPhoto->Title;
            $this->flickrID = $flickrPhoto->FlickrID;
            $this->mediumURL = $flickrPhoto->MediumURL;
        }

        $this->addExtraClass('flickrPhotoSelectionField');

        // @TODO is this a bug in the SilverStripe phpdoc?
        // @phpstan-ignore-next-line
        parent::__construct($name, $title, $value);
    }


    public function getFlickrTitle(): string
    {
        return $this->flickrTitle;
    }


    public function getFlickrID(): string
    {
        return $this->flickrID;
    }


    public function getMediumURL(): string
    {
        return $this->mediumURL;
    }


    /** @return $this */
    public function setMaxLength(int $length)
    {
        $this->maxLength = $length;

        return $this;
    }


    public function getMaxLength(): int
    {
        return $this->maxLength;
    }


    /** @return array<int|string, mixed> */
    public function getAttributes(): array
    {
        return \array_merge(
            parent::getAttributes(),
            [
                'maxlength' => $this->getMaxLength(),
                // @TODO is this correct logic?
                'size' => ($this->getMaxLength() === 0) ? \min($this->getMaxLength(), 30) : null,
            ]
        );
    }


    /** @return \SilverStripe\ORM\FieldType\DBField|\SilverStripe\ORM\FieldType\DBHTMLText */
    public function InternallyLabelledField()
    {
        if (\is_null($this->value)) {
            $this->value = $this->Title();
        }

        return $this->Field();
    }


    /** @inheritdoc */
    public function FieldHolder($properties = []): string
    {
        Requirements::javascript('weboftalent/flickr:dist/admin/client/js/flickredit.js');

        return parent::FieldHolder();
    }
}
