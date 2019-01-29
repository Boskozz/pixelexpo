<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Projet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('boskozz')
            ->setFirstName('Jean-Sébastien')
            ->setLastName('Wuilbaut')
            ->setEmail('jesewu@gmail.com')
            ->setPassword($this->encoder->encodePassword($user, '|>R$010NEly'))
            ->setRoles(['ROLE_ADMIN']);
        $user1 = new User();
        $user1->setUsername('jjwuil')
            ->setFirstName('Jean-Jacques')
            ->setLastName('Wuilbaut')
            ->setEmail('jjwuilbaut2@gmail.com')
            ->setPassword($this->encoder->encodePassword($user1, 'jjwjjw'))
            ->setRoles(['ROLE_USER']);
        $user2 = new User();
        $user2->setUsername('flomar')
            ->setFirstName('Florence')
            ->setLastName('Marcq')
            ->setEmail('florencemarcq1969@gmail.com')
            ->setPassword($this->encoder->encodePassword($user2, 'floflo'))
            ->setRoles(['ROLE_CREATOR']);
        $manager->persist($user);
        $manager->persist($user1);
        $manager->persist($user2);

        $manager->flush();
    }
}
