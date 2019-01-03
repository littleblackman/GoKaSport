<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Team;
use AppBundle\Entity\Tournament;
use AppBundle\Entity\TournamentGroup;

/**
 * TournamentRanking
 *
 * @ORM\Table(name="tournament_ranking")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TournamentRankingRepository")
 */
class TournamentRanking
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
    * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="rankings")
    * @ORM\JoinColumn(name="tournament_id", referencedColumnName="id")
    */
    private $tournament;

    /**
    * @ORM\ManyToOne(targetEntity="TournamentGroup", inversedBy="rankings")
    * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
    */
    private $group;

    /**
    * @ORM\ManyToOne(targetEntity="team")
    * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
    */
    private $team;


    /**
     * @var int
     *
     * @ORM\Column(name="MJ", type="integer", nullable=true)
     */
    private $mJ;

    /**
     * @var int
     *
     * @ORM\Column(name="V", type="integer", nullable=true)
     */
    private $v;

    /**
     * @var int
     *
     * @ORM\Column(name="N", type="integer", nullable=true)
     */
    private $n;

    /**
     * @var int
     *
     * @ORM\Column(name="D", type="integer", nullable=true)
     */
    private $d;

    /**
     * @var int
     *
     * @ORM\Column(name="BP", type="integer", nullable=true)
     */
    private $bP;

    /**
     * @var int
     *
     * @ORM\Column(name="BC", type="integer", nullable=true)
     */
    private $bC;

    /**
     * @var int
     *
     * @ORM\Column(name="PTS", type="integer", nullable=true)
     */
    private $pTS;


    public function __construct()
    {
        if($this->getId() == null)
        {
            $this->setMJ(0);
            $this->setV(0);
            $this->setN(0);
            $this->setD(0);
            $this->setBP(0);
            $this->setBC(0);
            $this->setPTS(0);
        }
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

    public function setTournament(Tournament $tournament)
    {
        $this->tournament = $tournament;
        return $this;
    }

    public function getTournament()
    {
        return $this->tournament;
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


    public function setTeam(Team $team)
    {
        $this->team = $team;
        return $this;
    }

    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set mJ
     *
     * @param integer $mJ
     *
     * @return TournamentRanking
     */
    public function setMJ($mJ)
    {
        $this->mJ = $mJ;

        return $this;
    }

    /**
     * Get mJ
     *
     * @return int
     */
    public function getMJ()
    {
        return $this->mJ;
    }

    /**
     * Set v
     *
     * @param integer $v
     *
     * @return TournamentRanking
     */
    public function setV($v)
    {
        $this->v = $v;

        return $this;
    }

    /**
     * Get v
     *
     * @return int
     */
    public function getV()
    {
        return $this->v;
    }

    /**
     * Set n
     *
     * @param integer $n
     *
     * @return TournamentRanking
     */
    public function setN($n)
    {
        $this->n = $n;

        return $this;
    }

    /**
     * Get n
     *
     * @return int
     */
    public function getN()
    {
        return $this->n;
    }

    /**
     * Set d
     *
     * @param integer $d
     *
     * @return TournamentRanking
     */
    public function setD($d)
    {
        $this->d = $d;

        return $this;
    }

    /**
     * Get d
     *
     * @return int
     */
    public function getD()
    {
        return $this->d;
    }


    /**
     * Set bP
     *
     * @param integer $bP
     *
     * @return TournamentRanking
     */
    public function setBP($bP)
    {
        $this->bP = $bP;

        return $this;
    }

    /**
     * Get bP
     *
     * @return int
     */
    public function getBP()
    {
        return $this->bP;
    }

    /**
     * Set bC
     *
     * @param integer $bC
     *
     * @return TournamentRanking
     */
    public function setBC($bC)
    {
        $this->bC = $bC;

        return $this;
    }

    /**
     * Get bC
     *
     * @return int
     */
    public function getBC()
    {
        return $this->bC;
    }

    public function getDB()
    {
        return $this->getBP()-$this->getBC();
    }

    /**
     * Set pTS
     *
     * @param integer $pTS
     *
     * @return TournamentRanking
     */
    public function setPTS($pTS)
    {
        $this->pTS = $pTS;

        return $this;
    }

    /**
     * Get pTS
     *
     * @return int
     */
    public function getPTS()
    {
        return $this->pTS;
    }
}
