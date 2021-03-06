<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Product;

class ProductFactory extends ApplicationMasterFactory
{ 
    protected $fieldKeys = ['name','image','title','authors','price','tags','active','sales','configurations'];
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
                'text', 'Title','','',['required'] 
            )
             ,'Authors' => $this->initializeField(
                'select', 'Authors', null,null,null,
                $this->getAuthorOptions()
            )
	        ,'Price' => $this->initializeField(
                'number', 'Price', 0.00,null,null, ['step'=>0.01]
            )
            ,'Description' => $this->initializeField(
                'richtext', 'Description', '', ''
            )

            ,'Image' => $this->initializeField(
                'image', 'Image', '',''             
            )
            ,'Configurations' => $this->initializeField(
                'select', 'Configurations', null, null, null,
                $this->getConfigurationOptions()
            )
            ,'Sales' => $this->initializeField(
                'number', 'Sales', 0, 0
            )
            ,'SortOrder' => $this->initializeField(
                'number', 'Sort Order', 0, 0
            )

            ,'Tags' => $this->initializeField(
                'text', 'Tags (comma separated)', '',''
            )			
        ]);

        $this->settings = ['cloudinary' => $Doctrine->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('cloudinary') ];

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

    public function getConfigurationOptions()
    {
        $configurations = $this->getDoctrine()->getRepository('AppBundle:Configuration')->findBy([], ['id'=>'DESC']);
        return [
           'selectOptions' => [
               'type' => 'entity'	
			   ,'multiple' => true
               ,'options' => $configurations
               ,'repository' => 'AppBundle:Configuration'
               ,'valueGetter' => 'getId'
               ,'labelGetter' => 'getId'
           ]
       ]; 
    }

}
