<?php

namespace Feederate\FeederateBundle\Importer\Platform;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\UserFeed;
use Feederate\FeederateBundle\Parser\FeedParser;

/**
 * FeedbinPlatform class
 */
class FeedbinPlatform extends AbstractPlatform
{
    /**
     * {@inheritdoc}
     */
    public function import($file)
    {
        $feedsDataXml = simplexml_load_file($file);

        foreach ($feedsDataXml->body->outline as $feedXml) {
            $this->getEntityManager()->getConnection()->beginTransaction();

            $feed = new Feed();
            $feed
                ->setTitle($feedXml->attributes()->title)
                ->setUrl($feedXml->attributes()->xmlUrl)
                ->setTargetUrl($feedXml->attributes()->htmlUrl);

            $this->getFeedManager()->saveUserFeed(
                $this->getSecurityContext()->getToken()->getUser(),
                $feed
            );

            if ($this->parseFeed($feed)) {
                $this->getEntityManager()->getConnection()->commit();
                $this->incrementParsed();
            } else {
                $this->getEntityManager()->getConnection()->rollback();
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
            $feedParser = new FeedParser($feed, $this->getEntityManager());
            $feedParser->parse();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
