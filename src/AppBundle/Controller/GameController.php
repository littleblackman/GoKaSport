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

    private $gameManager;

    public function __construct(GameManager $gameManager)
    {
            $this->gameManager = $gameManager;
    }

    /**
     * @Route("/creation-du-tournoi/{tournamentId}", name="createGame")
     */
    public function indexAction(Request $request, $tournamentId = null)
    {
        $tournament = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->find($tournamentId);

        // block acces to init
        if($tournament->getIsInit() == 1) {
          return $this->redirectToRoute('showMatchGroup', ['tournamentId' => $tournamentId]);
        }

        $val = 2;
        while($val <= $tournament->countNbTeams() ) {
          $maxTeamFinalRoundOption[] = $val;
          $val = $val*2;
        }

        $maxGroup = floor($tournament->countNbTeams()/3);

        return $this->render('AppBundle:game:create.html.twig', ['tournament' => $tournament, 'maxGroup' => $maxGroup, 'maxTeamFinalRoundOption' => $maxTeamFinalRoundOption]);
    }

    /**
     * @Route("/initialisation-partie", name="initGame")
     */
    public function initGameAction(Request $request, GameManager $gameManager)
    {
        $tournamentId = $request->get('tournamentId');
        $gameOptionsArray = $request->get('data');

        $em = $this->getDoctrine()->getManager();
        $tournament = $em->getRepository(Tournament::class)->find($tournamentId);

        ($request->get('resetForce')) ? $resetForce = true : $resetForce = false;

        if(!$tournamentInit = $gameManager->initTournament($tournament, $gameOptionsArray, $resetForce)) {
            $this->addFlash('warning',
                            'Impossible d\'initier le tournoi, les groupes sont déjà créés.<br/>Vous pouvez essayer en forçant la création.'
                          );
            return $this->redirectToRoute('createGame', ['tournamentId' => $tournament->getId()]);
        }

        $gameManager->setTournament($tournament);
        $gameManager->autoBindGroups();
        $gameManager->validGroups();

        return $this->redirectToRoute('showMatchGroup', ['tournamentId' => $tournamentId]);
    }


    /**
     * @Route("/remettre-zero-tournoi/{tournamentId}", name="resetGame")
     */
    public function resetGame(Request $request, $tournamentId)
    {

        $tournament = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->find($tournamentId);
        $this->gameManager->setTournament($tournament)->resetTournament('groups')->resetTournament('matchs');
        return $this->redirectToRoute('showTournament', ['id' => $tournamentId]);
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

    /**
     * @Route("/passer-en-phase-finale/{tournamentId}", name="finalRoundPrepar")
     */
    public function finalRoundPrepar(Request $request, $tournamentId)
    {

        $tournament = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->find($tournamentId);
        $this->gameManager->setTournament($tournament)->resetTournament('groups')->resetTournament('matchs');
        return $this->render('AppBundle:game:editFinalRound.html.twig', ['tournament' => $tournament,'gameManager' => $gameManager]);
    }

}
