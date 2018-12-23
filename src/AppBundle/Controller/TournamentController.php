<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Tournament;
use AppBundle\Form\TournamentType;


class TournamentController extends Controller
{
    /**
     * @Route("/liste-des-competitions", name="listTournament")
     */
    public function indexAction(Request $request)
    {

        $manager = $this->getDoctrine()->getManager();

        $tournaments = $manager->getRepository(Tournament::class)->findAll();

        // replace this example code with whatever you need
        return $this->render('AppBundle:tournament:list.html.twig', ['tournaments' => $tournaments]);
    }

    /**
     * @Route ("/competition", name="editTournament")
     */
    public function editTournament(Request $request)
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        return $this->render('AppBundle:tournament:edit.html.twig', ['form' => $form->createView()]);
    }
}
