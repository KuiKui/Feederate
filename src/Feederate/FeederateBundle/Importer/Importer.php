<?php

namespace Feederate\FeederateBundle\Importer;

class Importer
{
    const IMPORTER_CLASS_PATTERN = 'Feederate\FeederateBundle\Importer\%sImporter';

    const IMPORTER_FEEDIN = 'Feedbin';

    protected $securityContext;

    protected $mananger;

    protected $type;

    /**
     * Returns all importer allowed
     * 
     * @return array
     */
    public static function getImporters()
    {
        return array(
            self::IMPORTER_FEEDIN,
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
     * Set type
     * 
     * @param string $type
     *
     * @return this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Import file
     * 
     * @return array
     */
    public function import($file)
    {
        $importerclass = sprintf(self::IMPORTER_CLASS_PATTERN, $this->getType());

        $importer = new $importerclass($this->getSecurityContext(), $this->getManager());
        $importer->import($file);

        return array(
            'errors' => $importer->getErrors(),
            'parsed' => $importer->getParsed(),
        );
    }
}
