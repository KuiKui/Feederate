<?php

namespace Feederate\FeederateBundle\Model;

use JMS\Serializer\Annotation as Serializer;

use Feederate\FeederateBundle\Entity\Entry;
use Feederate\FeederateBundle\Entity\UserEntry;

/**
 * Class Summary
 *
 * @author Florent Dubost <florent.dubost@gmail.com>
 */
class Summary
{
     /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     *
     */
    private $generatedId;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $authorName;

    /**
     * @var string
     *
     * @JMS\Serializer\Annotation\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $generatedAt;

    /**
     * @var boolean
     */
    protected $read = false;

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set generatedId
     *
     * @param integer $generatedId
     * @return Entry
     */
    public function setGeneratedId($generatedId)
    {
        $this->generatedId = $generatedId;

        return $this;
    }

    /**
     * Get generatedId
     *
     * @return integer
     */
    public function getGeneratedId()
    {
        return $this->generatedId;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Entry
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Entry
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set authorName
     *
     * @param string $authorName
     * @return Entry
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;

        return $this;
    }

    /**
     * Get authorName
     *
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * Set generatedAt
     *
     * @param \DateTime $generatedAt
     * @return Entry
     */
    public function setGeneratedAt($generatedAt)
    {
        $this->generatedAt = $generatedAt;

        return $this;
    }

    /**
     * Get generatedAt
     *
     * @return \DateTime
     */
    public function getGeneratedAt()
    {
        return $this->generatedAt;
    }

    /**
     * Set read
     *
     * @param boolean $read
     * @return UserEntry
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Get read
     *
     * @return boolean
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Load entity
     *
     * @param AuthorEntity $entity
     *
     * @return $this
     */
    public function load(Entry $entry, UserEntry $userEntry = null)
    {
        $this
            ->setId($entry->getId())
            ->setGeneratedId($entry->getGeneratedId())
            ->setTitle($entry->getTitle())
            ->setDescription($entry->getDescription())
            ->setAuthorName($entry->getAuthorName())
            ->setGeneratedAt($entry->getGeneratedAt());

        if ($userEntry) {
            $this->setRead($userEntry->getRead());
        }

        return $this;
    }
}
