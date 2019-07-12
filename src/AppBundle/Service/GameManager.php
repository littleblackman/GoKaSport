<?php

namespace AppBundle\Service;

use AppBundle\Entity\Tournament;
use AppBundle\Entity\Match;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\GameOptions;
use AppBundle\Entity\TournamentGroup;
use AppBundle\Entity\TournamentRanking;
use AppBundle\Entity\TournamentFinalRound;

class GameManager
{

  const STEP_NAME      = ['Vainqueur', 'finale', 'demi-finale', 'quart de finale', 'huitième de finale', 'seizième de finale'];
  const NB_MATCH_STEP  = [       0   ,     1    ,      2      ,              4   ,                  8  ,              16      ];
  const POSITION_ORDER = [    null   ,    [11]  ,   [21, 22]  ,  [31, 34, 33, 32],[41, 48, 45, 44, 43, 42, 47, 46],    []     ];

  const NEXT_POSITION  = [   41 => 31, 42 => 31,
                        43 => 31, 44 => 32,
                        45 => 31, 46 => 33,
                        47 => 31, 48 => 34,

                        31 => 21, 32 => 21,
                        33 => 22, 34 => 22,

                        21 => 11,
                        22 => 11,

                        11 => 0

                      ];

  const POINTS = ['V' => 3, 'N' => 1, 'D' => 0];

  /**
   * Tournament
   */
  private $tournament;

  /**
   * GameOptions object
   */
  private $gameOptions;

  private $em;

  public function __construct(EntityManagerInterface $em)
  {
      $this->em = $em;
  }

  /**
   * set Tournament object
   *
   */
  public function setTournament(Tournament $tournament)
  {
      $this->tournament = $tournament;
      return $this;
  }

  /**
   * Array of GameOptions
   */
  public function getGameOptions()
  {
      return $this->gameOptions;
  }

  /**
   * init all options tournament
   * create all matches
   */
  public function initTournament(Tournament $tournament, Array $gameOptionsArray = [], $reset = false)
  {
      $em = $this->em;
      $this->tournament = $tournament;

      // if tournamenent is init
      if($tournament->getIsInit() == 1) {
          // if tournament is not forced reset return true
          if($reset == false) return false;
          // otherwise reset tournament
          $this->resetTournament();
      }

      /***** CREATE GAMES OPTIONS *****/
      $gameOptions = new GameOptions();
      if($gameOptionsArray['type'] == 'group') {
        $gameOptions->setNbGroupsFirstRound($gameOptionsArray['nbGroups']);
      } else {
        $gameOptions->setNbStepRoundFinal($gameOptionsArray['nbFinalRound']*2);
      }
      $gameOptions->setTotalTeams($tournament->countNbTeams());
      $em->persist($gameOptions);

      // add gameOptions to Tournament
      $tournament->setGameOptions($gameOptions);

      /***** CREATE GROUP-ROUND *****/
      if($gameOptionsArray['type'] == 'group') {
          $tournament = $this->createGroupsRound($tournament, $gameOptions);
      } else {
          /***** CREATE FINAL-ROUND *****/
          $tournament = $this->createFinalRoundMatch($tournament, $gameOptions);
      }

      $tournament->setIsInit(1);

      $em->persist($tournament);
      $em->flush();

      return $tournament;
  }

  /**
   * init final round after group round
   *
   * @return Tournament
   */
  public function initFinalRound($totalTeams)
  {
      $tournament = $this->tournament;
      $nbMatchs = $totalTeams/2;

      $nbStepRoundFinal = array_search($nbMatchs, self::NB_MATCH_STEP);

      $em = $this->em;
      // create games options
      $gameOptions = $tournament->getGameOptions();
      $gameOptions->setNbStepRoundFinal($nbStepRoundFinal);
      $gameOptions->setNbTeamsRoundFinal($totalTeams);
      $gameOptions->setNbTeamsSelectedByGroups(floor($totalTeams/$gameOptions->getNbGroupsFirstRound()));
      $em->persist($tournament);
      $em->persist($gameOptions);
      $em->flush();

      // create step of final round
      $tournament = $this->createFinalRoundStep($tournament);

      // create all match by round
      $tournament = $this->createFinalRoundMatch($tournament);

      // dispatch all teams on latest step
      $tournament = $this->finalRoundDispatchTeams($tournament);

      $tournament->setCompetitionType('FINAL-ROUND');
      $em->persist($tournament);
      $em->flush();

  }

