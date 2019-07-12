<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Tournament;
use AppBundle\Entity\TournamentRanking;
use AppBundle\Entity\Match;
use AppBundle\Entity\Team;

/**
 * TournamentGroup
 *
 * @ORM\Table(name="tournament_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TournamentGroupRepository")
 */
class TournamentGroup
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
     * @ORM\Column(name="order_group", type="integer")
     */
    private $orderGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
    * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="groups")
    * @ORM\JoinColumn(name="tournament_id", referencedColumnName="id")
    */
    private $tournament;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Team")
     * @ORM\JoinTable(name="tournament_group_teams")
     */
    private $teams;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="TournamentRanking", mappedBy="group", cascade={"persist", "remove"}))
    * @ORM\OrderBy({"pTS" = "DESC"})
    */
    private $rankings;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="Match", mappedBy="group", cascade={"persist", "remove"}))
    * @ORM\OrderBy({"typeMatch" = "ASC", "position" = "ASC"})
    */
    private $matchs;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->groups = new ArrayCollection();

    }

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
     * Set name
     *
     * @param string $name
     *
     * @return TournamentGroups
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set orderGroup
     *
     * @param int $orderGroup
     *
     * @return TournamentGroup
     */
    public function setOrderGroup($orderGroup)
    {
        $this->orderGroup = $orderGroup;

        return $this;
    }

    /**
     * Get orderGroup
     *
     * @return int
     */
    public function getOrderGroup()
    {
        return $this->orderGroup;
    }

    public function setTournament(Tournament $tournament)
    {
        $this->tournament = $tournament;
        return $this;
    }

    public function getTournament()
    {
        return $this->tournament;
    }

    public function addMatch(Match $match)
    {
        $this->matchs[] = $match;
        $match->setGroup($this);
        return $this;
    }

    public function getMatchs()
    {
        return $this->matchs;
    }

    public function addRanking(TournamentRanking $ranking)
    {
        $this->rankings[] = $ranking;
        $ranking->setGroup($this);
        return $this;
    }

    public function getRankings()
    {
        return $this->rankings;
    }

    public function addTeam(Team $team)
    {
        $this->teams[] = $team;
        return $this;
    }

    public function removeTeam(Team $team)
    {
        $this->teams->removeElement($team);
        return $this;
    }

    public function getTeams()
    {
        return $this->teams;
    }

}
