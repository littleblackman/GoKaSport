<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Tournament;
use AppBundle\Form\TournamentType;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;

use AppBundle\Service\GameManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;




class TournamentController extends Controller
{

    private $currentUser;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
            $this->currentUser = $tokenStorage->getToken()->getUser();
    }


    /**
     * @Route("/liste-des-tournois.html", name="listTournament")
     */
    public function listAction(Request $request)
    {

        $manager = $this->getDoctrine()->getManager();

        $opens  = $manager->getRepository(Tournament::class)->findIsOpen();
        $owners = $manager->getRepository(Tournament::class)->findByCreatedBy($this->currentUser);
        $associates = $manager->getRepository(Tournament::class)->findAssociated($this->currentUser->getId());

        return $this->render('AppBundle:tournament:list.html.twig', ['opens' => $opens, 'owners' => $owners, 'associates' => $associates]);
    }

    /**
     * @Route ("/creation-tournoi", name="createTournament")
     * @Route ("/modification-tournoi/{id}", name="editTournament")
     */
    public function editTournament(Request $request, TokenStorageInterface $tokenStorage, $id = null)
    {
        $em = $this->getDoctrine()->getManager();

        if($id) {
            $tournament = $em->getRepository(Tournament::class)->find($id);
            $mode = 'Modification d\'un tournoi';
        } else {
          $tournament = new Tournament();
          $mode = 'Création d\'un tournoi';
          $id = 0;
        }

        $form = $this->createForm(TournamentType::class, $tournament);

        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted())
        {
            if($tournament->getId()>0) {
                $tournament->setUpdatedBy($tokenStorage->getToken()->getUser());

            } else {
                $tournament->setCreatedBy($tokenStorage->getToken()->getUser());
            }

            $em->persist($tournament);
            $em->flush();
            return $this->redirectToRoute('showTournament', ['id' => $tournament->getId()]);

        }
        // l'objet est renvoyé avec son contenu (widget, label, errors => Type)
        return $this->render('AppBundle:tournament:edit.html.twig', ['form' => $form->createView(), 'mode' => $mode, 'tournamentId' => $id]);
    }

    /**
     * @Route("/voir-le-tournoi/{id}", name="showTournament")
     */
    public function showTournament($id)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($id);

        return $this->render('AppBundle:tournament:show.html.twig', ['tournament' => $tournament]);

    }

    /**
     * @Route("/preparer-tournoi/{id}", name="prepareTournament")
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
     * @Route("/supprimer-list-team/{tournament_id}/{team_id}", name="deleteTeamAjax")
     */
    public function deleteTeamAjax($tournament_id = null, $team_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);
        $team = $em->getRepository(Team::class)->find($team_id);

        $tournament->removeTeam($team);
        $em->persist($tournament);
        $em->flush();

        $teams = $tournament->getTeams();
        return $this->render('AppBundle:tournament:teamsList.html.twig', ['teams' => $teams]);

    }

    /**
     * @Route("/supprimer-list-user/{tournament_id}/{user_id}", name="deleteUserAjax")
     */
    public function deleteUserAjax($tournament_id = null, $user_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $role = $user->getRoleString();

        $tournament->removeUser($user);
        $em->persist($tournament);
        $em->flush();

        $users = $tournament->getUsers($role);
        return $this->render('AppBundle:tournament:usersList.html.twig', ['users' => $users]);

    }

    /**
     * @Route("/ajout-list-user/{tournament_id}/{user_id}", name="addUser")
     */
    public function addUser($tournament_id = null, $user_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $tournament->addUser($user);
        $em->persist($tournament);
        $em->flush();

        $users = $tournament->getUsers($user->getRoleString());
        return $this->render('AppBundle:tournament:usersList.html.twig', ['users' => $users]);

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
