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

    public function setFreeTrialEligible($free_trial_eligible)
    {
        $this->free_trial_eligible = $free_trial_eligible;
        return $this;
    }

    public function getFreeTrialEligible()
    {
        return $this->free_trial_eligible;
    }


    public function toArray()
    {
        return [
        ];

    }    

    public function getDiscounts()
    {
        return $this->discounts;
    }

    public function setDiscounts(ArrayCollection $discounts)
    {
        $this->discounts = $discounts;
        return $this;
    }

    public function addDiscount(Discount $Discount)
    {
        $this->discounts->add($Discount);
        return $this;
    }

    public function removeDiscount(Discount $Discount)
    {
        $this->discounts->remove($Discount);
    }

    public function getAlbums()
    {
        return $this->albums;
    }

    public function setAlbums(ArrayCollection $albums)
    {
        $this->albums = $albums;
        return $this;
    }

    public function addAlbum(Album $Album)
    {
        $this->albums->add($Album);
        return $this;
    }

    public function removeAlbum(Album $Album)
    {
        $this->albums->remove($Album);
    }



}

class UserException extends \Exception
{
    public function __construct($message) 
    {
        parent($message);
    }
}



