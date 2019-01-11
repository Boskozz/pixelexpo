<?php

namespace App\DataFixtures;

use App\Entity\Etiquette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DonneeFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $tabTag = [
            'Animaux' => 'Animaux',
            'Fleur' => 'Fleur',
            'Nature' => 'Nature',
            'Dessin' => 'Dessin',
            'Image de synthèse' => 'Image de synthèse',
        ];
        foreach ($tabTag as $tag) {
            $etiq = new Etiquette();
            $etiq->setName($tag);
            $manager->persist($etiq);
        }
        $manager->flush();
    }
}
