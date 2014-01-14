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
     * @var integer
     */
    private $feedId;

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
    private $isRead = false;

    /**
     * @var boolean
     */
    private $isStarred = false;

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
     * @param int $feedId
     *
     * @return $this
     */
    public function setFeedId($feedId)
    {
        $this->feedId = $feedId;

        return $this;
    }

    /**
     * Get feedId
     *
     * @return integer
     */
    public function getFeedId()
    {
        return $this->feedId;
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
     * Set isRead
     *
     * @param boolean $isRead
     * @return UserEntry
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * Get isRead
     *
     * @return boolean
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set isStarred
     *
     * @param boolean $isStarred
     * @return UserEntry
     */
    public function setIsStarred($isStarred)
    {
        $this->isStarred = $isStarred;

        return $this;
    }

    /**
     * Get isStarred
     *
     * @return boolean
     */
    public function getIsStarred()
    {
        return $this->isStarred;
    }

    /**
     * Load entity
     *
     * @param Entry     $entry
     * @param UserEntry $userEntry
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
            ->setGeneratedAt($entry->getGeneratedAt())
            ->setFeedId($entry->getFeed()->getId());

        if ($userEntry) {
            $this->setIsRead($userEntry->getIsRead());
            $this->setIsStarred($userEntry->getIsStarred());
        }

        return $this;
    }
}
