<?php

namespace Feederate\ORMBundle\Entity;

/**
 * Define an active status for an entity
 */
trait ActivableTrait
{
    /**
     * @ORM\Column(name="active", type="boolean")
     *
     * @var boolean
     */
    protected $active;

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Staff
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
}
