<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    
    public function __construct(UserPasswordEncoderInterface $passwordEncoder){
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setFirstName('Julien');
        $user->setLastName('Schiele');
        $user->setEmail('julien.schiele@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'test'));
        $manager->persist($user);
        
        $user = new User();
        $user->setFirstName('Ivan');
        $user->setLastName('Manzanilla');
        $user->setEmail('ivan.manzanilla@dilitrust.com');
        $user->setRoles(array(0 => 'ROLE_ADMIN'));
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'admin'));
        $manager->persist($user);
        
        $manager->flush();
    }
}
