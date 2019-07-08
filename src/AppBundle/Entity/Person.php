<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\User;

use DateTime;

/**
 * Person
 *
 * @ORM\Table(name="person")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PersonRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @author Sandy Razafitrimo <sandy@etsik.com>
 */
class Person extends LbmExtensionEntity
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
     * @var string|null
     *
     * @ORM\Column(name="firstname", type="string", length=64, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=64, nullable=false)
     */
    private $lastname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="photo", type="string", length=256, nullable=true)
     */
    private $photo;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="birthdate", type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @var Player
     *
     * @ORM\OneToOne(targetEntity="Player", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     */
    private $player;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function getId()
    {
        return $this->id;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFullname()
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function getFullnameR()
    {
        return $this->lastname.' '.$this->firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto(string $photo)
    {
        $this->photo = $photo;

        return $this;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
          if (!$birthdate instanceof DateTime) {
              $birthdate = new DateTime($birthdate);
          }

      $this->birthdate = $birthdate;

      return $this;
    }

    public function getPlayer()
    {
        return $this->player;
    }


    public function setPlayer(Player $player)
    {
        $this->player = $player;
        $player->setPerson($this);
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }


    public function getAge()
    {
        if($this->getBirthdate() == null) return null;
        $datetime1 = $this->getBirthdate();
        $datetime2 = new DateTime("now");
        $interval = $datetime1->diff($datetime2);
        return $interval->y;
    }

    public function toArray()
    {
      $objectArray = get_object_vars($this);
      return $objectArray;
    }

}
