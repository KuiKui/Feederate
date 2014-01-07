<?php

namespace Feederate\FeederateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Feederate\FeederateBundle\Entity\Feed;

class LoadFeedData implements FixtureInterface
{
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
            $manager->flush();
        }
    }
}
