<?php

namespace Feederate\FeederateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\UserFeed;

class LoadFeedData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $feedsDataFile = __DIR__.'/../../Resources/data/subscriptions.xml';
        if (!file_exists($feedsDataFile)) {
            throw new \Exception(sprintf("Le fichier %s n'existe pas", $feedsDataFile));
        }

        $feedsDataXml = simplexml_load_file($feedsDataFile);

        foreach ($feedsDataXml->body->outline as $feedXml) {
            $feed = new Feed();
            $feed
                ->setTitle($feedXml->attributes()->title)
                ->setUrl($feedXml->attributes()->xmlUrl)
                ->setTargetUrl($feedXml->attributes()->htmlUrl);

            $manager->persist($feed);

            $userFeed = new UserFeed();
            $userFeed
                ->setFeed($feed)
                ->setUser($manager->getRepository('FeederateFeederateBundle:User')->findOneBy([]));

            $manager->persist($userFeed);

            $manager->flush();
        }
    }
}
