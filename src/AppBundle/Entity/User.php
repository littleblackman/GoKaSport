<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * User
 *
 * // Nb : Architecture User > Person > Player in creation
 *        in use it can be player > person > user
 *
 * @ORM\Table(name="lbm_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class User extends LbmExtensionEntity implements UserInterface
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
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="array", nullable=true)
     */
    private $roles;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Tournament", mappedBy="users")
     * @ORM\JoinTable(name="tournaments_users")
     */
    private $tournaments;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Team", mappedBy="users")
     * @ORM\JoinTable(name="teams_users")
     */
    private $teams;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Match", mappedBy="users")
     * @ORM\JoinTable(name="matchs_users")
     */
    private $matchs;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    private $rolesUF = [    'ADMIN'     => 'Administrateur',
                                            'COACH'     => 'Coach',
                                            'MANAGER'   => 'Organisateur.trice',
                                            'REFEREE'   => 'Arbitre',
                                            'PLAYER'    => 'Joueur.se'
                                ];


    public function __construct()
    {
        $this->tournaments = new ArrayCollection();
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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function getRoleString($separator = ',')
    {
          if(count($this->getRoles()) == 1) {
              $string = str_replace('ROLE_', '', $this->getRoles()[0]);
          } else {
              $string = implode($separator, $this->getRoles());
          }
          return $string;
    }

    public function getPerson()
    {
        return $this->person;
    }


    public function setPerson(Person $person)
    {
        $this->person = $person;
        $person->setUser($this);
        return $this;
    }

    public function showRoleUF()
    {
        return $this->rolesUF[$this->getRoleString()];
    }

    public function addMatch(Match $match)
    {
        $this->matchs[] = $match;
        return $this;
    }

    public function getMatchs()
    {
        return $this->matchs;
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

    public function addTeam(Team $team)
    {
        $this->teams[] = $team;
        return $this;
    }

    public function getTeams()
    {
        return $this->teams;
    }

    public function toArray()
    {
        $objectArray = get_object_vars($this);
        unset($objectArray['rolesUF']);
        unset($objectArray['tournaments']);
        unset($objectArray['password']);
        unset($objectArray['id']);

        $objectArray['person'] = $this->getPerson()->toArray();
        $objectArray['user_id'] = $this->getId();
        $objectArray['role'] = $this->getRoleString();

        if($this->getPerson()->getPlayer())
        {
            $objectArray['sport'] = [
                                                            'name' => $this->getPerson()->getPlayer()->getPosition()->getSport()->getName(),
                                                            'position' => $this->getPerson()->getPlayer()->getPosition()->getName()
                                                        ];

        };

        if(  count($this->getTeams()) >0) {
            foreach($this->getTeams() as $team) {
                $teamsArr[] = $team->toArray();
            }
            $objectArray['teams'][] = $teamsArr;
        }

        return $objectArray;
    }


    public function eraseCredentials()
    {
    }
}
