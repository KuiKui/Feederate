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
     * Find userEntries by user and feed
     *
     * @param User    $user
     * @param Feed    $feed
     * @param array   $criteria
     * @param array   $orderBy
     * @param integer $limit
     * @param integer $offset
     *
     * @return int
     */
    public function findByUserAndFeed(User $user, Feed $feed, array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        $queryBuilder = $this->createQueryBuilder('UserEntry')
            ->join('UserEntry.entry', 'Entry')
            ->where('UserEntry.user = :user')
            ->andWhere('Entry.feed = :feed')
            ->setParameter('user', $user)
            ->setParameter('feed', $feed);

        if ($criteria) {
            foreach ($criteria as $field => $value) {
                $queryBuilder
                    ->andWhere(sprintf('UserEntry.%s = :%s', $field, $field))
                    ->setParameter($field, $value);
            }
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $queryBuilder
                    ->orderBy(sprintf('UserEntry.%s', $field), $order);
            }
        }

        return $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
