<?php

namespace AppBundle\Listener;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Log;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class MyLogActivityListener
{

  private $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  public function onKernelRequest(GetResponseEvent $event)
  {
    $route = $event->getRequest()->attributes->get('_route');

    $date = new \DateTime();

    $log = new Log();
    $log->setName($route);
    $log->setDescription($date->format('Y-m-d H:i:s'));

    $this->em->persist($log);
    $this->em->flush();
  }


}
