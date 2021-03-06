<?php

namespace AppBundle\Entity;

use \Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Session;

/**
 * Product
 */
class Product
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $created;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $price = 0.0;

    /**
     * @var string
     */
    private $tags;

    /**
     * @var boolean
     */
    private $active = true;

    private $reviews;
    private $authors;
    private $sales;
    private $configurations;
	private $sessions;
    private $sort_order;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setCreated(time());
        $this->setReviews(new ArrayCollection());
        $this->setConfigurations(new ArrayCollection());
		$this->setSessions(new ArrayCollection());
        $this->setSales(0);
    }

    /**
     * Get id
     *
     * @return integer
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
     * @return Product
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
     * Set name
     *
     * @param string $name
     *
     * @return Product
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
     * Set image
     *
     * @param string $image
     *
     * @return Product
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Product
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set tags
     *
     * @param string $tags
     *
     * @return Product
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Product
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Add review
     *
     * @param \AppBundle\Entity\Review $review
     *
     * @return Product
     */
    public function addReview(\AppBundle\Entity\Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param \AppBundle\Entity\Review $review
     */
    public function removeReview(\AppBundle\Entity\Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * Get reviews
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    public function setReviews(ArrayCollection $reviews)
    {
        $this->reviews = $reviews;
        return $this;
    }



    /**
     * Add author
     *
     * @param \AppBundle\Entity\Author $author
     *
     * @return Product
     */
    public function addAuthor(\AppBundle\Entity\Author $author)
    {
        $this->authors->add($author);
        return $this;
    }

    /**
     * Remove author
     *
     * @param \AppBundle\Entity\Author $author
     */
    public function removeAuthor(\AppBundle\Entity\Author $author)
    {
        $this->authors->removeElement($author);
    }

    /**
     * Get authors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    public function setAuthors(ArrayCollection $authors)
    {
        $this->authors = $authors;
        return $this;
    }
    /**
     * @var string
     */
    private $description;


    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
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

    public function setSales($sales)
    {
        $this->sales = $sales;
        return $this;
    }

    public function getSales()
    {
        return $this->sales;
    }

    public function setConfigurations(ArrayCollection $configurations = null)
    {
        $this->configurations = empty($configurations) ?  new ArrayCollection() : $configurations;
        return $this;
    }

    public function getConfigurations()
    {
        return $this->configurations;
    }

    public function addConfiguration(Configuration $Configuration)
    {
        if($this->configurations->contains($Configuration))
            return $this;

        $this->configurations->add($Configuration);
        return $this;
    }

    public function removeConfiguration(Configuration $Configuration)
    {
        if($this->configurations->contains($Configuration))
            $this->configurations->remove($Configuration);

        return $this;
    }

    public function setSessions(ArrayCollection $sessions = null)
    {
        $this->sessions = empty($sessions) ?  new ArrayCollection() : $sessions;
        return $this;
    }

    public function getSessions()
    {
        return $this->sessions;
    }

    public function addSession(Session $Session)
    {
        if($this->sessions->contains($Session))
            return $this;

        $this->sessions->add($Session);
        return $this;
    }

    public function removeSession(Session $Session)
    {
        if($this->sessions->contains($Session))
            $this->sessions->remove($Session);

        return $this;
    }


    public function getSortOrder()
    {
        return $this->sort_order;
    }

    public function setSortOrder($sort_order)
    {
        $this->sort_order = $sort_order;
        return $this;
    }
}
