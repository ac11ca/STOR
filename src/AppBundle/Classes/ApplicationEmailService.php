<?php

namespace AppBundle\Classes;
use Doctrine\Bundle\DoctrineBundle\Registry;
use CYINT\ComponentsPHP\Services\EmailService;

class ApplicationEmailService extends EmailService
{

    public function sendForgotPasswordEmail($to, $token, $subject = 'Forget your password?')
    {
        $message = $this->message
            ->setSubject($subject)
            ->setFrom($this->support_email)
            ->setTo($to)
            ->setBody(
                $this->Templating->render(
                    // app/Resources/views/Emails/registration.html.twig
                    'email/forgot.html.twig',
                    array('token' => $token)
                ),
                'text/html'
            )
        ;
        $this->getSwiftMailer()->send($message);        

    }

    public function sendErrorNotification($Transaction, $Machine, $error)
    {
        $message = $this->message
            ->setSubject('Error: Kiosk ' . $Machine->getId())
            ->setFrom($this->getFromEmail())
            ->setTo($this->support_email)
            ->setBody(
                $this->Templating->render(
                    // app/Resources/views/Emails/registration.html.twig
                    'email/error.html.twig',                   
                    array(
                        'Transaction' => $Transaction->toArray()
                        ,'Machine' => $Machine->toArray()
                        ,'error' => $error
                    )
                ),
                'text/html'
            )
        ;
        $this->getSwiftMailer()->send($message);        
    }
}

?>
