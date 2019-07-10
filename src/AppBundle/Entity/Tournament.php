<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Validation\DateTournament as DateTournament;

use AppBundle\Entity\TournamentGroup;
use AppBundle\Entity\TournamentRanking;
use AppBundle\Entity\TournamentFinalRound;
use AppBundle\Entity\GameOptions;
use AppBundle\Entity\Team;

/**
 * Tournament
 *
 * @ORM\Table(name="tournament")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TournamentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Tournament extends LbmExtensionEntity
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
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 2,
     *      minMessage = "Le nom doit avoir au moins {{ limit }} caractères"
     * )
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     * @DateTournament
     * @ORM\Column(name="dateStart", type="date")
     */
    private $dateStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEnd", type="date", nullable=true)
     */
    private $dateEnd;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255, nullable=true)
     */
    private $postalCode;

    /**
     * @var bool
     *
     * @ORM\Column(name="isOpen", type="boolean", nullable=true)
     */
    private $isOpen;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_init", type="boolean", nullable=true)
     */
    private $isInit;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_valided", type="boolean", nullable=true)
     */
    private $isValided;

    /**
     * @var string
     *
     * @ORM\Column(name="competition_type", type="text", nullable=true)
     */
    private $competitionType;

    /**
    * @ORM\ManyToOne(targetEntity="Sport")
    * @ORM\JoinColumn(name="sport_id", referencedColumnName="id", nullable=true)
    */
    private $sport;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Team", inversedBy="tournaments")
     * @ORM\JoinTable(name="tournaments_teams")
     */
    private $teams;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="TournamentGroup", mappedBy="tournament", cascade={"persist", "remove"}))
    */
    private $groups;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="TournamentFinalRound", mappedBy="tournament", cascade={"persist", "remove"}))
    * @ORM\OrderBy({"step" = "DESC"})
    */
    private $finalRounds;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="TournamentRanking", mappedBy="tournament", cascade={"persist", "remove"}))
    * @ORM\OrderBy({"pTS" = "DESC", "bP" = "DESC"})
    */
    private $rankings;

    /**
    * @ORM\ManyToOne(targetEntity="GameOptions")
    * @ORM\JoinColumn(name="game_options_id", referencedColumnName="id")
    */
    private $gameOptions;

    /**
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="tournaments")
     * @ORM\JoinTable(name="tournaments_users")
     */
    private $users;

    /**
    * @ORM\ManyToOne(targetEntity="Team")
    * @ORM\JoinColumn(name="winner_id", referencedColumnName="id")
    */
    private $winner;


    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->rankings = new ArrayCollection();
        $this->groups = new ArrayCollection();

        if(!$this->getId()) $this->setIsInit(0);
        if(!$this->getId()) $this->setIsValided(0);

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
     * @return Tournament
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
     * Set description
     *
     * @param string $description
     *
     * @return Tournament
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateStart
     *
     * @param \DateTime $dateStart
     *
     * @return Tournament
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function setSport(Sport $sport)
    {
        $this->sport = $sport;
        return $this;
    }

    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     *
     * @return Tournament
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Tournament
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Tournament
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     *
     * @return Tournament
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function countNbTeams()
    {
        return count($this->getTeams());
    }

    /**
     * Set competition type
     *
     * @param string $type
     *
     * @return Tournament
     */
    public function setCompetitionType($type)
    {
        $this->competitionType = $type;

        return $this;
    }

    /**
     * Get competitionType
     *
     * @return string
     */
    public function getCompetitionType()
    {
        return $this->competitionType;
    }

    /**
     * Set isOpen
     *
     * @param boolean $isOpen
     *
     * @return Tournament
     */
    public function setIsOpen($isOpen)
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    /**
     * Get isOpen
     *
     * @return bool
     */
    public function getIsOpen()
    {
        return $this->isOpen;
    }


    public function getIsOpenclass()
    {
        if($this->getIsOpen() == 0) return null;
        return "btn--info";
    }

    /**
     * Set isInit
     *
     * @param boolean $isInit
     *
     * @return Tournament
     */
    public function setIsInit($isInit)
    {
        $this->isInit = $isInit;

        return $this;
    }

    /**
     * Get isInit
     *
     * @return bool
     */
    public function getIsInit()
    {
        return $this->isInit;
    }

    /**
     * Set $isValided
     *
     * @param boolean $isValided
     *
     * @return Tournament
     */
    public function setIsValided($isValided)
    {
        $this->isValided = $isValided;

        return $this;
    }

    /**
     * Get isValided
     *
     * @return bool
     */
    public function getIsValided()
    {
        return $this->isValided;
    }


    public function addTeam($team)
    {
        $this->teams[] = $team;
        $team->addTournament($this);
        return $this;
    }

    public function removeTeam($team)
    {
        $this->teams->removeElement($team);
        return $this;
    }


    public function getTeams()
    {
        return $this->teams;
    }

    public function addUser($user)
    {
        $this->users[] = $user;
        $user->addTournament($this);
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

    public function getManagers()
    {
        return $this->getUsers('MANAGER');
    }

    public function getReferees()
    {
        return $this->getUsers('REFEREE');
    }

    public function addGroup(TournamentGroup $group)
    {
        $this->groups[] = $group;
        $group->setTournament($this);
        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function addFinalRound(TournamentFinalRound $finalRound)
    {
        $this->finalRounds[] = $finalRound;
        $finalRound->setTournament($this);
        return $this;
    }

    public function getFinalRounds()
    {
        return $this->finalRounds;
    }

    public function addRanking(TournamentRanking $ranking)
    {
        $this->rankings[] = $ranking;
        $ranking->setTournament($this);
        return $this;
    }

    public function removeRanking(TournamentRanking $ranking)
    {
        $this->rankings->removeElement($ranking);
        return $this;
    }

    public function getRankings()
    {
        return $this->rankings;
    }

    public function setGameOptions(GameOptions $gameOptions)
    {
        $this->gameOptions = $gameOptions;
        return $this;
    }

    public function getGameOptions()
    {
        return $this->gameOptions;
    }

    /**
     * Set winner
     *
     * @param Team $team
     *
     * @return Tournament
     */
    public function setWinner($team)
    {
        $this->winner = $team;

        return $this;
    }

    /**
     * Get winner
     *
     * @return Team
     */
    public function getWinner()
    {
        return $this->winner;
    }





    public function getMatchPerGroup()
    {
        return $this->getGameOptions()->getMatchPerGroup();
    }

    /**
     * Return the number of match left
     * @return int
     */
    public function nbMatchLeft() {
        $total = 0; $totalEnd = 0;
        foreach($this->getGroups() as $group)
        {
          $total += count($group->getMatchs());
          foreach($group->getMatchs() as $match)
          {
              if($match->getStatus() == "END") $totalEnd ++;
          }
        }
        return $total - $totalEnd;
    }
}
