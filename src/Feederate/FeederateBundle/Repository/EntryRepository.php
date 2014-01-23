<?php

namespace Feederate\FeederateBundle\Repository;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Feederate\FeederateBundle\Entity\User;

/**
 * FeedRepository class
 */
class EntryRepository extends EntityRepository
{
    /**
     * findStarredByUser
     *
     * @param User    $user
     * @param array   $criteria
     * @param array   $orderBy
     * @param integer $limit
     * @param integer $offset
     *
     * @return array The entity instance or the entities collection
     */
    public function findByUserAndType(User $user, $type, array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        $queryBuilder = $this->createQueryBuilder('Entry')
            ->setParameter('user', $user);

        switch ($type) {
            case 'starred':
                $queryBuilder
                    ->join('Entry.userEntries', 'UserEntry')
                    ->where('UserEntry.user = :user')
                    ->andWhere('UserEntry.isStarred = true');
                break;
            case 'unread':
                $queryBuilder
                    ->leftJoin('Entry.userEntries', 'UserEntry')
                    ->join('Entry.feed', 'Feed')
                    ->join('Feed.userFeeds', 'UserFeed')
                    ->where('UserFeed.user = :user')
                    ->andWhere('UserEntry.isRead = false OR UserEntry.isRead IS NULL');
                break;
            default:
                throw \Exception('Unknown type');

        }

        if ($criteria) {
            foreach ($criteria as $field => $value) {
                $queryBuilder
                    ->andWhere(sprintf('Entry.%s = :%s', $field, $field))
                    ->setParameter($field, $value);
            }
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $queryBuilder
                    ->orderBy(sprintf('Entry.%s', $field), $order);
            }
        }

        if (array_key_exists('id', $criteria)) {
            return $queryBuilder
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
