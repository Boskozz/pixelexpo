<?php

namespace App\Services;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use App\Entity\Picture;

class DirectoryNamer implements DirectoryNamerInterface {

    public function directoryName($picture, PropertyMapping $mapping): string
    {
        $albumId = $picture->getAlbum()->getId();
        $projetId = $picture->getAlbum()->getProjet()->getId();
        $userId = $picture->getAlbum()->getProjet()->getUser()->getId();
        return $userId.'/'.$projetId.'/'.$albumId.'/';
    }

}