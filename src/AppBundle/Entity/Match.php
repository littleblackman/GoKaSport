<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\TournamentFinalRound;
use AppBundle\Entity\Team;
use AppBundle\Entity\Match;
use AppBundle\Entity\TournamentGroup;

/**
 * Match
 *
 * @ORM\Table(name="match_game")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MatchRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Match extends LbmExtensionEntity
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
    * @ORM\ManyToOne(targetEntity="Team")
    * @ORM\JoinColumn(name="team_A_id", referencedColumnName="id")
    */
    private $teamA;

    /**
    * @ORM\ManyToOne(targetEntity="Team")
    * @ORM\JoinColumn(name="team_B_id", referencedColumnName="id")
    */
    private $teamB;

    /**
    * @ORM\ManyToOne(targetEntity="TournamentFinalRound", inversedBy="matchs")
    * @ORM\JoinColumn(name="final_round_id", referencedColumnName="id")
    */
    private $finalRound;

    /**
    * @ORM\ManyToOne(targetEntity="TournamentGroup", inversedBy="matchs")
    * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
    */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50, nullable=true)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_start", type="time",nullable=true)
     */
    private $timeStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_end", type="time",nullable=true)
     */
    private $timeEnd;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="pointA", type="integer", nullable=true)
     */
    private $pointA;

    /**
     * @var int
     *
     * @ORM\Column(name="pointB", type="integer", nullable=true)
     */
    private $pointB;

    /**
     * @var string
     *
     * @ORM\Column(name="winner", type="string", length=20, nullable=true)
     */
    public $winner;

    /**
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="matchs")
     * @ORM\JoinTable(name="matchs_users")
     */
    private $users;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function resetValues()
    {
        $this->setStatus('EMPTY');
        $this->setPointA(null);
        $this->setPointB(null);
        $this->setWinner(null);
        $this->setTimeStart(null);
        $this->setTimeEnd(null);

    }

    public function setTeamA(Team $team)
    {
        $this->teamA = $team;
        return $this;
    }

    public function getTeamA()
    {
        return $this->teamA;
    }

    public function setTeamB(Team $team)
    {
        $this->teamB = $team;
        return $this;
    }

    public function getTeamB()
    {
        return $this->teamB;
    }

    public function getTeamName($team)
    {
        if($team == 'teamA') $name = $this->getTeamA()->getName();
        if($team == 'teamB') $name = $this->getTeamB()->getName();
        if($team == $this->getWinner()) {
          $name = "<span class='winner'>".$name.'</span>';
        }
        return $name;
    }

    public function setFinalRound(TournamentFinalRound $finalRound)
    {
        $this->finalRound = $finalRound;
        return $this;
    }

    public function getFinalRound()
    {
        return $this->finalRound();
    }

    public function setGroup(TournamentGroup $group)
    {
        $this->group = $group;
        return $this;
    }

    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Match
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set timeStart
     *
     * @param \DateTime $time
     *
     * @return Match
     */
    public function setTimeStart($time)
    {
        $this->timeStart = $time;

        return $this;
    }

    /**
     * Get timeStart
     *
     * @return \DateTime
     */
    public function getTimeStart()
    {
        return $this->timeStart;
    }


    /**
     * Set timeEnd
     *
     * @param \DateTime $time
     *
     * @return Match
     */
    public function setTimeEnd($time)
    {
        $this->timeEnd = $time;

        return $this;
    }

    public function addUser($user)
    {
        $this->users[] = $user;
        $user->addMatch($this);
        return $this;
    }

    public function removeUser($user)
    {
        $this->users->removeElement($user);
        return $this;
    }

    public function getUsers($role = null)
    {
        if($role) {
            $result = [];
            foreach($this->users as $user) {
                if($user->getRoleString() == $role) $result[] = $user;
            }
            return $result;
        }
        return $this->users;
    }

    public function getReferees()
    {
        return $this->getUsers('REFEREE');
    }

    /**
     * Get timeEnd
     *
     * @return \DateTime
     */
    public function getTimeEnd()
    {
        return $this->timeEnd;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Match
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set pointA
     *
     * @param integer $pointA
     *
     * @return Match
     */
    public function setPointA($pointA)
    {
        $this->pointA = $pointA;

        return $this;
    }

    /**
     * Get pointA
     *
     * @return int
     */
    public function getPointA()
    {
        return $this->pointA;
    }

    /**
     * Set pointB
     *
     * @param integer $pointB
     *
     * @return Match
     */
    public function setPointB($pointB)
    {
        $this->pointB = $pointB;

        return $this;
    }

    public function getScore($separator = ' - ')
    {
        return $this->getPointA().$separator.$this->getPointB();
    }

    /**
     * Get pointB
     *
     * @return int
     */
    public function getPointB()
    {
        return $this->pointB;
    }

    /**
     * Set winner
     *
     * @param string $winner
     *
     * @return Match
     */
    public function setWinner($winner)
    {
        $this->winner = $winner;

        return $this;
    }

    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * Get winner
     *
     * @return Team|null
     */
    public function getWinnerTeam()
    {
        if($this->winner == 'none') return 'none';
        if($this->winner == 'teamA') return $this->getTeamA();
        if($this->winner == 'teamB') return $this->getTeamB();
        return null;
    }

}
