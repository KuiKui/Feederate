<?php

namespace Feederate\FeederateBundle\Importer;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use Feederate\FeederateBundle\Manager\FeedManager;
use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Parser\FeedParser;

use Celd\Opml\Importer as OpmlImporter;

/**
 * Importer class
 */
class Importer
{
    protected $securityContext;

    protected $entityManager;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @var integer
     */
    protected $parsed = 0;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(SecurityContext $securityContext, EntityManager $entityManager, FeedManager $feedManager)
    {
        $this->securityContext = $securityContext;
        $this->entityManager   = $entityManager;
        $this->feedManager     = $feedManager;
    }

    /**
     * Set securityContext
     *
     * @return this
     */
    public function setSecurityContext(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;

        return $this;
    }

    /**
     * Get securityContext
     *
     * @return SecurityContext
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * Set entityManager
     *
     * @return this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Get entityManager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Set feedManager
     *
     * @return this
     */
    public function setFeedManager(FeedManager $feedManager)
    {
        $this->feedManager = $feedManager;

        return $this;
    }

    /**
     * Get feedManager
     *
     * @return FeedManager
     */
    public function getFeedManager()
    {
        return $this->feedManager;
    }

    /**
     * Increment number of parsed feed
     *
     * @return this
     */
    protected function incrementParsed()
    {
        $this->parsed++;

        return $this;
    }

    /**
     * Get number of parsed feed
     *
     * @return integer
     */
    public function getParsed()
    {
        return $this->parsed;
    }

    /**
     * Add error
     *
     * @param string $message
     *
     * @return this
     */
    protected function addError($message)
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Import file
     *
     * @return array
     */
    public function import($file)
    {
        $importer = new OpmlImporter(file_get_contents($file));
        $feedList = $importer->getFeedList();

        foreach ($feedList->getItems() as $item) {
            if ($item->getType() == 'category') {
                foreach($item->getFeeds() as $feed) {
                    $this->addFeed($feed);
                }
            } else {
                $this->addFeed($item);
            }
        }

        return array(
            'errors' => $this->getErrors(),
            'parsed' => $this->getParsed(),
        );
    }

    protected function addFeed($opmlItem)
    {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $feed = new Feed();
            $feed->setTitle($opmlItem->getTitle())
                ->setUrl($opmlItem->getXmlUrl())
                ->setTargetUrl($opmlItem->getHtmlUrl());

            $this->getFeedManager()->saveUserFeed(
                $this->getSecurityContext()->getToken()->getUser(),
                $feed
            );

            $this->getEntityManager()->getConnection()->commit();
            $this->incrementParsed();
        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollback();
            $this->addError(sprintf("It's not possible to parse the feed %s. (Exception: %s)", $feed->getTitle(), $e->getMessage()));
        }
    }
}
