<?php

namespace Feederate\FeederateBundle\Importer\Platform;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;

/**
 *
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
     * Set manager
     *
     * @return this
     */
    public function setManager(EntityManager $manager);

    /**
     * Get manager
     *
     * @return EntityManager
     */
    public function getManager();

    /**
     * Import file
     *
     * @return void
     */
    public function import($file);
}
