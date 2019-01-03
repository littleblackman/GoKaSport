<?php

 Namespace AppBundle\Controller;

 use Symfony\Bundle\FrameworkBundle\Controller\Controller;
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\Routing\Annotation\Route;
 use AppBundle\Entity\Tournament;
 use AppBundle\Entity\GameOptions;
 use AppBundle\Service\GameManager;
 use AppBundle\Entity\Match;


 class MatchController extends Controller
 {

   /**
    * @Route("/match/{id}", name="showMatch")
    */
    public function showMatch(Request $request, $id)
    {
        $match = $this->getDoctrine()->getManager()->getRepository(Match::class)->find($id);
        return $this->render('AppBundle:match:show.html.twig', ['match' => $match]);
    }

    /**
     * @Route("/start-match/{id}", name="startMatch")
     */
    public function startMatch(Request $request, $id = null)
    {
        $id = $_GET['id'];

        $time = new \DateTime( date('H:i:s'));
        $em = $this->getDoctrine()->getManager();
        $match = $em->getRepository(Match::class)->find($id);
        $match->setStatus('START');
        $match->setTimeStart($time);
        $em->persist($match);
        $em->flush();

        return new JsonResponse( ['timeStart' => $time->format('H:i:s')]);
    }

    /**
     * @Route("/fin-de-match", name="endMatch")
     */
    public function endMatch(Request $request, GameManager $gameManager)
    {
        $match_id = $request->get('match_id');
        $score = $request->get('score');
        $point = explode('-', $score);
        $winner = $request->get('winner');

        $time = new \DateTime( date('H:i:s'));

        $em = $this->getDoctrine()->getManager();
        $match = $em->getRepository(Match::class)->find($match_id);

        $match->setWinner($winner);
        $match->setStatus('END');
        $match->setTimeEnd($time);

        $match->setPointA($point[0]);
        $match->setPointB($point[1]);

        $em->persist($match);
        $em->flush();

        $gameManager->updateRankingMatch($match);
        $tournamentId = $match->getGroup()->getTournament()->getId();

        return $this->redirectToroute('showMatchGroup', ['tournamentId' => $tournamentId]);

    }


 }
