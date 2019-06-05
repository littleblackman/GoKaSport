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

  const STEP_NAME = ['', 'Vainqueur', 'finale', 'demi-finale', 'quart de finale', 'huitième de finale', 'seizième de finale'];
  const POINTS = ['V' => 3, 'N' => 1, 'D' => 0];
  /**
   * Tournament
   */
  private $tournament;

  /**
   * Array of GameOptions object
   */
  private $gameOptions;

  public function __construct(EntityManagerInterface $em)
  {
      $this->em = $em;
      $gameOptions = $em->getRepository(GameOptions::class)->findBy(['totalTeams' => 16]);
      $this->gameOptions = $gameOptions;
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
  public function initTournament(Tournament $tournament, GameOptions $gameOptions, $reset = false)
  {
      $em = $this->em;

      // if tournamenent is init
      if($tournament->getIsInit() == 1) {

          // if tournament is not forced reset return true
          if($reset == false)
          {
            return false;
          }

          // otherwise reset tournament
          $this->resetTournament();
      }

      // add gameOptions to Tournament
      $tournament->setGameOptions($gameOptions);

      // create groups round
      if($gameOptions->getNbGroupsFirstRound() != 0)
      {
          // create groups
          for($i = 1; $i <= $gameOptions->getNbGroupsFirstRound(); $i++) {
              $group = new TournamentGroup();
              $group->setOrderGroup($i);
              $group->setName('Groupe '.chr(64+$i));

              // add group to tournament
              $tournament->addGroup($group);

              $em->persist($group);
              $em->persist($tournament);
          }

      }

      // create final round & final empty matches
      $diviseur = 1;
      for($k = $gameOptions->getNbStepRoundFinal(); $k > 0; $k--)
      {
          $diviseur = $diviseur * 2;

          $finalRound = new TournamentFinalRound();
          $finalRound->setStep($k);
          $finalRound->setName(self::STEP_NAME[$k]);

          // add the final round
          $tournament->addFinalRound($finalRound);

          for($m = 1; $m <= $gameOptions->getNbTeamsRoundFinal()/$diviseur; $m++)
          {
              $match = new Match();
              $match->setStatus('EMPTY');
              $match->setPosition($m);
              $em->persist($match);

              // add match to final round
              $finalRound->addMatch($match);
          }

          $em->persist($finalRound);
          $em->persist($tournament);

      }

      $tournament->setIsInit(1);

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

          for($i = 0; $i<$tournament->getMatchPerGroup(); $i++)
          {
                $key = rand(0, count($teams)- 1);
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
      $group = $match->getGroup();
      $tournament = $group->getTournament();

      // team A
      if(!$rankingA = $em->getRepository(TournamentRanking::class)->findOneBy(['team' => $match->getTeamA(), 'tournament' => $tournament]))
      {
        $rankingA = new TournamentRanking();
      }

      $rankingA->setTeam($match->getTeamA());
      $rankingA->setGroup($group);
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
      $rankingB->setGroup($group);
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
      if($type != null && $type== 'matchs') {
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
      if($type != null &&  ( $type == 'groups' || $type == "teams") ){
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

              $tournament->setIsInit(0);
          }

          $tournament->setIsValided(0);
          $tournament->setIsOpen(0);
      }

      $em->persist($tournament);
      $em->flush();

  }



}
