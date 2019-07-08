<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;



/**
 * Sport
 *
 * @ORM\Table(name="sport")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SportRepository")
 */
class Sport
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
    * @ORM\Column(name="name", type="string")
    */
    private $name;

    /**
    * @ORM\Column(name="description", type="string", nullable=true)
    */
    private $description;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="SportPosition", mappedBy="sport", cascade={"persist", "remove"}))
    */
    private $positions;

    /**
    * @var ArrayCollection
    * @ORM\OneToMany(targetEntity="Team", mappedBy="sport"))
    */
    private $teams;


    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->positions = new ArrayCollection();
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @return Sport
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set name
     *
     * @return Sport
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getTeams()
    {
        return $this->teams;
    }

    public function getPositions()
    {
        return $this->positions;
    }


}
