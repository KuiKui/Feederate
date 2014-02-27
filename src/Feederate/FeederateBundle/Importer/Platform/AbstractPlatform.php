<?php

namespace Feederate\FeederateBundle\Importer\Platform;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Manager\FeedManager;

/**
 * AbstractPlatform class
 */
abstract class AbstractPlatform implements PlatformInterface
{
    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var FeedManager
     */
    protected $feedManager;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @var integer
     */
    protected $parsed = 0;

    /**
     * Import file
     *
     * @return void
     */
    abstract public function import($file);

    /**
     * Parse feed
     *
     * @param Feed $feed
     *
     * @return void
     */
    abstract protected function parseFeed(Feed $feed);

    /**
     * Constructor
     *
     * @param SecurityContext $securityContext
     * @param EntityManager   $entityManager
     * @param FeedManager     $feedManager
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
}
