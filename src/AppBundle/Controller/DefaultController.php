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
use AppBundle\Entity\Session as DBSession;
use AppBundle\Entity\Analytics;
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
                $User = $UserManager->findUserBy(['external_id'=>$user]);
                if(empty($User))
                {
                    $User = $UserManager->createUser();
                    $User->setUsername($user . '@STOR.com');
                    $User->setEmail($user . '@STOR.com');
                    $User->setPlainPassword($user . '@STOR.com');
                    $User->setExternalId($user);
                    $UserManager->updateUser($User);
                }

                $DBSession = new DBSession($User);
                $EntityManager = $this->getDoctrine()->getManager();
                $EntityManager->persist($DBSession);
                $EntityManager->flush();

                $Session->set('user_id', $user);
                $Session->set('SessionID', $DBSession->getId());

                $CustomContent = $this->getDoctrine()->getRepository('CYINTCustomContentBundle:CustomContent')->find(1);
                $term = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('search');
                $Session->set('term',$term['term']);
                
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


    public function searchResultsAction(Request $Request, $term, $page = 1, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $term, $page, $_render)
            {            
                $pagination_settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('pagination');                 
                $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findByFilter($term,null,$page, $pagination_settings['items_per_page']);               
                $ratings = $this->getDoctrine()->getRepository('AppBundle:Review')->findByProductAverages($products['result']);
                return $this->renderRoute(
                    'default/results.html.twig'
                    ,[
                        'products' => $products
                        ,'items_per_page' => $pagination_settings['items_per_page']
                        ,'ratings' => $ratings
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
                $Product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($product);                                  
                $ratings = $this->getDoctrine()->getRepository('AppBundle:Review')->findByProductAverages(new ArrayCollection([$Product]));
                $reviews = $this->getDoctrine()->getRepository('AppBundle:Review')->findBy(['Product'=>$Product], ['rating'=>'DESC']);
                $ratings_by_value = $this->getDoctrine()->getRepository('AppBundle:Review')->findByProductAndValue($Product);

                return $this->renderRoute(
                    'default/details.html.twig'
                    ,[
                        'Product' => $Product
                        ,'ratings' => $ratings  
                        ,'ratings_by_value' => $ratings_by_value
                        ,'term' => $Session->get('term')
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('product_details', ['_render'=>$_render])
			,$_render
		);

    }

    public function productReviewsAction(Request $Request, $product, $page = 1, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $product, $page)
            {    
                $sort = empty($Session->get('sort')) ? 'e.created' : $Session->get('sort');
                $dir = empty($Session->get('sort')) ? 'DESC' : $Session->get('dir');

                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    die(print_r($form_data));
                    $sortdata = ParseData::setArray($form_data, 'sort', 'e.created:DESC');
                    $sortarray = explode(':', $sortdata);
                    $sort = $sortarray[0];
                    $dir = $sortarray[1];
                    $Session->set('sort', $sort);
                    $Session->set('dir', $dir);
                    $page = 1;
                }

                $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('');
                $Product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($product);                                  
                $reviews = $this->getDoctrine()->getRepository('AppBundle:Review')->findByFilter(null,$Product->getId(), $page, $settings['reviewsperpage'], $sort, $dir);

                return $this->renderRoute(
                    'default/reviews.html.twig'
                    ,[
                        'Product' => $Product                    
                        ,'reviews' => $reviews['result']
                        ,'review_count' => $reviews['count']
                        ,'page' => $page
                        ,'term' => $Session->get('term')
                        ,'reviews_per_page' => $settings['reviewsperpage']
                        ,'sort' => $sort . ':' . $dir
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
            function ($Session, $messages) use ($Request, $_render)
            {    
                $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('');                              
                $term = $Session->get('term');
                $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findByFilter($term);               
                $User = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(['external_id'=>$Session->get('user_id')]);
                if(!empty($User))
                    $User = array_pop($User);
                return $this->renderRoute(
                    'default/checkout.html.twig'
                    ,[
                        'products' => $products
                        ,'items_per_page' => $settings['paginationitemsperpage']
                        ,'redirect_url' => $settings['formurl']
                        ,'term' => $term
                        ,'User' => $User
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

    public function trackEventAction(Request $Request, $_render = 'JSON')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {

                $success = false;
                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $type = ParseData::setArray($form_data,'event',null);
                    $label = ParseData::setArray($form_data,'label',null);        
                    $category = ParseData::setArray($form_data,'category',null);        
                    if(empty($type) || empty($label))
                        throw new \Exception('Label and category must be specified');

                    $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));
                    $Analytic = new Analytics($DBSession, $type, $label, $category);
                    $EntityManager = $this->getDoctrine()->getManager();
                    $EntityManager->persist($Analytic);
                    $EntityManager->flush();
                    $success = true;
                }

                return $this->renderRoute(
                    null
                    , [
                        'success'=>$success               
                    ]
                    , $_render
                );       
            }
            ,$this->generateUrl('track_event', ['render'=>$_render])
			,$_render
        );
    }

}
