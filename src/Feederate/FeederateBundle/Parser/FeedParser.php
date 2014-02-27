<?php

namespace Feederate\FeederateBundle\Parser;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Feed\Reader\Reader;
use Zend\Feed\Reader\Entry\EntryInterface;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\Entry;

class FeedParser
{
    const LOG_INFO    = 'question';
    const LOG_SUCCESS = 'info';
    const LOG_ERROR   = 'error';

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var Feed
     */
    private $feed;

    /**
     * @var integer
     */
    private $newEntries = 0;

    /**
     * @var integer
     */
    private $limitEntries = 20;

    /**
     * @var OutputInterface
     */
    private $output = null;

    /**
     * Constructor
     *
     * @param Feed          $feed
     * @param EntityManager $manager
     */
    public function __construct(Feed $feed, EntityManager $manager)
    {
        $this->feed        = $feed;
        $this->manager     = $manager;
    }

    public function setLimitEntries($limitEntries)
    {
        $this->limitEntries = $limitEntries;

        return $this;
    }

    /**
     * Set output
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Log message
     *
     * @param srting $message
     *
     * @return void
     */
    public function log($message, $type = self::LOG_INFO)
    {
        if ($this->output) {
            $this->output->writeln(sprintf("<%s>%s</%s>", $type, $message, $type));
        }
    }

    /**
     * Get new entries
     *
     * @return integer
     */
    public function getNewEntries()
    {
        return $this->newEntries;
    }

    /**
     * Increment new entries
     *
     * @return
     */
    public function incrementNewEntries()
    {
        $this->newEntries++;

        return $this;
    }

    /**
     * Parse
     *
     * @return integer Total entries
     */
    public function parse()
    {
        $this->log(sprintf("Import %s feed", $this->feed->getUrl()));
        try {
            $this->reader = Reader::import($this->feed->getUrl());

            // Update feed infos
            $this->updateFeed();
            $this->log("Feed informations updated", self::LOG_SUCCESS);

            /**
             * For limitEntries elements in reader
             * Update/create entry informations
             * Update user entry unread informations
             */
            while ($this->reader->key() < $this->limitEntries && $this->reader->key() < $this->reader->count()) {
                $rss = $this->reader->current();

                $entry = $this->manager
                    ->getRepository('FeederateFeederateBundle:Entry')
                    ->findOneBy(['generatedId' => $rss->getId(), 'feed' => $this->feed]);

                if (!$entry) {
                    $entry = new Entry();
                    $entry
                        ->setGeneratedId($rss->getId())
                        ->setFeed($this->feed);

                    $this->incrementNewEntries();
                }

                $this->updateEntry($entry, $rss);

                $this->reader->next();
            }

            $this->log("Entries informations updated", self::LOG_SUCCESS);

            $this->updateUser();
            $this->log("User informations updated", self::LOG_SUCCESS);

            return min($this->reader->count(), $this->limitEntries);
        } catch (\Exception $e) {
            $this->log(sprintf("Impossible to parse feed %s : %s", $this->feed->getUrl(), $e->getMessage()), self::LOG_ERROR);

            return 0;
        }
    }

    /**
     * Update feed
     *
     * @return void
     */
    private function updateFeed()
    {
        $this->feed->setTitle($this->reader->getTitle());

        $this->manager->persist($this->feed);
        $this->manager->flush();
    }

    /**
     * Update feed
     *
     * @return void
     */
    private function updateEntry(Entry $entry, EntryInterface $rss)
    {
        $entry
            ->setGeneratedAt($rss->getDateCreated() ?: $rss->getDateModified())
            ->setTitle($rss->getTitle())
            ->setTargetUrl($rss->getLink())
            ->setDescription(strip_tags($rss->getDescription()))
            ->setContent($rss->getContent());

        $this->manager->persist($entry);
        $this->manager->flush();
    }

    /**
     * Update user
     *
     * @return void
     */
    private function updateUser()
    {
        $userFeeds = $this->manager
            ->getRepository('FeederateFeederateBundle:UserFeed')
            ->findBy(['feed' => $this->feed]);

        foreach ($userFeeds as $userFeed) {
            $userFeed->incrUnreadCount($this->getNewEntries());

            $this->manager->persist($userFeed);
            $this->manager->flush();
        }
    }
}
