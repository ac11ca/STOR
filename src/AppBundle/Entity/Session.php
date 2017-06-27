<?php

namespace AppBundle\Entity;

/**
 * Session
 */
class Session
{
    /**
     * @var guid
     */
    private $id;

    /**
     * @var integer
     */
    private $created;

    /**
     * @var \AppBundle\Entity\User
     */
    private $User;

    public function __construct(User $User)
    {
        $this->setUser($User);
        $this->setCreated(time());
    }


    /**
     * Get id
     *
     * @return guid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param integer $created
     *
     * @return Session
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return integer
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Session
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->User = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->User;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $analytics;


    /**
     * Add analytic
     *
     * @param \AppBundle\Entity\Analytics $analytic
     *
     * @return Session
     */
    public function addAnalytic(\AppBundle\Entity\Analytics $analytic)
    {
        $this->analytics[] = $analytic;

        return $this;
    }

    /**
     * Remove analytic
     *
     * @param \AppBundle\Entity\Analytics $analytic
     */
    public function removeAnalytic(\AppBundle\Entity\Analytics $analytic)
    {
        $this->analytics->removeElement($analytic);
    }

    /**
     * Get analytics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAnalytics()
    {
        return $this->analytics;
    }
}
