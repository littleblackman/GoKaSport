<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Tournament;
use AppBundle\Entity\GameOptions;

use AppBundle\Service\GameManager;

class GameController extends Controller
{
    /**
     * @Route("/creation-du-tournoi/{tournamentId}", name="createGame")
     */
    public function indexAction(Request $request, $tournamentId = null, GameManager $gameManager)
    {
        $tournament = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->find($tournamentId);
        $gameManager->setTournament($tournament);
        return $this->render('AppBundle:game:create.html.twig', ['tournament' => $tournament,'gameManager' => $gameManager]);
    }

    /**
     * @Route("/initialisation-partie", name="initGame")
     */
    public function initGameAction(Request $request, GameManager $gameManager)
    {
        $tournamentId = $request->get('tournamentId');
        $gameOptionsId = $request->get('gameOptionsId');
        ($request->get('resetForce')) ? $resetForce = true : $resetForce = false;

        $em = $this->getDoctrine()->getManager();
        $gameOptions = $em->getRepository(GameOptions::class)->find($gameOptionsId);
        $tournament = $em->getRepository(Tournament::class)->find($tournamentId);

        if(!$tournamentUpdated = $gameManager->initTournament($tournament, $gameOptions, $resetForce)) {
            $this->addFlash('error',
                            'Impossible d\'initier le tournoi, les groupes sont déjà créés.<br/>Vous pouvez essayer en forçant la création.'
                          );
            return $this->render('AppBundle:game:create.html.twig', ['tournament' => $tournament,'gameManager' => $gameManager]);
        }

        return $this->redirectToRoute('manageGame', ['tournamentId' => $tournamentId]);

    }

    /**
     * @Route("/gerer-la-partie/{tournamentId}", name="manageGame")
     */
    public function manageGame(Request $request, GameManager $gameManager, $tournamentId)
    {
        $tournament = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->find($tournamentId);

        // if tournament is valided
        if($tournament->getIsValided() == 1) return $this->redirectToRoute('showMatchGroup', ['tournamentId' => $tournamentId]);

        $gameManager->setTournament($tournament);
        return $this->render('AppBundle:game:manage.html.twig', ['tournament' => $tournament,'gameManager' => $gameManager]);

    }

    /**
     * @Route("/generer-les-groupes/{tournamentId}", name="bindGroups")
     */
    public function bindGroups(Request $request, GameManager $gameManager, $tournamentId)
    {
      $tournament = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->find($tournamentId);
      $gameManager->setTournament($tournament);
      $gameManager->autoBindGroups();
      return $this->redirectToRoute('manageGame', ['tournamentId' => $tournamentId]);
    }

    /**
     * @Route("/valider-les-groupes/{tournamentId}", name="validGroups")
     */
    public function validGroups(Request $request, GameManager $gameManager, $tournamentId)
    {
        $tournament = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->find($tournamentId);
        $gameManager->setTournament($tournament);

        if($tournament->getIsValided() == 0) $gameManager->validGroups();

        return $this->redirectToRoute('showMatchGroup', ['tournamentId' => $tournamentId]);

    }


    /**
     * @Route("/voir-les-matchs/{tournamentId}", name="showMatchGroup")
     */
    public function showMatchGroup(Request $request, GameManager $gameManager, $tournamentId)
    {
        $tournament = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->find($tournamentId);
        $gameManager->setTournament($tournament);
        return $this->render('AppBundle:game:matchGroup.html.twig', ['tournament' => $tournament,'gameManager' => $gameManager]);
    }

}
