<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\Tournament;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, Session $session, EntityManagerInterface $em)
    {

        return $this->render('AppBundle:default:home.html.twig');
    }

    /**
     * @Route("/maroute", name="maroute")
     */
    public function maroute(Request $request, Session $session)
    {


        return $this->render('AppBundle:default:home.html.twig', array('title' => '<b>mon titre</b>'));
    }



}