  /**
   * Create final round step
   * @param Tournament
   * @param GameOptions
   * @return Tournament
   */
  private function createFinalRoundStep(Tournament $tournament) {

    $gameOptions = $tournament->getGameOptions();

    $em = $this->em;
    for($k = $gameOptions->getNbStepRoundFinal(); $k >= 0; $k--)
    {
        $finalRound = new TournamentFinalRound();
        $finalRound->setStep($k);
        $finalRound->setName(self::STEP_NAME[$k]);
        $finalRound->setNbMatchByStep(self::NB_MATCH_STEP[$k]);

        // add the final round
        $tournament->addFinalRound($finalRound);

        $em->persist($finalRound);
        $em->persist($tournament);
    }
    $em->flush();
    return $tournament;
  }

  /**
   * Create final round match
   * @param Tournament
   * @return Tournament
   */
  private function createFinalRoundMatch(Tournament $tournament) {

    $em = $this->em;

    foreach($tournament->getFinalRounds() as $round)
    {
      $nb_match = $round->getNbMatchByStep();
      for($i = 1; $i <= $nb_match; $i++) {
          $match = new Match();
          $match->setPosition($round->getStep().$i);
          $match->setStatus('EMPTY');
          $round->addMatch($match);
          $em->persist($match);
      }
      $em->persist($round);
    }

    $em->flush();
    return $tournament;
  }


  /**
   * dispatch teams for first step of final round
   * @param Tournament
   * @return Tournament
   */
  private function finalRoundDispatchTeams(Tournament $tournament)
  {
      $gameOptions = $tournament->getGameOptions();

      // order teamSelected
      $teamSelecteds = $tournament->getTeamsSelecteds();

      // get last round
      $round = $tournament->getFinalRounds()[0];

      // step played
      $step  = $round->getStep();
      $positionOrders = self::POSITION_ORDER[$step];

      // create teams head series
      foreach($positionOrders as $key => $position) {
        $match = $this->em->getRepository(Match::class)->findOneBy(['finalRound' => $round, 'position' => $position]);
        $matchs[] = $match;
        $match->setTeamA($teamSelecteds[$key]);
        $match->setStatus('TO_UPDATE');
        $this->em->persist($match);
        unset($teamSelecteds[$key]);
      }

      // add other match no head series
      foreach($matchs as $match)
      {
        sort($teamSelecteds);
        if($match->getStatus() == "TO_UPDATE") {
          $key = rand(0, count($teamSelecteds)-1);
          $match->setTeamB($teamSelecteds[$key]);
          $match->setStatus('READY');
          $this->em->persist($match);
          unset($teamSelecteds[$key]);
        }

      }

      $this->em->flush();

      return $tournament;

      /*
        delete from match_game WHERE final_round_id is not null;
        delete from tournament_final_round;
        update tournament set competition_type = "GROUP-ROUND" where id = 2;
      */

  }




  /**
   * Create groups for tournament (no matchs created)
   * @param Tournament
   * @param GameOptions
   * @return Tournament
   */
  private function createGroupsRound(Tournament $tournament, GameOptions $gameOptions)
  {
      $em = $this->em;

      // create groups
      for($i = 1; $i <= $gameOptions->getNbGroupsFirstRound(); $i++) {
          $group = new TournamentGroup();
          $group->setOrderGroup($i);
          $group->setName('Groupe '.chr(64+$i));

          // add group to tournament
          $tournament->addGroup($group);

          $tournament->setHasGroundRound(1);

          $tournament->setCompetitionType('GROUP-ROUND');

          $em->persist($group);
          $em->persist($tournament);
      }
      $em->flush();
      return $tournament;
  }

  /**
   * check if all teams are in groups
   */
  public function getTeamsWithoutGroup()
  {
      $em = $this->em;
      $allTeams = $this->tournament->getTeams();
      $teamsInGroup = $this->getTeamsInGroup();
      foreach($allTeams as $team)
      {
          if(!key_exists($team->getId(), $teamsInGroup)) $teamWithoutGroups[] = $team;
      }

      if(!isset($teamWithoutGroups)) return null;
      return $teamWithoutGroups;
  }

  /**
   * get all teams in group
   * warning : return an array of object, not an ArrayCollection
   */
  public function getTeamsInGroup()
  {
      $teams = [];
      foreach($this->tournament->getGroups() as $group)
      {
          foreach($group->getTeams() as $team)
          {
              $teams[$team->getId()] = $team;
          }
      }
      return $teams;
  }

