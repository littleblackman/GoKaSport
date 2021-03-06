<?php

namespace AppBundle\Repository;

/**
 * SportPositionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SportPositionRepository extends \Doctrine\ORM\EntityRepository
{

  public function findPositionBySport($sport)
  {
    return $this
          ->createQueryBuilder('s')
          ->where('s.sport = :sport')
          ->setParameter(':sport', $sport)
          ;
  }

}
