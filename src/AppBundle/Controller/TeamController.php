<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Team;
use AppBundle\Entity\Player;
use AppBundle\Form\TeamType;
use Doctrine\Common\Collections\ArrayCollection;


class TeamController extends Controller
{
    /**
     * @Route("/liste-des-equipes", name="listTeam")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $teams = $em->getRepository(Team::class)->findAll();
        return $this->render('AppBundle:team:list.html.twig', ['teams' => $teams]);
    }

    /**
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

            $em->persist($team);
            $em->flush();

            return $this->redirectToRoute('listTeam');
        }

        return $this->render('AppBundle:team:edit.html.twig', ['form' => $form->createView(), 'mode' => $mode]);

    }

    /**
     * @Route("/get-teams-ajax/{search}", name="teamsListAjax", defaults={"search" = null})
     */
    public function getTeamsListAjax($search)
    {
        $em = $this->getDoctrine()->getManager();
        $teams = $em->getRepository(Team::class)->findLike($search);

        return $this->render('AppBundle:team:teamsListAjax.html.twig', ['teams' => $teams]);

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
