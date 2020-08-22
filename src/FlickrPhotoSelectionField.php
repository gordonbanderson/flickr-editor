<?php declare(strict_types = 1);

namespace Suilven\Flickr;

use SilverStripe\Forms\HiddenField;
use SilverStripe\View\Requirements;

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
     * Returns an input field, class="text" and type="text" with an optional maxlength
     */
    public function __construct($name, $title = null, $flickrPhoto = '', $maxLength = null, $form = null)
    {
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


    public function getFlickrTitle()
    {
        return $this->flickrTitle;
    }


    public function getFlickrID()
    {
        return $this->flickrID;
    }


    public function getMediumURL()
    {
        return $this->mediumURL;
    }


    public function setMaxLength(int $length)
    {
        $this->maxLength = $length;

        return $this;
    }


    public function getMaxLength(): int
    {
        return $this->maxLength;
    }


    public function getAttributes()
    {
        return \array_merge(
            parent::getAttributes(),
            [
                'maxlength' => $this->getMaxLength(),
                'size' => ($this->getMaxLength()) ? \min($this->getMaxLength(), 30) : null
            ],
        );
    }


    public function InternallyLabelledField()
    {
        if (!$this->value) {
            $this->value = $this->Title();
        }

        return $this->Field();
    }


    public function FieldHolder($properties = [])
    {
        Requirements::javascript('weboftalent/flickr:dist/admin/client/js/flickredit.js');

        return parent::FieldHolder();
    }
}
