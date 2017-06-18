<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;

class ModalController extends ApplicationMasterController
{
    public function deleteCartItemAction(Request $Request, $_render = 'HTML', $id = null, $slot = null, $mode = null)
    {
        return $this->renderRoute(
            '/modal/delete_cart_item.html.twig'
            ,[
                'id' => $id
                ,'slot' => $slot
                ,'mode' => $mode
            ]
            ,$_render
        );

    }

    public function cancelOrderAction(Request $Request, $_render = 'HTML')
    {
        return $this->renderRoute(
            '/modal/cancel_order.html.twig'
            ,[
            ]
            ,$_render
        );

    }

    public function preRenderRoute() {}
}
