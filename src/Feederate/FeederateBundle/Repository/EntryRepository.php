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
     * @param User  $user
     * @param array $criteria
     * @param array $orderBy
     *
     * @return array The entity instance or the entities collection
     */
    public function findByUserAndType(User $user, $type, array $criteria = [], array $orderBy = null)
    {
        $queryBuilder = $this->createQueryBuilder('Entry')
            ->join('Entry.userEntries', 'UserEntry')
            ->where('UserEntry.user = :user')
            ->setParameter('user', $user);

        switch ($type) {
            case 'starred':
                $queryBuilder->andWhere('UserEntry.isStarred = true');
                break;
            case 'unread':
                // Next step
                // $queryBuilder->andWhere('UserEntry.isRead = false');
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
            ->getQuery()
            ->getResult();
    }
}