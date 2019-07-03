<?php

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use AppBundle\Entity\User;



/**
 * Class LbmExtensionEntity
 * Add commons methods to all entities
 *
 * @package LBM\ExtensionBundle\Service
 */
class LbmExtensionEntity
{

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    protected $createdBy;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    protected $updatedBy;

    /**
     * @var int
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    protected $isActive;

    /**
     * @var int
     *
     * @ORM\Column(name="is_archived", type="boolean", nullable=true)
     */
    protected $isArchived;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return LbmCompany
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return LbmCompany
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return LbmCompany
     */
    public function setCreatedBy(User $user)
    {
        $this->createdBy = $user;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param string $updatedBy
     *
     * @return LbmCompany
     */
    public function setUpdatedBy(User $user)
    {
        $this->updatedBy = $user;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     *
     * set is_active value
     *
     * @param $is_active
     * @return $this
     */
    public function setIsActive($is_active)
    {
        $this->isActive = $is_active;
        return $this;
    }

    /**
     *
     * get is_active value
     * @return int
     */
    public function getIsActive()
    {
        return $this->isActive;
    }


    /**
     *
     * set is_archived value
     *
     * @param $is_archived
     * @return $this
     */
    public function setIsArchived($is_archived)
    {
        $this->isArchived = $is_archived;
        return $this;
    }

    /**
     *
     * get is_archived value
     * @return int
     */
    public function getIsArchived()
    {
        return $this->isArchived;
    }


    /********* PRE PERSIST ************/

    /**
     * @ORM\PrePersist
     */
    public function updateAt()
    {
        $this->setUpdatedAt(new \Datetime());

    }

    /**
     * @ORM\PrePersist
     */
    public function createdAt()
    {
        if($this->createdAt == null) $this->setCreatedAt(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     */
    public function isActive()
    {
        if(!$this->isActive) $this->is_active = 1;
    }

    /**
     * @ORM\PrePersist
     */
    public function isArchived()
    {
        if(!$this->isArchived) $this->is_archived = 0;
    }
    /**
     * @ORM\PrePersist
     */
    public function createdBy()
    {
        /*
        if($this->tokenStorage()->getToken()->getUser() && ($this->createdBy == null || $this->createdBy == "")) {
            $this->createdBy = $tokenStorage()->getToken()->getUser()->getUsername();
        }*/
    }

    /**
     * @ORM\PrePersist
     */
    public function updatedBy()
    {
        /*
        if($user = $this->tokenStorage()->getToken()->getUser()) {
            $this->createdBy = $user->getUsername();
        }*/
    }


}
