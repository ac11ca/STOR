<?php
   
namespace AppBundle\EventListener;

use AppBundle\Controller\DashboardController;
use AppBundle\Interfaces\RegisteredUserControllerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Bundle\FrameworkBundle\Routing\Router; 
use AppBundle\Classes\ViewMessage;
use Symfony\Component\HttpFoundation\Session\Session;

class RegisteredUserAccessListener
{
    private $TokenStorage;
    private $Router;
    public function __construct(TokenStorage $TokenStorage, Router $Router)
    {
        $this->TokenStorage = $TokenStorage;
        $this->Router = $Router;
    }

    public function onKernalController(FilterControllerEvent $Event)
    {
        $Controller = $Event->getController();

        if(!is_array($Controller))         
            return;           
    }


}