  /**
   * dispatch all teams in all groups
   * with a random function
   */
  public function autoBindGroups()
  {
      $em = $this->em;
      $tournament = $this->tournament;
      $teams = $tournament->getTeams();
      $teams = $teams->toArray();
      $groups = $tournament->getGroups();


      foreach($groups as $group)
      {
          $this->resetGroupTeams($group);

          for($i = 0; $i< floor($tournament->getMatchPerGroup()); $i++)
          {
                $key = rand(0, count($teams)-1);
                $group->addTeam($teams[$key]);
                unset($teams[$key]);
                sort($teams);
          }

          $em->persist($group);
      }


      if($addTeams = $this->getTeamsWithoutGroup()) {
        $i = 0;
        foreach($addTeams as $addTeam)
        {
            $groups[$i]->addTeam($addTeam);
            $i++;
            if($i > $tournament->getGameOptions()->getNbGroupsFirstRound()) $i = 0;
        }
      }
      $em->flush();

  }

  /**
   * update the match on over step
   * @param currentMatch
   * @return match
   */
  public function updateFinalRoundMatch($currentMatch)
  {
      $currentRound = $currentMatch->getFinalRound();
      $nextStep     = $currentRound->getStep()-1;
      if($nextStep == 0) {
        $this->gameOver($currentMatch);
        return $currentMatch;
      }
      $tournament   = $currentMatch->getTournament();

      $position     = $currentMatch->getPosition();
      $winner       = $currentMatch->getWinnerTeam();

      $nextRound = $this->em->getRepository(TournamentFinalRound::class)->findOneBy(['tournament' => $tournament, 'step' => $nextStep]);
      $nextPosition = self::NEXT_POSITION[$position];


      $nextMatch = $this->em->getRepository(Match::class)->findOneBy(['finalRound' => $nextRound, 'position' => $nextPosition]);
      if($nextMatch->getStatus() == "EMPTY") {
        $nextMatch->setTeamA($winner);
        $nextMatch->setStatus('TO_UPDATE');

      } else {
        $nextMatch->setTeamB($winner);
        $nextMatch->setStatus('READY');
      }
      $this->em->persist($nextMatch);
      $this->em->flush();

      return $currentMatch;

  }

  public function gameOver($match)
  {
    $tournament = $match->getTournament();
    $tournament->setWinner($match->getWinnerTeam());
    $this->em->persist($tournament);
    $this->em->flush();

    return true;
  }



  /**
   * valide group and create group
   *
   */
  public function validGroups()
  {
      $em = $this->em;
      $tournament = $this->tournament;
      $tournament->setIsValided(1);

      $em->persist($tournament);

      $this->createGroupsMatch();

      $em->flush();

  }

  /**
   * createAllgroupMatch
   *
   */
  public function createGroupsMatch()
  {
      $em = $this->em;
      $groups = $this->tournament->getGroups();
      $matchCreated = [];

      // check group by group
      foreach($groups as $group)
      {
            // create match team by team
            $teams = $group->getTeams();
            $totalMatchs = count($teams)*(count($teams)-1);
            $numbers = range(1, $totalMatchs);
            shuffle($numbers);
            $k = 1;
            foreach($teams as $teamA)
            {
                foreach($teams as $teamB) {

                    if( !key_exists($teamA->getId().$teamB->getId(), $matchCreated)
                        && !key_exists($teamB->getId().$teamA->getId(), $matchCreated)
                        && $teamA->getId() != $teamB->getId()) {

                          $match = new Match();
                          $match->setTeamA($teamA);
                          $match->setTeamB($teamB);
                          $match->setPosition($numbers[$k]);
                          $match->setStatus('READY');
                          $em->persist($match);

                          $group->addMatch($match);

                          $matchCreated[$teamA->getId().$teamB->getId()] = 1;

                          $k++;
                    }

                }

            }

            $em->persist($group);


      }

      $em->flush();

  }

