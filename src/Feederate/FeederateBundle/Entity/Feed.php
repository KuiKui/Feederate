<?php

namespace Feederate\FeederateBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

use Feederate\ORMBundle\Entity\BaseEntity;
use Feederate\ORMBundle\Entity\TimestampableTrait;

/**
 * Feed
 *
 * @ORM\Table(name="feed")
 * @ORM\Entity(repositoryClass="Feederate\FeederateBundle\Repository\FeedRepository")
 */
class Feed
{
    use TimestampableTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="generated_id", type="integer", nullable=true)
     */
    private $generatedId;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="target_url", type="string", length=255)
     */
    private $targetUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="author_name", type="string", length=255, nullable=true)
     */
    private $authorName;

    /**
     * @var string
     *
     * @ORM\Column(name="author_email", type="string", length=255, nullable=true)
     */
    private $authorEmail;

    /**
     * @var date
     *
     * @ORM\Column(name="generated_at", type="datetime", nullable=true)
     */
    private $generatedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="unused", type="boolean")
     */
    private $unused;

    /**
     * @ORM\OneToMany(targetEntity="Feederate\FeederateBundle\Entity\Entry", mappedBy="feed", cascade={"persist", "remove"})
     * @ORM\OrderBy({"updatedAt" = "ASC"})
     */
    private $entries;

    /**
     * @var userFeeds[]
     *
     * @ORM\OneToMany(targetEntity="UserFeed", mappedBy="feed", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $userFeeds;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entries   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userFeeds = new \Doctrine\Common\Collections\ArrayCollection();
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
        $this->url = (string) $url;

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
        $this->title = (string) $title;

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
        $this->description = (string) $description;

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
        $this->targetUrl = (string) $targetUrl;

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
        $this->authorName = (string) $authorName;

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
        $this->authorEmail = (string) $authorEmail;

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
     * Set unused
     *
     * @param boolean $unused
     * @return Feed
     */
    public function setUnused($unused)
    {
        $this->unused = (string) $unused;

        return $this;
    }

    /**
     * Get unused
     *
     * @return boolean
     */
    public function getUnused()
    {
        return $this->unused;
    }

    /**
     * Add entries
     *
     * @param \Feederate\FeederateBundle\Entity\Entry $entries
     * @return Feed
     */
    public function addEntrie(\Feederate\FeederateBundle\Entity\Entry $entries)
    {
        $this->entries[] = $entries;

        return $this;
    }

    /**
     * Remove entries
     *
     * @param \Feederate\FeederateBundle\Entity\Entry $entries
     */
    public function removeEntrie(\Feederate\FeederateBundle\Entity\Entry $entries)
    {
        $this->entries->removeElement($entries);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Add userFeeds
     *
     * @param \Feederate\FeederateBundle\Entity\UserFeed $userFeeds
     * @return Feed
     */
    public function addUserFeed(\Feederate\FeederateBundle\Entity\UserFeed $userFeeds)
    {
        $this->userFeeds[] = $userFeeds;

        return $this;
    }

    /**
     * Remove userFeeds
     *
     * @param \Feederate\FeederateBundle\Entity\UserFeed $userFeeds
     */
    public function removeUserFeed(\Feederate\FeederateBundle\Entity\UserFeed $userFeeds)
    {
        $this->userFeeds->removeElement($userFeeds);
    }

    /**
     * Get userFeeds
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserFeeds()
    {
        return $this->userFeeds;
    }
}
