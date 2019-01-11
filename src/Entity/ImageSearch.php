<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class ImageSearch {

    /**
     * @var string|null
     */
    private $orientation;

    /**
     * @var ArrayCollection
     */
    private $etiquettes;

    /**
     * ImageSearch constructor.
     */
    public function __construct()
    {
        $this->etiquettes = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getOrientation(): ?string
    {
        return $this->orientation;
    }

    /**
     * @param string $orientation
     * @return ImageSearch
     */
    public function setOrientation(string $orientation): ImageSearch
    {
        $this->orientation = $orientation;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getEtiquettes(): ArrayCollection
    {
        return $this->etiquettes;
    }

    /**
     * @param ArrayCollection $etiquettes
     */
    public function setEtiquettes(ArrayCollection $etiquettes): void
    {
        $this->etiquettes = $etiquettes;
        //return $this;
    }

}