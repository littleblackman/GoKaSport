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
 use AppBundle\Entity\User;



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
     * @Route("/add-referre-match/{match_id}/{user_id}", name="addRefereeMatch")
     */
    public function addRefereeMatch(Request $request, $match_id = null, $user_id = null)
    {


        $em = $this->getDoctrine()->getManager();
        $match = $em->getRepository(Match::class)->find($match_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $match->addUser($user);
        $em->persist($match);
        $em->flush();

        return new JsonResponse( ['referee' => $user->toArray()]);
    }

    /**
     * @Route("/remove-referre-match/{match_id}/{user_id}", name="removeRefereeMatch")
     */
    public function removeRefereeMatch(Request $request, $match_id = null, $user_id = null)
    {


        $em = $this->getDoctrine()->getManager();
        $match = $em->getRepository(Match::class)->find($match_id);
        $user = $em->getRepository(User::class)->find($user_id);

        $match->removeUser($user);
        $em->persist($match);
        $em->flush();

        return new JsonResponse( ['referee' => $user->toArray()]);
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

        if($match->getGroup()) {
          $tournamentId = $match->getGroup()->getTournament()->getId();
          return $this->redirectToroute('showMatchGroup', ['tournamentId' => $tournamentId]);
        } else {
         $gameManager->updateFinalRoundMatch($match);
          $tournamentId = $match->getTournament()->getId();
          return $this->redirectToroute('showMatchFinalRound', ['tournamentId' => $tournamentId]);
        }


    }


 }
