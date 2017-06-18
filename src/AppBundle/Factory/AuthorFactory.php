<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Product;

class ProductFactory extends ApplicationMasterFactory
{ 
    protected $fieldKeys = ['username', 'role', 'plain_password', 'enabled'];
    protected $EntityType = 'AppBundle\Entity\Product';

    public function __construct($Repository, $Doctrine, $Manager)
    {
        $this->setDoctrine($Doctrine);
        //Case mut match that used in the getter and setter method as 'get'and 'set' wll be appended to the keys.
         $this->setFields([
            'Id' => $this->initializeField(
                'none', null, null, null, null
            )
            ,'Name' => $this->initializeField(
                'text', 'Name','','',['required'] 
            )
            ,'Image' => $this->initializeField(
                'image', 'Image', '',''             
            )
            ,'Title' => $this->initializeField(
                'text', 'text', '', ''
            )
            ,'Active' => $this->initializeField(
                'checkbox', 'Active', true, false, []
            )
        ]);

        parent::__construct($Repository, $Doctrine, $Manager);
    }

    public function entityEditUnique(&$Product)
    {

    } 
      
}
