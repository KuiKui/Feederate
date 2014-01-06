<?php

namespace Feederate\ORMBundle\Entity;

/**
 * Define a publish date and a unpublish date for an entity
 */
trait PublishableTrait
{
    /**
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     * @JMS\Serializer\Annotation\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @var \DateTime
     */
    protected $publishedAt;

    /**
     * @ORM\Column(name="unpublished_at", type="datetime", nullable=true)
     * @JMS\Serializer\Annotation\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @var \DateTime
     */
    protected $unpublishedAt;

    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     *
     * @return Staff
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $this->normalizeDate($publishedAt);

        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set unpublishedAt
     *
     * @param \DateTime $unpublishedAt
     *
     * @return Staff
     */
    public function setUnpublishedAt($unpublishedAt)
    {
        $this->unpublishedAt = $this->normalizeDate($unpublishedAt);

        return $this;
    }

    /**
     * Get unpublishedAt
     *
     * @return \DateTime
     */
    public function getUnpublishedAt()
    {
        return $this->unpublishedAt;
    }
}
