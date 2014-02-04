<?php

namespace Feederate\FeederateBundle\Importer;

/**
 * Importer class
 */
class Importer
{
    const IMPORTER_CLASS_PATTERN = 'Feederate\FeederateBundle\Importer\Platform\%sPlatform';

    const IMPORTER_PLATFORM_FEEDIN = 'Feedbin';

    protected $securityContext;

    protected $mananger;

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
     * @param EntityManager $manager
     */
    public function __construct($securityContext, $manager)
    {
        $this->securityContext = $securityContext;
        $this->manager         = $manager;
    }

    /**
     * Set securityContext
     *
     * @return this
     */
    public function setSecurityContext($securityContext)
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
    public function setManager($manager)
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

        $platform = new $platformclass($this->getSecurityContext(), $this->getManager());
        $platform->import($file);

        return array(
            'errors' => $platform->getErrors(),
            'parsed' => $platform->getParsed(),
        );
    }
}
