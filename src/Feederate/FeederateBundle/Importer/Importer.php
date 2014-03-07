<?php

namespace Feederate\FeederateBundle\Importer;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use Feederate\FeederateBundle\Manager\FeedManager;

/**
 * Importer class
 */
class Importer
{
    const IMPORTER_CLASS_PATTERN = 'Feederate\FeederateBundle\Importer\Platform\%sPlatform';

    const IMPORTER_PLATFORM_FEEDIN = 'Feedbin';

    protected $securityContext;

    protected $entityManager;

    protected $platform;

    /**
     * Returns all platforms allowed
     *
     * @return array
     */
    public static function getPlatforms()
    {
        return array(
            self::IMPORTER_PLATFORM_FEEDIN,
        );
    }

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
     * Set platform
     *
     * @param string $platform
     *
     * @return this
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Import file
     *
     * @return array
     */
    public function import($file)
    {
        $platformclass = sprintf(self::IMPORTER_CLASS_PATTERN, $this->getPlatform());

        $platform = new $platformclass($this->getSecurityContext(), $this->getEntityManager(), $this->getFeedManager());
        $platform->import($file);

        return array(
            'errors' => $platform->getErrors(),
            'parsed' => $platform->getParsed(),
        );
    }
}
