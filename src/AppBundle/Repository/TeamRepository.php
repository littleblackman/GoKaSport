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

}