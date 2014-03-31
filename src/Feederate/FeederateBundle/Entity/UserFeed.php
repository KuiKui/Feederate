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
 * @ORM\Table(name="user_feed")
 * @ORM\Entity
 */
class UserFeed
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
     * @var integer
     *
     * @ORM\Column(name="unread_count", type="integer")
     */
    private $unreadCount = 0;

    /**
     * @var \Feederate\FeederateBundle\Entity\Feed
     *
     * @ORM\ManyToOne(targetEntity="Feed", inversedBy="userFeeds", cascade={"persist"})
     * @ORM\JoinColumn(name="feed_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Serializer\Exclude()
     */
    protected $feed;

    /**
     * @var \Feederate\FeederateBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userFeeds", cascade={"persist"})
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
     * Increment unreadCount
     * 
     * @param integer $count
     *
     * @return integer
     */
    public function incrUnreadCount($count = 1) {
        $this->unreadCount += $count;
        
        return $this;
    }

    /**
     * Decrement unreadCount
     * 
     * @param integer $count
     *
     * @return integer
     */
    public function decrUnreadCount($count = 1) {
        if ($this->unreadCount > 0) {
            $this->unreadCount -= $count;
        }
        
        return $this;
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
}
