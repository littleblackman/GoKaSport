<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Team;
use AppBundle\Entity\MatchDetails;

/**
 * Player
 *
 * // Nb : Architecture User > Person > Player in creation
 *        in use it can be player > person > user
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlayerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Player extends LbmExtensionEntity
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
      * @ORM\ManyToOne(targetEntity="SportPosition")
      * @ORM\JoinColumn(name="position_id", referencedColumnName="id", nullable=true)
      */
      private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @var string
     *
     * @ORM\Column(name="shirt_number", type="string", length=10, nullable=true)
     */
    private $shirtNumber;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="MatchDetails", mappedBy="player", cascade={"remove"}))
    * @ORM\OrderBy({"created_at" = "DESC"})
    */
    private $matchDetails;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;


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
     * Set position
     *
     * @param SportPosition
     *
     * @return Player
     */
    public function setPosition(SportPosition $position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return SportPosition
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPositionName()
    {
        return $this->getPosition()->getName();
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     *
     * @return Player
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set shirtNumber
     *
     * @param string $shirtNumber
     *
     * @return Player
     */
    public function setShirtNumber($shirtNumber)
    {
        $this->shirtNumber = $shirtNumber;

        return $this;
    }

    /**
     * Get shirtNumber
     *
     * @return string
     */
    public function getShirtNumber()
    {
        return $this->shirtNumber;
    }


    public function getPerson()
    {
        return $this->person;
    }

    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    public function getSport()
    {
      return $this->getPosition()->getSport();
    }

    public function getSportName()
    {
      return $this->getSport()->getName();
    }

    public function toArray()
    {
      $objectArray = get_object_vars($this);

      return $objectArray;
    }
}
