<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Tournament;
use AppBundle\Entity\Match;

/**
 * TournamentFinalRound
 *
 * @ORM\Table(name="tournament_final_round")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TournamentFinalRoundRepository")
 */
class TournamentFinalRound
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="step", type="integer")
     */
    private $step;

    /**
    * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="finalRounds")
    * @ORM\JoinColumn(name="tournament_id", referencedColumnName="id")
    */
    private $tournament;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="Match", mappedBy="finalRound", cascade={"persist", "remove"}))
    */
    private $matchs;

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
     * Set step
     *
     * @param integer $step
     *
     * @return TournamentFinalRound
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return TournamentFinalRound
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
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
        $match->setFinalRound($this);
        return $this;
    }

    public function getMatchs()
    {
        return $this->matchs;
    }

}
