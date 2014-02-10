<?php

namespace Feederate\FeederateBundle\Repository;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Feederate\FeederateBundle\Entity\User;
use Feederate\FeederateBundle\Entity\Feed;

/**
 * FeedRepository class
 */
class UserEntryRepository extends EntityRepository
{
    /**
     * Delete userEntries by user and feed
     *
     * @param User    $user
     * @param Feed    $feed
     *
     * @return int
     */
    public function deleteByUserAndFeed(User $user, Feed $feed)
    {
        $queryBuilder = $this->createQueryBuilder('UserEntry')
            ->delete('UserEntry')
            ->join('UserEntry.entry', 'Entry')
            ->where('UserEntry.user = :user')
            ->andWhere('Entry.feed = :feed')
            ->setParameter('user', $user)
            ->setParameter('feed', $feed);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
