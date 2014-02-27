<?php

namespace Feederate\FeederateBundle\Importer\Platform;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use Feederate\FeederateBundle\Manager\FeedManager;

/**
 * PlatformInterface class
 */
interface PlatformInterface
{
    /**
     * Set securityContext
     *
     * @return this
     */
    public function setSecurityContext(SecurityContext $securityContext);

    /**
     * Get securityContext
     *
     * @return SecurityContext
     */
    public function getSecurityContext();

    /**
     * Set entityManager
     *
     * @return this
     */
    public function setEntityManager(EntityManager $entityManager);

    /**
     * Get entityManager
     *
     * @return EntityManager
     */
    public function getEntityManager();

    /**
     * Set feedManager
     *
     * @return this
     */
    public function setFeedManager(FeedManager $feedManager);

    /**
     * Get feedManager
     *
     * @return FeedManager
     */
    public function getFeedManager();

    /**
     * Import file
     *
     * @return void
     */
    public function import($file);
}
