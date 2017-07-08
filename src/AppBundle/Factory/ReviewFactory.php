<?php

namespace AppBundle\Factory;

use CYINT\ComponentsPHP\Classes\ParseData;
use AppBundle\Entity\Review;

class ReviewFactory extends ApplicationMasterFactory
{ 
    protected $fieldKeys = ['created', 'product','comment', 'reviewer','rating'];
    protected $EntityType = 'AppBundle\Entity\Review';
    protected $CryptographyService;

    public function __construct($Repository, $Doctrine, $Manager)
    {
        $this->setDoctrine($Doctrine);
        //Case mut match that used in the getter and setter method as 'get'and 'set' wll be appended to the keys.
         $this->setFields([
            'Id' => $this->initializeField(
                'none', null, null, null, null
            )
            ,'Title' => $this->initializeField(
                'text', 'Title', '', '', ['required']
            )
            ,'Product' => $this->initializeField(
                'select', 'Product', null, null, ['required']
                 , $this->getProductOptions()
            )           
            ,'Created' => $this->initializeField(
                'datetime', 'Posted',time(),'',['required'] 
            )
            ,'Comment' => $this->initializeField(
                'textarea', 'Comment',''
            )
            ,'Rating' => $this->initializeField(
                'number', 'Rating',null,null,['required'],['min'=>0,'max'=>5,'step'=>0.5]
            )
            ,'Reviewer' => $this->initializeField(
                'text', 'Reviewer', '','',['required']
            )
            ,'HelpScore' => $this->initializeField(
                'number', 'Helpfulness', 0,0,['required']
            )

        ]);

        parent::__construct($Repository, $Doctrine, $Manager);
    }

    public function entityEditUnique(&$User)
    {

    }   

    protected function getProductOptions()
    {
        $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findBy(['active'=>true], ['name'=>'ASC']);
        return [
           'selectOptions' => [
               'type' => 'entity'	
               ,'options' => $products
               ,'repository' => 'AppBundle:Product'
               ,'valueGetter' => 'getId'
               ,'labelGetter' => 'getName'
           ]
       ]; 

    }
}