  /**
   *  update ranking
   *
   */
  public function updateRankingMatch(Match $match)
  {
      $em = $this->em;
      if(!$group = $match->getGroup()) $group = null;
      $tournament = $match->getTournament();

      // team A
      if(!$rankingA = $em->getRepository(TournamentRanking::class)->findOneBy(['team' => $match->getTeamA(), 'tournament' => $tournament]))
      {
        $rankingA = new TournamentRanking();
      }

      $rankingA->setTeam($match->getTeamA());
      if($group) $rankingA->setGroup($group);
      $rankingA->setTournament($tournament);

      $rankingA->setMJ($rankingA->getMJ()+1);
      $rankingA->setBP($rankingA->getBP()+$match->getPointA());
      $rankingA->setBC($rankingA->getBC()+$match->getPointB());

      if($match->getWinner() == 'teamA') {
          $rankingA->setV($rankingA->getV()+1);
          $points = self::POINTS['V'];
      }
      if($match->getWinner() == 'teamB')
      {
          $rankingA->setD($rankingA->getD()+1);
          $points = self::POINTS['D'];
      }
      if($match->getWinner() == 'none') {
          $rankingA->setN($rankingA->getN()+1);
          $points = self::POINTS['N'];
      }
      $rankingA->setPTS($rankingA->getPTS()+$points);
      $em->persist($rankingA);

      // team B
      if(!$rankingB = $em->getRepository(TournamentRanking::class)->findOneBy(['team' => $match->getTeamB(), 'tournament' => $tournament]))
      {
        $rankingB = new TournamentRanking();
      }

      $rankingB->setTeam($match->getTeamB());
      if($group) $rankingB->setGroup($group);
      $rankingB->setTournament($tournament);

      $rankingB->setMJ($rankingB->getMJ()+1);
      $rankingB->setBP($rankingB->getBP()+$match->getPointB());
      $rankingB->setBC($rankingB->getBC()+$match->getPointA());

      if($match->getWinner() == 'teamB') {
          $rankingB->setV($rankingB->getV()+1);
          $points = self::POINTS['V'];
      }
      if($match->getWinner() == 'teamA')
      {
          $rankingB->setD($rankingB->getD()+1);
          $points = self::POINTS['D'];
      }
      if($match->getWinner() == 'none') {
          $rankingB->setN($rankingB->getN()+1);
          $points = self::POINTS['N'];
      }
      $rankingB->setPTS($rankingB->getPTS()+$points);
      $em->persist($rankingB);

      $em->flush();

  }


  /**
   * Use in the autobind groups only !!!!
   * reset all groups to null
   */
  private function resetGroupTeams($group)
  {
      foreach($group->getTeams() as $team)
      {
          $group->removeTeam($team);
      }
  }

  /**
   * reset tournamenet value
   * set isInit, gameOptions, isOpen to 0 or null
   * delete alls groups and finalRounds
   */
  public function resetTournament($type = null)
  {
      $tournament = $this->tournament;
      $em = $this->em;

      // reset matchs
      if($type == null || $type== 'matchs') {
          if($groups = $em->getRepository(TournamentGroup::class)->findBy(['tournament' => $tournament]))
          {
              foreach($groups as $group)
              {
                  foreach($group->getMatchs() as $match)
                  {
                      $match->resetValues();
                      $em->persist($match);

                  }
              }
          }

          // remove rankings
          foreach($tournament->getRankings() as $ranking)
          {
              $em->remove($ranking);
              $em->flush();
          }
      }

      // reset groups & finalround
      if($type == null ||  $type == 'groups' || $type == "teams" ){
          if($groups = $em->getRepository(TournamentGroup::class)->findBy(['tournament' => $tournament])) {
              foreach($groups as $group)
              {
                  $em->remove($group);
              }
          }

          // reset final round
          if($finalRounds = $em->getRepository(TournamentFinalRound::class)->findBy(['tournament' => $tournament])) {
              foreach($finalRounds as $finalRound)
              {
                  $em->remove($finalRound);
              }
          }

          // reset teams
          if( $type == "teams")
          {
              foreach($tournament->getTeams() as $team)
              {
                  $tournament->removeTeam($team);
              }

          }

          $tournament->setIsInit(0);
          $tournament->setIsValided(0);
          $tournament->setIsOpen(0);
      }

      $em->persist($tournament);
      $em->flush();

      return $this;

  }

  public function simulateTournament()
  {
      $tournament = $this->tournament;
      $em = $this->em;
      foreach($tournament->getGroups() as $group)
      {
        foreach($group->getMatchs() as $match)
        {

            if($match->getStatus() != "END") {
                $time = new \DateTime( date('H:i:s'));
                $scoreA = rand(0, 3);
                $scoreB = rand(0,3);
                $winner = "none";
                if($scoreA > $scoreB) {
                  $winner = "teamA";
                }
                if($scoreB > $scoreA) {
                  $winner = "teamB";
                }

                $match->setWinner($winner);
                $match->setStatus('END');
                $match->setTimeEnd($time);
                $match->setPointA($scoreA);
                $match->setPointB($scoreB);

                $em->persist($match);
                $em->flush();

                $this->updateRankingMatch($match);

            }

        }
      }
  }

}
