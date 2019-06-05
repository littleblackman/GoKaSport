<?php

namespace AppBundle\Listener;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Log;


class MyTeamListener
{

  private $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  public function sendMail($event)
  {

    $log = new Log();
    $log->setName("création d'une équipe");
    $log->setDescription($event->getSubject()->getName());

    $this->em->persist($log);
    $this->em->flush();
  }


}
