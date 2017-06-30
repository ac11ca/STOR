<?php

namespace AppBundle\Factory;

use CYINT\ComponentsPHP\Classes\ParseData;
use AppBundle\Entity\User;

class UserFactory extends ApplicationMasterFactory
{ 
    protected $fieldKeys = ['username', 'role', 'plain_password', 'enabled', 'external_id', 'ip_address'];
    protected $EntityType = 'AppBundle\Entity\User';
    protected $CryptographyService;

    public function __construct($Repository, $Doctrine, $Manager, $CryptographyService)
    {
        $this->setDoctrine($Doctrine);
        //Case mut match that used in the getter and setter method as 'get'and 'set' wll be appended to the keys.
         $this->setFields([
            'Id' => $this->initializeField(
                'none'
            )          
            ,'Username' => $this->initializeField(
                'text', 'Username/Email','','',['required'] 
            )
            ,'IPAddress' => $this->initializeField(
                'text', 'IP Address', '', '',''
            )
            ,'ExternalId' => $this->initializeField(
                'text', 'External ID', '', '',['required']
            )
            ,'Role' => $this->initializeField(
                'select', 'Role', 'ROLE_ADMIN', 'ROLE_ADMIN', ['required']
                ,$this->getRoleOptions()
            )
            ,'PlainPassword' => $this->initializeField(
                'password', 'Password', null, null
            )
            ,'Enabled' => $this->initializeField(
                'checkbox', 'Active', false, false, [], ['getter'=>'isEnabled']
            )
        ]);

        $this->CryptographyService = $CryptographyService;
        parent::__construct($Repository, $Doctrine, $Manager);
    }

    public function entityConstruction(&$Entity)
    {
        if(empty($this->fields['ExternalId']['value']))
            throw new \Exception('External ID is required');

        $Entity = $this->Manager->createUser();
        $Entity->setCryptographyService($this->CryptographyService);
        $Entity->setExternalId($this->fields['ExternalId']['value']);
        $Entity->setUsername($this->fields['Username']['value']);
        $Entity->setEmail($this->fields['Username']['value']);
        $Entity->setPlainPassword($this->fields['PlainPassword']['value']);
        $Entity->setEnabled(empty($this->fields['Enabled']['value']) ? false : true);
        $Entity->setIpAddress($this->fields['IPAddress']['value']);
    }

    public function entityEditUnique(&$User)
    {

    } 
    
    public function persistData($User)
    {
        $this->Manager->updateUser($User);
    }

    public function getSuccessMessage($create = true)
    {
        if($create)
            return "The user has been created successfully.";
        else
            return "The user has been updated successfully.";
    }

    public function getExceptionMessage(\Exception $Ex = null)
    {    
        switch(get_class($Ex))
        {
            case 'Doctrine\DBAL\Exception\UniqueConstraintViolationException':
                return 'A user with this user name already exists, and user names must be unique. Please enter a new user name and try again.';
            break;

            default:
                return $Ex->getMessage();
            break; 
        }
    }    
 
    private function getRoleOptions()
    {
        return [
            'selectOptions' => [
                'type' => 'static'
                ,'options' => [
                    'Admin' => 'ROLE_ADMIN'
                    ,'User' => 'ROLE_AUTHENTICATED'
                ]
            ]
        ];
    }

   
}
