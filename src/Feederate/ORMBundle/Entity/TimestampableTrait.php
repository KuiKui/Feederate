<?php

namespace Feederate\ORMBundle\Entity;

/**
 * Define a creation date and a update date for an entity
 */
trait TimestampableTrait
{
    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @JMS\Serializer\Annotation\Type("DateTime<'Y-m-d H:i:s'>")
     */
    protected $createdAt;

    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     * @JMS\Serializer\Annotation\Type("DateTime<'Y-m-d H:i:s'>")
     */
    protected $updatedAt;

    /**
     * Set creation date
     *
     * @param \DateTime $date Creation date
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $date)
    {
        $this->createdAt = $this->normalizeDate($date);

        return $this;
    }

    /**
     * Get creation date
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set update date
     *
     * @param \DateTime $date Update date
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $date)
    {
        $this->updatedAt = $this->normalizeDate($date);

        return $this;
    }

    /**
     * Get update date
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
