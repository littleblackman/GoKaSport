<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use AppBundle\Entity\Player;
use AppBundle\Form\TeamType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



class TeamController extends Controller
{

    private $currentUser;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->currentUser = $tokenStorage->getToken()->getUser();
    }



    /**
    * @Route("/liste-des-equipes", name="listTeam")
     */
    public function listAction(Request $request)
    {

        $manager = $this->getDoctrine()->getManager();

        $owners = $manager->getRepository(Team::class)->findByCreatedBy($this->currentUser);
        $associates = $manager->getRepository(Team::class)->findAssociated($this->currentUser->getId());

        return $this->render('AppBundle:team:list.html.twig', ['owners' => $owners, 'associates' => $associates]);
    }

    /**
     * @Route("/voir-equipe/{teamId}", name="showTeam")
     */
    public function showTeam(Request $request, $teamId) {
        $team = $this->getDoctrine()->getManager()->getRepository(Team::class)->find($teamId);
        return $this->render('AppBundle:team:show.html.twig', ['team' => $team]);
    }

    /**
     * @Route("/creer-une-equipe", name="createTeam")
     * @Route("/modifier-equipe/{id}", name="editTeam")
     */
    public function editTeam(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $originalPlayers = new ArrayCollection();

        if($id)
        {
          $team = $em->getRepository(Team::class)->find($id);
          foreach($team->getPlayers() as $player)
          {
              $originalPlayers->add($player);
          }
          $mode = "Modification";
        } else {
          $team = new Team();

          $mode = "Création";
        }

          $form = $this->createForm(TeamType::class, $team);
          $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted())
        {
            foreach($originalPlayers as $player)
            {
                if(false === $team->getPlayers()->contains($player)) $em->remove($player);
            }

            foreach($team->getPlayers() as $player)
            {
               $team->addPlayer($player);
            }

            if($mode == "Création") $team->setCreatedBy($this->currentUser);
            if($mode == "Modification") $team->setUpdatedBy($this->currentUser);

            $em->persist($team);
            $em->flush();

            return $this->redirectToRoute('showTeam', ['teamId' => $team->getId()]);
        }

        return $this->render('AppBundle:team:edit.html.twig', ['form' => $form->createView(), 'mode' => $mode]);

    }

    /**
     * @Route("/ajout-joueur-team/{team_id}/{user_id}", name="addPlayerTeam")
     */
    public function addPlayerTeam($team_id = null, $user_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $team->addUser($user);
        $em->persist($team);
        $em->flush();

        $users = $team->getUsers($user->getRoleString());
        return $this->render('AppBundle:team:usersList.html.twig', ['users' => $users]);

    }

    /**
     * @Route("/ajout-coach-team/{team_id}/{user_id}", name="addCoachTeam")
     */
    public function addCoachTeam($team_id = null, $user_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $team->addUser($user);
        $em->persist($team);
        $em->flush();

        $users = $team->getUsers($user->getRoleString());
        return $this->render('AppBundle:team:usersList.html.twig', ['users' => $users]);

    }


    /**
     * @Route("/playerListTeam/{team_id}", name="playerListTeam")
     */
    public function playerListTeam($team_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        return $this->render('AppBundle:team:playersList.html.twig', ['users' => $team->getPlayers()]);

    }


    /**
     * @Route("/supprimer-coach-team/{team_id}/{user_id}", name="deleteUserCoachAjax")
     */
    public function deleteUserCoachAjax($team_id = null, $user_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $team->removeUser($user);
        $em->persist($team);
        $em->flush();

        $users = $team->getUsers($user->getRoleString());
        return $this->render('AppBundle:team:usersList.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/get-teams-ajax/{search}", name="teamsListAjax", defaults={"search" = null})
     */
    public function getTeamsListAjax($search)
    {
        $teams = $this->getDoctrine()->getManager()->getRepository(Team::class)->findLike($search);
        return $this->render('AppBundle:team:teamsListAjax.html.twig', ['teams' => $teams]);
    }

    /**
     * @Route("/supprimer-player-team/{team_id}/{user_id}", name="deletePlayerTeam")
     */
    public function deleteUserTeam($team_id = null, $user_id = null) {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($team_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $team->removeUser($user);
        $em->persist($team);
        $em->flush();

        $users = $team->getUsers($user->getRoleString());
        return $this->render('AppBundle:team:playersList.html.twig', ['users' => $users]);
    }


    /**
     * @Route("/supprimer-une-equipe/{id}", name="delTeam")
     */
    public function deleteTeam(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository(Team::class)->find($id);

        $em->remove($team);
        $em->flush();

        return $this->redirectToRoute('listTeam');
    }

}
