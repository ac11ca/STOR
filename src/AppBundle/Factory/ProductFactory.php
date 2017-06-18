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
            ,'Authors' => $this->initializeField(
                'select', 'Authors', null,null,['required']
                $this->getAuthorOptions()
            )
	        ,'Price' => $this->initializeField(
                'number', 'Price', 0.00,null,['required'], ['step'=>0.01]
            )
            ,'Tags' => $this->initializeField(
                'text', 'Tags (comma separated)', '',''
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
      
    public function getAuthorOptions()
    {
        $authors = $this->getDoctrine()->getRepository('AppBundle:Author')->findBy(['active'=>true], ['name'=>'ASC']);
        return [
           'selectOptions' => [
               'type' => 'entity'	
			   ,'multiple' => true
               ,'options' => $authors
               ,'repository' => 'AppBundle:Author'
               ,'valueGetter' => 'getId'
               ,'labelGetter' => 'getName'
           ]
       ]; 
    }
}
