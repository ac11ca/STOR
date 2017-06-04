<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Classes\ViewMessage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;

class ContentController extends PhotoATMMasterController
{
	

    public function contentAction(Request $Request, $friendly_url, $template=true, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $friendly_url, $template, $_render)
            {       
                $template_file = $template ? 'content.html.twig' : 'content_raw.html.twig';
                $result = $this->getDoctrine()->getRepository('AppBundle:CustomContent')->findBy(['friendlyUrl'=>$friendly_url,'published'=>true]);        
                if(empty($result))
                    return $this->redirect($this->generateUrl('404'));

                $CustomContent = array_pop($result);
                if($CustomContent->getId() == 1)
                    return $this->redirect($this->generateUrl('index'));
                // replace this example code with whatever you need
                return $this->renderRoute(
                    '/content/' . $template_file
                    ,[
                        'CustomContent'=>$CustomContent      
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('custom_page', ['friendly_url'=>$friendly_url,'template'=>$template, '_render'=>$_render])
			,$_render
		);
    }

    public function contentFooterAction(Request $Request, $_render = 'HTML')
    {
        $links = $this->getDoctrine()->getRepository('AppBundle:LinkRelationship')->findByNavigation(2);

        return $this->renderRoute(
            '/content/content_footer.html.twig'
            , [
                'links'=>$links
            ]
            , $_render
        );       
    }

    public function menuBarAction(Request $Request, $_render = 'HTML')
    {
        $links = $this->getDoctrine()->getRepository('AppBundle:LinkRelationship')->findByNavigation(1);

        return $this->renderRoute(
            '/content/menu_bar.html.twig'
            , [
                'links'=>$links
            ]
            , $_render
        );       
    }

    public function preRenderRoute() {}

}
