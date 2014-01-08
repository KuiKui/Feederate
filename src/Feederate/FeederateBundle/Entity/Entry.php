<?php

namespace Feederate\FeederateBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

use Feederate\ORMBundle\Entity\BaseEntity;
use Feederate\ORMBundle\Entity\TimestampableTrait;

/**
 * Entry
 *
 * @ORM\Table(name="entry")
 * @ORM\Entity
 */
class Entry
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
     * @var string
     *
     * @ORM\Column(name="generated_id", type="string", nullable=true)
     */
    private $generatedId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=255)
     */
    private $content;

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
     * @var string
     *
     * @ORM\Column(name="generated_at", type="datetime", nullable=true)
     * @JMS\Serializer\Annotation\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $generatedAt;

    /**
     * @var \Feederate\FeederateBundle\Entity\Feed
     *
     * @ORM\ManyToOne(targetEntity="Feed", inversedBy="entries")
     * @ORM\JoinColumn(name="feed_id", referencedColumnName="id")
     * @Serializer\Exclude()
     * @Assert\NotBlank()
     */
    private $feed;

    /**
     * @var UserEntries[]
     *
     * @ORM\OneToMany(targetEntity="UserEntry", mappedBy="entry")
     * @Serializer\Exclude()
     */
    private $userEntries;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userEntries = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set content
     *
     * @param string $content
     * @return Entry
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
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
     * Set authorEmail
     *
     * @param string $authorEmail
     * @return Entry
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
     * Set feed
     *
     * @param \Feederate\FeederateBundle\Entity\Feed $feed
     * @return Entry
     */
    public function setFeed(\Feederate\FeederateBundle\Entity\Feed $feed = null)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return \Feederate\FeederateBundle\Entity\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * Add userEntries
     *
     * @param \Feederate\FeederateBundle\Entity\UserEntry $userEntries
     * @return Entry
     */
    public function addUserEntrie(\Feederate\FeederateBundle\Entity\UserEntry $userEntries)
    {
        $this->userEntries[] = $userEntries;

        return $this;
    }

    /**
     * Remove userEntries
     *
     * @param \Feederate\FeederateBundle\Entity\UserEntry $userEntries
     */
    public function removeUserEntrie(\Feederate\FeederateBundle\Entity\UserEntry $userEntries)
    {
        $this->userEntries->removeElement($userEntries);
    }

    /**
     * Get userEntries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserEntries()
    {
        return $this->userEntries;
    }
}
