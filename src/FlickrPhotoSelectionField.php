<?php declare(strict_types = 1);

namespace Suilven\Flickr;

use SilverStripe\Forms\HiddenField;
use SilverStripe\View\Requirements;
use Suilven\Flickr\Model\Flickr\FlickrPhoto;

// These are for the FieldHolder method
// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification

/**
 * Text input field.
 *
 * @package forms
 * @subpackage fields-basic
 */
class FlickrPhotoSelectionField extends HiddenField
{

    /** @var int */
    protected $maxLength;

    protected $flickrTitle;
    protected $flickrID;

    /**
     * FlickrPhotoSelectionField constructor.
     *
     * @param \SilverStripe\Forms\Form|null $form
     */
    public function __construct(
        string $name,
        ?string $title = null,
        ?FlickrPhoto $flickrPhoto = null,
        ?int $maxLength = null,
        ?Form $form = null
    ) {
        $this->maxLength = $maxLength;

        parent::setTemplate('FLickrPhotoSelectionField');

        $value = '';
        if ($flickrPhoto) {
            $value = $flickrPhoto->ID;
            $this->flickrTitle = $flickrPhoto->Title;
            $this->flickrID = $flickrPhoto->FlickrID;
            $this->mediumURL = $flickrPhoto->MediumURL;
        }

        $this->addExtraClass('flickrPhotoSelectionField');

        parent::__construct($name, $title, $value, $form);
    }


    public function getFlickrTitle(): string
    {
        return $this->flickrTitle;
    }


    public function getFlickrID(): int
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


    /** @return array<string,string|int|float|bool|null> */
    public function getAttributes(): array
    {
        return \array_merge(
            parent::getAttributes(),
            [
                'maxlength' => $this->getMaxLength(),
                'size' => ($this->getMaxLength()) ? \min($this->getMaxLength(), 30) : null,
            ],
        );
    }


    /** @return \SilverStripe\ORM\FieldType\DBField|\SilverStripe\ORM\FieldType\DBHTMLText */
    public function InternallyLabelledField()
    {
        if (!$this->value) {
            $this->value = $this->Title();
        }

        return $this->Field();
    }


    /** @param array $properties */
    public function FieldHolder(array $properties = []): string
    {
        Requirements::javascript('weboftalent/flickr:dist/admin/client/js/flickredit.js');

        return parent::FieldHolder();
    }
}
