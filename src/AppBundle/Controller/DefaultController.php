<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use AppBundle\Entity\Subscription;
use CYINT\ComponentsPHP\Classes\ViewMessage;
use CYINT\ComponentsPHP\Classes\ParseData;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\RouteEntity;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\Machine;

class DefaultController extends ApplicationMasterController
{
    public function rootAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {
                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $user = ParseData::setArray($form_data, 'user', null);
                    if(empty($user))
                        throw new \Exception('Invalid user id');

                    return $this->redirect($this->generateUrl('index', ['user'=>$user]));
                }

                return $this->renderRoute(
                    'default/root.html.twig'
                    ,[
                    ]
                    , $_render
                );
      
            }
            ,$this->generateUrl('root', ['_render'=>$_render])
			,$_render
		);
    }

    public function indexAction(Request $Request, $user, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $user, $_render)
            {       
                if(empty($user))
                    throw new \Exception('Invalid user id');

                $UserManager= $this->get('app.user_manager'); 
                $User = $UserManager->findUserBy(['id'=>$user]);
                if(empty($user))
                    throw new \Exception('Invalid user id');

                $Session->set('user_id', $user);

                $CustomContent = $this->getDoctrine()->getRepository('CYINTCustomContentBundle:CustomContent')->find(1);
                $term = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('search');
                
                return $this->renderRoute(
                    'default/index.html.twig'
                    ,[
                        'CustomContent' => $CustomContent
                        ,'term' => $term['term']
                    ]
                    , $_render
                );

            }
            ,$this->generateUrl('root', ['_render'=>$_render])
			,$_render
		);
    }


    public function searchResultsAction(Request $Request, $page = 1, $term, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $term, $page, $_render)
            {    
                $PaginationService = $this->get('app.pagination');
                
                $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findByFilter($term);
                $page_data = $PaginatorService->processPagination($products, $page);                
               
                return $this->renderRoute(
                    'default/results.html.twig'
                    ,[
                        'items_per_page' => $pagination_settings['items_per_page']
                        ,'page_data' => $page_data
						,'page' => $page
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('search_results', ['_render'=>$_render, 'term'=>null])
			,$_render
		);

    }

    public function productDetailsAction(Request $Request, $product, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $product)
            {    

                return $this->renderRoute(
                    'default/product_details.html.twig'
                    ,[
                        ''
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('product_details', ['_render'=>$_render])
			,$_render
		);

    }

    public function productReviewsAction(Request $Request, $product, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $product)
            {    

                return $this->renderRoute(
                    'default/product_reviews.html.twig'
                    ,[
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('product_reviews', ['_render'=>$_render, 'product'=>$product])
			,$_render
		);

    }

    public function checkoutAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $product)
            {    

                return $this->renderRoute(
                    'default/checkout.html.twig'
                    ,[
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('checkout', ['_render'=>$_render])
			,$_render
		);
    }

    public function abandonAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {    
                $Session->clear();
                $messages[] = ViewMessage::constructMessage('Your cart has been abandoned.', 'danger', null);
                return $this->redirect($this->generateUrl('root'));
            }
            ,$this->generateUrl('root', ['_render'=>$_render])
			,$_render
		);
    }





    public function messagesAction(Request $Request, $_render = 'HTML')
    {
        $Session = $this->get('session');
        $messages = empty($Session->get('messages')) ? [] : $Session->get('messages');
        $Session->set('messages', null);

        return $this->renderRoute('components/messages.html.twig', ['messages'=>$messages]);
    }

    public function routesAction(Request $Request, $_render='HTML')
    {
          return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
                 $router = $this->get('router');
                 $collection = $router->getRouteCollection(); 
                 $allRoutes = $collection->all();
                 $routes = [];
                 
			     /** @var $params \Symfony\Component\Routing\Route */
				 foreach ($allRoutes as $route => $params)
				 {
					$defaults = $params->getDefaults();

					if (isset($defaults['_controller']))
					{
						$controllerAction = explode(':', $defaults['_controller']);
						$controller = $controllerAction[0];

						if (!isset($routes[$controller])) {
							$routes[$controller] = array();
						}
                      
					 	$routes[$controller][] = new RouteEntity($route,$this->generateUrl($route));
					 }
				 }

   			  	 $thisRoutes = isset($routes[get_class($this)]) ?
											$routes[get_class($this)] : null ;

                 return $this->renderRoute(
                    '/default/routes.html.twig'
                    ,[
                        'routes' => $thisRoutes
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('routes', ['render'=>$_render])
			,$_render
		);
   
    }

    public function notfoundAction(Request $Request, $machine, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {
                return $this->renderRoute(
                    '/default/notfound.html.twig'
                    , []
                    , $_render
                );
            }
            ,$this->generateUrl('404', ['_render'=>$_render])
			,$_render
		);
    }


    public function gaTagAction(Request $Request, $_render = 'HTML')
    {
        $setting = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findOneBy(['settingKey'=>'ga_account']);
        return $this->renderRoute(
            '/default/gaTag.html.twig'
            , [
                'account'=>$setting->getValue()
            ]
            , $_render
        );       
    }

    public function headerHTMLAction(Request $Request, $_render = 'HTML')
    {
        $setting = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findOneBy(['settingKey'=>'html_header']);
        return $this->renderRoute(
            '/default/HTML.html.twig'
            , [
                'HTML'=>$setting->getValue()
            ]
            , $_render
        );
       
    }


    public function contentFooterAction(Request $Request, $_render = 'HTML')
    {
        $links = $this->getDoctrine()->getRepository('AppBundle:LinkRelationship')->findByNavigation(2);

        return $this->renderRoute(
            '/default/content_footer.html.twig'
            , [
                'links'=>$links
            ]
            , $_render
        );       
    }


    public function footerHTMLAction(Request $Request, $_render = 'HTML')
    {
        $setting = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findOneBy(['settingKey'=>'html_footer']);
        return $this->renderRoute(
            '/default/HTML.html.twig'
            , [
                'HTML'=>$setting->getValue()
            ]
            , $_render
        );       
    }

}
