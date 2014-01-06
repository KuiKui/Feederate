<?php

namespace Feederate\FeederateBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use JMS\Serializer\Annotation as Serializer;

use Feederate\ORMBundle\Entity\BaseEntity;
use Feederate\ORMBundle\Entity\TimestampableTrait;
use Feederate\ORMBundle\Entity\ActivableTrait;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User extends BaseUser
{
    use TimestampableTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var UserFeeds[]
     *
     * @ORM\OneToMany(targetEntity="UserFeed", mappedBy="user")
     * @Serializer\Exclude()
     */
    private $userFeeds;

    /**
     * @var UserEntries[]
     *
     * @ORM\OneToMany(targetEntity="UserEntry", mappedBy="user")
     * @Serializer\Exclude()
     */
    private $userEntries;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userFeeds   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userEntries = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct();
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
     * Set status
     *
     * @param string $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add userFeeds
     *
     * @param \Feederate\FeederateBundle\Entity\UserFeed $userFeeds
     * @return User
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

    /**
     * Add userEntries
     *
     * @param \Feederate\FeederateBundle\Entity\UserEntry $userEntries
     * @return User
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
