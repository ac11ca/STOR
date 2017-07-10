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
    protected $no_hook = false;

    public function rootAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {
                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $user = ParseData::setArray($form_data, 'user', null);
                    $configuration = ParseData::setArray($form_data, 'configuration', null); 
                    if(empty($user))
                        throw new \Exception('Invalid user id');

                    return $this->redirect($this->generateUrl('index', ['user'=>$user, 'configuration'=>$configuration]));
                }
                else 
                {
                    $visit = $this->getCurrentVisit('root_visit', $Session);
                }

                return $this->renderRoute(
                    'default/root.html.twig'
                    ,[
                        'visit'=>$visit
                    ]
                    , $_render
                );
      
            }
            ,$this->generateUrl('root', ['_render'=>$_render])
			,$_render
		);
    }

    public function indexAction(Request $Request, $user, $configuration, $_render = 'HTML')
    {
         $this->no_hook = true;
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $user, $configuration, $_render)
            {       
                if(empty($user))
                    throw new \Exception('Invalid user id');

                if(empty($configuration))
                    throw new \Exception('Invalid configuration id');

                $Session->clear();
                $visit = $this->getCurrentVisit('index_visit', $Session);
                $UserManager= $this->get('app.user_manager'); 
                $User = $UserManager->findUserBy(['external_id'=>$user]);
                if(empty($User))
                {
                    $User = $UserManager->createUser();
                    $User->setUsername($user . '@STOR.com');
                    $User->setEmail($user . '@STOR.com');
                    $User->setPlainPassword($user . '@STOR.com');
                    $User->setExternalId($user);
                    $User->setIPAddress($Request->getClientIp());
                    $UserManager->updateUser($User);
                }

                $Configuration = $this->getDoctrine()->getRepository('AppBundle:Configuration')->find($configuration);
                if(empty($Configuration))
                    throw new \Exception('Invalid configuration id');

                $settings = $Configuration->getAllConfigurationSettings();

                $Session->set('configuration', $configuration);

                $DBSession = new DBSession($User, $Configuration);
                $EntityManager = $this->getDoctrine()->getManager();
                $EntityManager->persist($DBSession);
                $EntityManager->flush();

                $Session->set('user_id', $user);
                $Session->set('SessionID', $DBSession->getId());

                $CustomContent = $this->getDoctrine()->getRepository('CYINTCustomContentBundle:CustomContent')->find(1);

                return $this->renderRoute(
                    'default/index.html.twig'
                    ,[
                        'CustomContent' => $CustomContent
                        ,'term' => $settings['search_term']
                        ,'visit' => $visit  
                        ,'settings' => $settings
                    ]
                    , $_render
                );

            }
            ,$this->generateUrl('root', ['_render'=>$_render])
			,$_render
		);
    }


    public function searchResultsAction(Request $Request, $page = 1, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $page, $_render)
            {           
                $settings = $this->viewParams['settings']; 
                $term = $settings['search_term'];
                $visit = $this->getCurrentVisit('results_visit', $Session);
                $configuration = $Session->get('configuration');

                $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findByFilter($term, $configuration,$page, $settings['srs_products_per_page']);                
                if(!empty($settings['srs_display_random']))
                    shuffle($products['result']);

                $index = $page * $settings['srs_products_per_page'];
                $ratings = $this->getDoctrine()->getRepository('AppBundle:Review')->findByProductAverages($products['result']);
                
                return $this->renderRoute(
                    'default/results.html.twig'
                    ,[
                        'products' => $products['result']
                        ,'total' => $products['count']
                        ,'items_per_page' => $settings['srs_products_per_page']
                        ,'index' => $index
                        ,'ratings' => $ratings
						,'page' => $page
                        ,'visit' => $visit
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('search_results', ['_render'=>$_render])
			,$_render
		);

    }

    public function productDetailsAction(Request $Request, $product, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $product)
            {    
                $visit = $this->getCurrentVisit('details_visit_' . $product, $Session);
                $settings = $Configuration->getAllConfigurationSettings();

                $Product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($product);                                  
                $ratings = $this->getDoctrine()->getRepository('AppBundle:Review')->findByProductAverages(new ArrayCollection([$Product]));
                $reviews = $this->getDoctrine()->getRepository('AppBundle:Review')->findBy(['Product'=>$Product], ['rating'=>'DESC']);
                $ratings_by_value = $this->getDoctrine()->getRepository('AppBundle:Review')->findByProductAndValue($Product);                

                return $this->renderRoute(
                    'default/details.html.twig'
                    ,[
                        'Product' => $Product
                        ,'ratings' => $ratings 
                        ,'settings' => $settings 
                        ,'ratings_by_value' => $ratings_by_value
                        ,'visit' => $visit
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
                $visit = $this->getCurrentVisit('reviews_visit_' . $product, $Session);
                $sort = empty($Session->get('sort')) ? 'e.created' : $Session->get('sort');
                $dir = empty($Session->get('sort')) ? 'DESC' : $Session->get('dir');

                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $sortdata = ParseData::setArray($form_data, 'sort', 'e.created:DESC');
                    $sortarray = explode(':', $sortdata);
                    $sort = $sortarray[0];
                    $dir = $sortarray[1];
                    $Session->set('sort', $sort);
                    $Session->set('dir', $dir);
                    $page = 1;
                }

                $Configuration = $this->loadConfiguration($Session->get('configuration'));
                $settings = $Configuration->getAllConfigurationSettings();
                $Product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($product);                                  
                $reviews = $this->getDoctrine()->getRepository('AppBundle:Review')->findByFilter(null,$Product->getId(), $page, $settings['crs_reviews_per_page'], $sort, $dir);

                return $this->renderRoute(
                    'default/reviews.html.twig'
                    ,[
                        'Product' => $Product                    
                        ,'reviews' => $reviews['result']
                        ,'review_count' => $reviews['count']
                        ,'page' => $page                   
                        ,'reviews_per_page' => $settings['crs_reviews_per_page']
                        ,'sort' => $sort . ':' . $dir
                        ,'visit' => $visit
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('product_reviews', ['_render'=>$_render, 'product'=>$product])
			,$_render
		);

    }

    public function checkoutAction(Request $Request, $_render = 'HTML', $product = null)
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $product)
            {    
                $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('');                              
                
                $User = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(['external_id'=>$Session->get('user_id')]);

                $visit = $this->getCurrentVisit('checkout_visit', $Session);

                $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));

                if($product)
                {
                    $Product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($product);
                    $DBSession->addProduct($Product);
                    $EntityManager = $this->getDoctrine()->getManager();
                    $EntityManager->persist($DBSession);
                    $EntityManager->flush();
                }
                
                $products = $DBSession->getProducts();

                if(!empty($User))
                    $User = array_pop($User);

                return $this->renderRoute(
                    'default/checkout.html.twig'
                    ,[
                        'products' => $products                
                        ,'items_per_page' => $settings['paginationitemsperpage']
                        ,'redirect_url' => $settings['formurl']                   
                        ,'User' => $User
                        ,'visit' => $visit
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

    private function loadConfiguration($configuration)
    {
        $Configuration = $this->getDoctrine()->getRepository('AppBundle:Configuration')->find($configuration);
        if(empty($Configuration))
            throw new \Exception('Invalid configuration id');

        return $Configuration;
    }

    private function getCurrentVisit($page_id, $Session)
    {
        $visit = empty($Session->get($page_id)) ? 0 : $Session->get($page_id);
        $visit++;
        $Session->set($page_id,$visit);
        return $visit;  
    }

    protected function controllerHook($Session, $messages) 
    {
        if(!$this->no_hook && !empty($Session->get('configuration')))
        {
            $Configuration = $this->loadConfiguration($Session->get('configuration'));
            $settings = $Configuration->getAllConfigurationSettings();
            $this->viewParams = [
                'settings' => $settings
                ,'term' => empty($settings['search_term']) ? '' : $settings['search_term']
            ];
        }        
    }
}
