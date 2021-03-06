<?php

namespace AppBundle\Repository;

/**
 * TeamRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TeamRepository extends \Doctrine\ORM\EntityRepository
{

    public function findLike($search)
    {
      return $this
            ->createQueryBuilder('t')
            ->where('t.name like :search')
            ->setParameter(':search', '%'.$search.'%')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByCreatedBy($user)
    {
      return $this
            ->createQueryBuilder('t')
            ->where('t.createdBy = :user')
            ->setParameter(':user', $user)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

    }


    public function findAssociated($user_id)
    {
      return $this
            ->createQueryBuilder('t')
            ->innerJoin('t.users', 'u')
            ->where('u.id = :user_id')
            ->setParameter(':user_id', $user_id)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

    }

}
