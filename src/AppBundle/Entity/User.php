<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use CYINT\ComponentsPHP\Services\CryptographyService;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    protected $id;
    protected $created;
    protected $reviews;

    public function __construct()
    {
        parent::__construct();
    }

    public function setCryptographyService(CryptographyService $Cryptography = null)
    {
        $this->Cryptography = $Cryptography;
    }

    public function lastUpdated()
    {
        return $this->lastupdated;
    }

    public function getEmail()
    {
        if(!empty($this->Cryptography)) 
            return $this->Cryptography->decrypt(trim($this->email));

        return $this->email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getUsernameCanonical()
    {
        return $this->username_canonical;
    }

    public function getEmailCanonical()
    {      
        if(!empty($this->Cryptography))
            return $this->Cryptography->decrypt(trim($this->email_canonical));
        return $this->email_canonical;
    }

    public function setEmail($email)
    {
        $this->email = $this->Cryptography->encrypt(strtolower(trim($email)));
        return $this;
    }

    public function setUsername($username)
    {
        $this->username = trim($username); 
        return $this;
    }

    public function setUsernameCanonical($username)       
    {
        $this->usernameCanonical = trim($username);
        return $this;
    }

    public function setEmailCanonical($email)
    {
        if(!empty($this->Cryptography))
        {
            $this->emailCanonical = $this->Cryptography->encrypt(strtolower(trim($email)));
        }
        else
        {
            $this->emailCanonical = $email;
        }

        return $this;
    }

    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    public function getRole()
    {
        if(!empty($this->roles))
        {
            return $this->roles[0];
        }

        return null;
    }

    public function setRole($role)
    {
        if(!empty($role))
            $this->roles = [$role];
        else
            $this->roles = null;
        
        return $this;
    }

    public function toArray()
    {
        return [
        ];

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
     * Set reviews
     *
     * @param \AppBundle\Entity\Review $reviews
     *
     * @return User
     */
    public function setReviews(\AppBundle\Entity\Review $reviews = null)
    {
        $this->reviews = $reviews;

        return $this;
    }

    /**
     * Get reviews
     *
     * @return \AppBundle\Entity\Review
     */
    public function getReviews()
    {
        return $this->reviews;
    }


}

class UserException extends \Exception
{
    public function __construct($message) 
    {
        parent($message);
    }

}
