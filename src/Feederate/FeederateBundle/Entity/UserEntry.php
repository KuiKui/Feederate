<?php

namespace Feederate\FeederateBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

use Feederate\ORMBundle\Entity\BaseEntity;
use Feederate\ORMBundle\Entity\TimestampableTrait;
use Feederate\ORMBundle\Entity\ActivableTrait;

/**
 * User
 *
 * @ORM\Table(name="user_entry")
 * @ORM\Entity
 */
class UserEntry
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     * @Serializer\Exclude()
     */
    protected $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="read", type="boolean")
     */
    protected $read = false;

    /**
     * @var \Feederate\FeederateBundle\Entity\Entry
     *
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="userEntries")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Serializer\Exclude()
     */
    protected $entry;

    /**
     * @var \Feederate\FeederateBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userEntries")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Serializer\Exclude()
     */
    protected $user;

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
     * Set feed
     *
     * @param \Feederate\FeederateBundle\Entity\Feed $feed
     * @return UserHasFeed
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
     * Set user
     *
     * @param \Feederate\FeederateBundle\Entity\User $user
     * @return UserHasFeed
     */
    public function setUser(\Feederate\FeederateBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Feederate\FeederateBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
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
     * Set entry
     *
     * @param \Feederate\FeederateBundle\Entity\Entry $entry
     * @return UserEntry
     */
    public function setEntry(\Feederate\FeederateBundle\Entity\Entry $entry = null)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry
     *
     * @return \Feederate\FeederateBundle\Entity\Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }
}
