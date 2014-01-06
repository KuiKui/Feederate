<?php

namespace Feederate\FeederateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Feederate\FeederateBundle\Entity\User;

class LoadUserData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user
            ->setUsername('admin')
            ->setEmail('admin@feederate.fr')
            ->setPlainPassword('admin')
            ->setEnabled(true)
            ->setStatus('VIP')
            ->setSuperAdmin(true);

        $manager->persist($user);
        $manager->flush();

    }
}
