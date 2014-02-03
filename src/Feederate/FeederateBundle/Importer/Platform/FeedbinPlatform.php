<?php

namespace Feederate\FeederateBundle\Importer\Platform;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\UserFeed;
use Feederate\FeederateBundle\Parser\FeedParser;

class FeedbinPlatform extends AbstractPlatform
{
    /**
     * {@inheritdoc}
     */
    public function import($file)
    {
        $feedsDataXml = simplexml_load_file($file);

        foreach ($feedsDataXml->body->outline as $feedXml) {
            $this->getManager()->getConnection()->beginTransaction();

            $feed = $this->getManager()
                ->getRepository('FeederateFeederateBundle:Feed')
                ->findOneBy(['url' => $feedXml->attributes()->xmlUrl]);

            if (!$feed) {
                $feed = new Feed();
                $feed
                    ->setTitle($feedXml->attributes()->title)
                    ->setUrl($feedXml->attributes()->xmlUrl)
                    ->setTargetUrl($feedXml->attributes()->htmlUrl);
            }

            $this->getManager()->persist($feed);

            $userFeed = new UserFeed();
            $userFeed
                ->setFeed($feed)
                ->setUser($this->getSecurityContext()->getToken()->getUser());

            $this->getManager()->persist($userFeed);

            $this->getManager()->flush();

            if ($this->parseFeed($feed)) {
                $this->getManager()->getConnection()->commit();
                $this->incrementParsed();
            } else {
                $this->getManager()->getConnection()->rollback();
                $this->addError(sprintf("It's not possible to parse the feed %s", $feed->getTitle()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function parseFeed(Feed $feed)
    {
        try {
            $feedParser = new FeedParser($feed, $this->getManager());
            $feedParser->parse();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
