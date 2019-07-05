<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 *
 * @ORM\Table(name="game_options")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GameOptionsRepository")
 */
class GameOptions
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="total_teams", type="integer")
     */
    private $totalTeams;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_teams_round_final", type="integer", nullable=true)
     */
    private $nbTeamsRoundFinal;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_step_round_final", type="integer", nullable=true)
     */
    private $nbStepRoundFinal;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_groups_first_round", type="integer", nullable=true)
     */
    private $nbGroupsFirstRound;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_teams_selected_by_groups", type="integer", nullable=true)
     */
    private $nbTeamsSelectedByGroups;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set totalTeams
     *
     * @param integer $totalTeams
     *
     * @return Game
     */
    public function setTotalTeams($totalTeams)
    {
        $this->totalTeams = $totalTeams;

        return $this;
    }

    /**
     * Get totalTeams
     *
     * @return int
     */
    public function getTotalTeams()
    {
        return $this->totalTeams;
    }

    /**
     * Set nbTeamsRoundFinal
     *
     * @param integer $nbTeamsRoundFinal
     *
     * @return Game
     */
    public function setNbTeamsRoundFinal($nbTeamsRoundFinal)
    {
        $this->nbTeamsRoundFinal = $nbTeamsRoundFinal;

        return $this;
    }

    /**
     * Get nbTeamsRoundFinal
     *
     * @return int
     */
    public function getNbTeamsRoundFinal()
    {
        return $this->nbTeamsRoundFinal;
    }


    /**
     * Set nbStepRoundFinal
     *
     * @param integer $nbStepRoundFinal
     *
     * @return Game
     */
    public function setNbStepRoundFinal($nbStepRoundFinal)
    {
        $this->nbStepRoundFinal = $nbStepRoundFinal;

        return $this;
    }

    /**
     * Get nbStepRoundFinal
     *
     * @return int
     */
    public function getNbStepRoundFinal()
    {
        return $this->nbStepRoundFinal;
    }


    /**
     * Set nbGroupsFirstRound
     *
     * @param integer $nbGroupsFirstRound
     *
     * @return Game
     */
    public function setNbGroupsFirstRound($nbGroupsFirstRound)
    {
        $this->nbGroupsFirstRound = $nbGroupsFirstRound;

        return $this;
    }

    /**
     * Get nbGroupsFirstRound
     *
     * @return int
     */
    public function getNbGroupsFirstRound()
    {
        return $this->nbGroupsFirstRound;
    }

    /**
     * Set nbTeamsSelectedByGroups
     *
     * @param integer $nbTeamsSelectedByGroups
     *
     * @return Game
     */
    public function setNbTeamsSelectedByGroups($nbTeamsSelectedByGroups)
    {
        $this->nbTeamsSelectedByGroups = $nbTeamsSelectedByGroups;

        return $this;
    }

    /**
     * Get nbTeamsSelectedByGroups
     *
     * @return int
     */
    public function getNbTeamsSelectedByGroups()
    {
        return $this->nbTeamsSelectedByGroups;
    }

    public function getMatchPerGroup()
    {
        return $this->getTotalTeams()/$this->getNbGroupsFirstRound();
    }
}
