<?php

namespace Feederate\FeederateBundle\Model;

use JMS\Serializer\Annotation as Serializer;

use Feederate\FeederateBundle\Entity\Feed as FeedEntity;
use Feederate\FeederateBundle\Entity\UserFeed;

/**
 * Class Feef
 *
 * @author Florent Dubost <florent.dubost@gmail.com>
 */
class Feed
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $generatedId;

    /**
     * @var string
     */
    private $url;

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
    private $targetUrl;

    /**
     * @var string
     */
    private $authorName;

    /**
     * @var string
     */
    private $authorEmail;

    /**
     * @var date
     * 
     * @JMS\Serializer\Annotation\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $generatedAt;

    /**
     * @var integer
     */
    private $unreadCount = 0;

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
     * @return Feed
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
     * Set url
     *
     * @param string $url
     * @return Feed
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Feed
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
     * @return Feed
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
     * Set targetUrl
     *
     * @param string $targetUrl
     * @return Feed
     */
    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;

        return $this;
    }

    /**
     * Get targetUrl
     *
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Set authorName
     *
     * @param string $authorName
     * @return Feed
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
     * Set authorEmail
     *
     * @param string $authorEmail
     * @return Feed
     */
    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    /**
     * Get authorEmail
     *
     * @return string
     */
    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    /**
     * Set generatedAt
     *
     * @param \DateTime $generatedAt
     * @return Feed
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
     * Set unreadCount
     *
     * @param integer $unreadCount
     * @return UserHasFeed
     */
    public function setUnreadCount($unreadCount)
    {
        $this->unreadCount = $unreadCount;

        return $this;
    }

    /**
     * Get unreadCount
     *
     * @return integer
     */
    public function getUnreadCount()
    {
        return $this->unreadCount;
    }

    /**
     * Load entity
     *
     * @param FeedEntity $feed
     * @param UserFeed   $userFeed
     *
     * @return $this
     */
    public function load(FeedEntity $feed, UserFeed $userFeed)
    {
        $this
            ->setId($feed->getId())
            ->setGeneratedId($feed->getGeneratedId())
            ->setTitle($feed->getTitle())
            ->setDescription($feed->getDescription())
            ->setTargetUrl($feed->getTargetUrl())
            ->setAuthorName($feed->getAuthorName())
            ->setAuthorEmail($feed->getAuthorEmail())
            ->setGeneratedAt($feed->getGeneratedAt())
            ->setUnreadCount($userFeed->getUnreadCount());

        return $this;
    }
}
