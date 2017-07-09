<?php

namespace AppBundle\Entity;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Session
 */
class Session
{
    /**
     * @var guid
     */
    private $id;
    private $created;
    private $User;
    private $Configuration;
    private $products;

    public function __construct(User $User, Configuration $Configuration)
    {
        $this->setUser($User);
        $this->setCreated(time());
        $this->setConfiguration($Configuration);
        $this->setProducts(new ArrayCollection());
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


    public function getConfiguration()
    {
        return $this->Configuration;
    }

    public function setConfiguration(Configuration $Configuration)
    {
        $this->Configuration = $Configuration;
        return $this;
    }

    public function setProducts(ArrayCollection $products)
    {
        $this->products = $products;
        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(Product $Product)
    {
        if($this->products->contains($Product))
            return $this;

        $this->products->add($Product);
        return $this;
    }

    public function removeProduct(Product $Product)
    {
        if($this->products->contains($Product))
            $this->products->remove($Product);

        return $this;
    }
}
