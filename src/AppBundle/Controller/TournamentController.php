<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Tournament;
use AppBundle\Form\TournamentType;
use AppBundle\Entity\Team;
use AppBundle\Service\GameManager;


class TournamentController extends Controller
{
    /**
     * @Route("/liste-des-competitions", name="listTournament")
     */
    public function indexAction(Request $request)
    {

        $manager = $this->getDoctrine()->getManager();
        $tournaments = $manager->getRepository(Tournament::class)->findAll();
        return $this->render('AppBundle:tournament:list.html.twig', ['tournaments' => $tournaments]);
    }

    /**
     * @Route ("/creation-tournoi", name="createTournament")
     * @Route ("/modification-tournoi/{id}", name="editTournament")
     */
    public function editTournament(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();

        if($id) {
            $tournament = $em->getRepository(Tournament::class)->find($id);
            $mode = 'modification';
        } else {
          $tournament = new Tournament();
          $mode = 'create';
          $id = 0;
        }

        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted())
        {
            $em->persist($tournament);
            $em->flush();
            return $this->redirectToRoute('listTournament');

        }
        return $this->render('AppBundle:tournament:edit.html.twig', ['form' => $form->createView(), 'mode' => $mode, 'tournamentId' => $id]);
    }

    /**
     * @Route("/preparer-tournoi/{id}", name="showTournament")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($id);

        // case redirection

        // tournament is init / not group made
        if($tournament->getIsInit() == 1) return $this->redirectToRoute('manageGame', ['tournamentId' => $tournament->getId()]);

        return $this->render('AppBundle:tournament:show.html.twig', ['tournament' => $tournament]);

    }

    /**
     * @Route("/supprime-un-tournoi/{id}", name="delTournament")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($id);
        $em->remove($tournament);
        $em->flush();
        return $this->redirectToRoute('listTournament');
    }

    /**
     * @Route("/ajout-list-team/{tournament_id}/{team_id}", name="addTeam")
     */
    public function addTeam($tournament_id = null, $team_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);
        $team = $em->getRepository(Team::class)->find($team_id);

        $tournament->addTeam($team);
        $em->persist($tournament);
        $em->flush();

        $teams = $tournament->getTeams();
        return $this->render('AppBundle:tournament:teamsList.html.twig', ['teams' => $teams]);


    }

    /**
     *
     * @Route("/ouvrir-le-tournoi/{tournamentId}", name="openTournament")
     */
    public function openTournament($tournamentId)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($tournamentId);
        $tournament->setIsOpen(1);
        $em->persist($tournament);
        $em->flush();

        return $this->redirectToRoute('showMatchGroup', ['tournamentId' => $tournamentId]);

    }

    /**
     *
     * @Route("/reinit-le-tournoi/{tournamentId}/{type}", name="resetTournament" )
     */
    public function resetTournament(GameManager $gameManager, $tournamentId, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($tournamentId);
        $gameManager->setTournament($tournament);
        $gameManager->resetTournament($type);

        return $this->redirectToRoute('editTournament', ['id' => $tournament->getId()]);
    }
}
