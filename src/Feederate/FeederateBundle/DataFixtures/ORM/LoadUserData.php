<?php

namespace Feederate\FeederateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Feederate\FeederateBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }

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
