<?php

namespace Feederate\FeederateBundle\Manager;

use Doctrine\ORM\EntityManager;

use Feederate\FeederateBundle\Entity\Feed;
use Feederate\FeederateBundle\Entity\User;
use Feederate\FeederateBundle\Entity\UserEntry;

/**
 * FeedManager class
 */
class FeedManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     * 
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Set a whole feed as read (or unread)
     *
     * @param User $user
     * @param Feed $feed
     * @param bool $isRead
     *
     * @return $this
     */
    public function setIsRead(User $user, Feed $feed, $isRead)
    {
        // Update userEntries
        $entries = $feed->getEntries();

        foreach ($entries as $entry) {
            $userEntry = $this
                ->em
                ->getRepository('FeederateFeederateBundle:UserEntry')
                ->findOneBy(['user' => $user, 'entry' => $entry]);

            if ($isRead && !$userEntry) {
                $userEntry = new UserEntry();
                $userEntry
                    ->setEntry($entry)
                    ->setUser($user);

                $this->em->persist($userEntry);
            }

            if ($userEntry) {
                $userEntry->setIsRead($isRead);
            }
        }

        // Update userFeed
        $userFeed = $this
            ->em
            ->getRepository('FeederateFeederateBundle:UserFeed')
            ->findOneBy(['user' => $user, 'feed' => $feed]);

        $userFeed->setUnreadCount(!$isRead ? count($entries) : 0);

        $this->em->flush();

        return $this;
    }

    /**
     * Set starred feed as read (or unread)
     *
     * @param User $user
     * @param bool $isRead
     *
     * @return $this
     */
    public function setStarredIsRead(User $user, $isRead)
    {
        // Update userEntries
        $userEntries = $this
            ->em
            ->getRepository('FeederateFeederateBundle:UserEntry')
            ->findBy(['user' => $user, 'isStarred' => true]);

        $feedsToUpdate = [];
        foreach ($userEntries as $userEntry) {           
            if ($isRead != $userEntry->getIsRead()) {
                $feed = $userEntry->getEntry()->getFeed();
                
                if (isset($feedsToUpdate[$feed->getId()])) {
                    $feedsToUpdate[$feed->getId()]['count']++;
                } else {
                    $feedsToUpdate[$feed->getId()] = ['feed' => $feed, 'count' => 1];
                }

                $userEntry->setIsRead($isRead);
            }
        }

        // Update userFeeds
        foreach ($feedsToUpdate as $data) {
            $userFeed = $this
                ->em
                ->getRepository('FeederateFeederateBundle:UserFeed')
                ->findOneBy(['user' => $user, 'feed' => $data['feed']]);

            if (!$isRead) {
                $userFeed->incrUnreadCount($data['count']);
            } else {
                $userFeed->decrUnreadCount($data['count']);
            }
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Set unread feed as read
     *
     * @param User $user
     *
     * @return $this
     */
    public function setUnreadAsRead(User $user)
    {
        // Update userEntries
        $entries = $this
            ->em
            ->getRepository('FeederateFeederateBundle:Entry')
            ->findByUserAndType($user, 'unread');

        $feedsToUpdate = [];
        foreach ($entries as $entry) {
            $userEntry = $this
                ->em
                ->getRepository('FeederateFeederateBundle:UserEntry')
                ->findOneBy(['user' => $user, 'entry' => $entry]);

            if (!$userEntry) {
                $userEntry = new UserEntry();
                $userEntry
                    ->setEntry($entry)
                    ->setUser($user);

                $this->em->persist($userEntry);
            }

            $userEntry->setIsRead(true);

            $feed = $entry->getFeed();
            $feedsToUpdate[$feed->getId()] = $feed;
        }

        // Update userFeeds
        foreach ($feedsToUpdate as $feed) {
            $userFeed = $this
                ->em
                ->getRepository('FeederateFeederateBundle:UserFeed')
                ->findOneBy(['user' => $user, 'feed' => $feed]);

            $userFeed->setUnreadCount(0);
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Get Feed repository
     * 
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('FeederateFeederateBundle:Feed');
    }
}
