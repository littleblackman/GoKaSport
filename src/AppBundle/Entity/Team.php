<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Player;
use AppBundle\Entity\Tournament;

/**
 * Team
 *
 * // Nb : Architecture User > Person > Player in creation
 *        in use it can be player > person > user
 *
 * @ORM\Table(name="team")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TeamRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Team extends LbmExtensionEntity
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
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=255)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="sport_class", type="string", length=30, nullable=true)
     */
    private $sportClass;

    /**
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="teams")
     * @ORM\JoinTable(name="teams_users")
     */
    private $users;

    /**
    * @ORM\ManyToOne(targetEntity="Sport")
    * @ORM\JoinColumn(name="sport_id", referencedColumnName="id", nullable=true)
    */
    private $sport;


    /**
     *
     * @ORM\ManyToMany(targetEntity="Tournament", mappedBy="teams")
     * @ORM\JoinTable(name="tournaments_teams")
     */
    private $tournaments;


    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->users = new ArrayCollection();

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
     * @return Team
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
     * @return Team
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
     * Set city
     *
     * @param string $city
     *
     * @return Team
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
     * @return Team
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

    /**
     * Set sportClass
     *
     * @param string $sportClass
     *
     * @return Team
     */
    public function setSportClass($sportClass)
    {
        $this->sportClass = $sportClass;

        return $this;
    }

    /**
     * Get sportClass
     *
     * @return string
     */
    public function getSportClass()
    {
        return $this->sportClass;
    }

    public function addUser($user)
    {
        $this->users[] = $user;
        $user->addTeam($this);
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
            $result = new ArrayCollection();
            foreach($this->users as $user) {
                if($user->getRoleString() == $role) $result[] = $user;
            }
            return $result;
        }
        return $this->users;
    }

    public function getCoachs()
    {
        return $this->getUsers('COACH');
    }

    public function getPlayers()
    {
        return $this->getUsers('PLAYER');
    }


    public function addTournament(Tournament $tournament)
    {
        $this->tournaments[] = $tournament;
        return $this;
    }

    public function getTournaments()
    {
        return $this->tournaments;
    }

    public function toArray()
    {
      $objectArray = get_object_vars($this);
      return $objectArray;
    }
}
