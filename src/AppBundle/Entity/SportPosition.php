<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Sport
 *
 * @ORM\Table(name="sport_position")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SportPositionRepository")
 */
class SportPosition
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
    * @ORM\Column(name="abbreviation", type="string")
    */
    private $abbreviation;


    /**
    * @ORM\Column(name="description", type="string", nullable=true)
    */
    private $description;

    /**
    * @ORM\ManyToOne(targetEntity="Sport")
    * @ORM\JoinColumn(name="sport_id", referencedColumnName="id")
    */
    private $sport;


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
     * @return SportPosition
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get $abbreviation
     *
     * @return string
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * Set $abbreviation
     *
     * @return SportPosition
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
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
     * @return SportPosition
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }



    /**
     * Get Sport
     *
     * @return Sport
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Set sport
     *
     * @return SportPosition
     */
    public function setSport(Sport $sport)
    {
        $this->sport = $sport;
        return $this;
    }


}
