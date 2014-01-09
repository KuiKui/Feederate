<?php

namespace Feederate\FeederateBundle\Repository;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Feederate\FeederateBundle\Entity\User;

/**
 * FeedRepository class
 */
class FeedRepository extends EntityRepository
{
    /**
     * findByUser
     *
     * @param User  $user
     * @param array $criteria
     * @param array $orderBy
     *
     * @return array The entity instance or the entities collection
     */
    public function findByUser(User $user, array $criteria = [], array $orderBy = null)
    {
        $queryBuilder = $this->createQueryBuilder('Feed')
            ->join('Feed.userFeeds', 'UserFeed')
            ->where('UserFeed.user = :user')
            ->setParameter('user', $user);

        if ($criteria) {
            foreach ($criteria as $field => $value) {
                $queryBuilder
                    ->andWhere(sprintf('Feed.%s = :%s', $field, $field))
                    ->setParameter($field, $value);
            }
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $queryBuilder
                    ->orderBy(sprintf('Feed.%s', $field), $order);
            }
        }

        if (array_key_exists('id', $criteria)) {
            return $queryBuilder
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
