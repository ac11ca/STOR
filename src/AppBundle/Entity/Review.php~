<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Review
 */
class Review
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
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $rating;

    /**
     * @var \AppBundle\Entity\Product
     */
    private $Product;


    /**
     * @var string
     */
    private $reviewer;

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
     * @return Review
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
     * Set comment
     *
     * @param string $comment
     *
     * @return Review
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set rating
     *
     * @param string $rating
     *
     * @return Review
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return string
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set product
     *
     * @param \AppBundle\Entity\Product $product
     *
     * @return Review
     */
    public function setProduct(\AppBundle\Entity\Product $product = null)
    {
        $this->Product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \AppBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * Set reviewer
     *
     * @param string $reviewer
     *
     * @return Review
     */
    public function setReviewer($reviewer)
    {
        $this->reviewer = $reviewer;

        return $this;
    }

    /**
     * Get reviewer
     *
     * @return string
     */
    public function getReviewer()
    {
        return $this->reviewer;
    }
    /**
     * @var integer
     */
    private $help_score = 0;


    /**
     * Set helpScore
     *
     * @param integer $helpScore
     *
     * @return Review
     */
    public function setHelpScore($helpScore)
    {
        $this->help_score = $helpScore;

        return $this;
    }

    /**
     * Get helpScore
     *
     * @return integer
     */
    public function getHelpScore()
    {
        return $this->help_score;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}
