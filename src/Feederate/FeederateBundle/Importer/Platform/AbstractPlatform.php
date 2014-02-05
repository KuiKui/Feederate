<?php

namespace Feederate\FeederateBundle\Importer\Platform;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;

use Feederate\FeederateBundle\Entity\Feed;

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
    protected $mananger;

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
     * @param EntityManager $manager
     */
    public function __construct(SecurityContext $securityContext, EntityManager $manager)
    {
        $this->securityContext = $securityContext;
        $this->manager         = $manager;
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
     * Set manager
     *
     * @return this
     */
    public function setManager(EntityManager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager
     *
     * @return EntityManager
     */
    public function getManager()
    {
        return $this->manager;
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
