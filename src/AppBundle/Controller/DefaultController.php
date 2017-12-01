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
         $this->no_hook = true;
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
                    $visit = $this->getCurrentVisit('root_visit', $Session, $Request);
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
                $visit = $this->getCurrentVisit('index_visit', $Session, $Request);
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
                $visit = $this->getCurrentVisit('results_visit', $Session, $Request);
                $configuration = $Session->get('configuration');

                $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findByFilter($term, $configuration,$page, $settings['srs_products_per_page']);                
                if(!empty($settings['srs_display_random']))
				{
					$seed = $this->getSeed($Session);
                    $this->fisherYatesShuffle($products['result'], $seed);
				}

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
                $visit = $this->getCurrentVisit('details_visit_' . $product, $Session, $Request);

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
                // Commenting this line because visitor count will increase even if user have filter or short actions.
                //$visit = $this->getCurrentVisit('reviews_visit_' . $product, $Session, $Request);
                $sort = empty($Session->get('sort')) ? 'e.created' : $Session->get('sort');
                $dir = empty($Session->get('sort')) ? 'DESC' : $Session->get('dir');
                 //*push
                $sort="";
                $dir="";
                //push*
                $filter = empty($Session->get('filter')) ? null : $Session->get('filter');
                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $sortdata = ParseData::setArray($form_data, 'sort', 'e.created:DESC');
                    $filter = ParseData::setArray($form_data, 'filter', null);
                    $sortarray = explode(':', $sortdata);
                    $sort = $sortarray[0];
                    $dir = $sortarray[1];
                    //*push
                    if($form_data["sort"]=="")
                    {
                        $sort = "";
                        $dir = "";
                    }
                    //push*
                    if($filter != null && $filter != $Session->get('filter'))
                    {                                           
                        $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));
                        $Analytic = new Analytics($DBSession, 'click', 'Visit: ' . $Session->get('reviews_visit'), 'Product_' . $product . '_FilterBy' . $filter . 'Star');
                        $EntityManager = $this->getDoctrine()->getManager();
                        $EntityManager->persist($Analytic);
                        $EntityManager->flush();
                    }
                    else if($filter == null && !empty($Session->get('filter')))
                    {
                        $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));
                        $Analytic = new Analytics($DBSession, 'click', 'Visit: ' . $Session->get('reviews_visit'), 'Product_' . $product . '_FilterByAllStars');
                        $EntityManager = $this->getDoctrine()->getManager();
                        $EntityManager->persist($Analytic);
                        $EntityManager->flush();                       
                    }
  
                    if($sort !='e.created' || $sort == 'e.created' && $Session->get('sort') != 'e.created')
                    {
                        $sortname = 'Date';
                        $sortdir = empty($dir) ? 'Descending' : $dir == 'ASC' ? 'Ascending' : 'Descending';
                        switch($sort)
                        {
                            case 'e.created':
                                $sortname = 'Date';
                            break;

                            case 'e.rating':
                                $sortname = 'Rating';
                            break;
                        
                            case 'e.help_score':
                                $sortname = 'Helpfulness';
                            break;
                        }

                        $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));
                       //*push
                        if($sort!="")
                        {
                        $Analytic = new Analytics($DBSession, 'click', 'Visit: ' . $Session->get('reviews_visit'), 'Product_' . $product . '_SortBy' . $sortname . $sortdir);
                        $EntityManager = $this->getDoctrine()->getManager();
                        $EntityManager->persist($Analytic);
                        $EntityManager->flush();   
                        }                    
                        //push*                   

                    } 

                    $Session->set('sort', $sort);
                    $Session->set('dir', $dir);
                    $Session->set('filter', $filter);
                    $page = 1;
                }
                else
                {
                    $query_data = $Request->query->all();
                    $filter = ParseData::setArray($query_data, 'filter', null);
                    $Session->set('filter', $filter);
                    // Push visit here because it should increase the visit when it just load.
                    $visit = $this->getCurrentVisit('reviews_visit_' . $product, $Session, $Request);
                }

                $Configuration = $this->loadConfiguration($Session->get('configuration'));
                $settings = $Configuration->getAllConfigurationSettings();
                $Product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($product);                                  
                $reviews = $this->getDoctrine()->getRepository('AppBundle:Review')->findByFilter($filter,$Product->getId(), $page, $settings['crs_reviews_per_page'], $sort, $dir);

                return $this->renderRoute(
                    'default/reviews.html.twig'
                    ,[
                        'Product' => $Product                    
                        ,'reviews' => $reviews['result']
                        ,'review_count' => $reviews['count']
                        ,'page' => $page                   
                        ,'reviews_per_page' => $settings['crs_reviews_per_page']
                        ,'sort' => $sort . ':' . $dir
                        ,'filter' => $filter
                        ,'visit' =>  $Session->get('reviews_visit') //$visit
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
                $visit = $this->getCurrentVisit('checkout_visit', $Session, $Request);

                $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));

                if(!empty($product))
                {
                    $Product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($product);
                    $DBSession->addProduct($Product);
                    $Product->addSession($DBSession);
                    $EntityManager = $this->getDoctrine()->getManager();
                    $EntityManager->persist($DBSession);
                    $EntityManager->persist($Product);
                    $EntityManager->flush();
                }
                
                $products = $DBSession->getProducts();

                if(!empty($User))
                    $User = array_pop($User);

                return $this->renderRoute(
                    'default/checkout.html.twig'
                    ,[
                        'products' => $products                
                        ,'items_per_page' => $settings['paginatoritemsperpage']
                        ,'redirect_url' => $settings['formurl']
                        ,'configuration_id' => $Session->get('configuration') 
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

    public function purchaseAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
                if(!$Request->isMethod("POST"))
                    throw new \Exception('Access Denied');

                $form_data = $Request->request->all();
                $product = $form_data['product'];
                $timestamp = $form_data['timestamp'];

                $duration = time() - $timestamp;
                $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));
                $Analytic = new Analytics($DBSession, 'click', 'Visit: ' . $Session->get('checkout_visit'), 'Product_' . $product . '_Purchase');
                $EntityManager = $this->getDoctrine()->getManager();
                $EntityManager->persist($Analytic);
                $EntityManager->flush();
                $Analytic = new Analytics($DBSession, 'duration', 'Visit: ' . $Session->get('checkout_visit'), 'PS_Duration');
                $Analytic->setTime($duration);
                $EntityManager = $this->getDoctrine()->getManager();
                $EntityManager->persist($Analytic);
                $EntityManager->flush();
            
                $Analytic = new Analytics($DBSession, 'unload', 'Visit: ' . $Session->get('checkout_visit'), 'PS_Time_End');
                $EntityManager = $this->getDoctrine()->getManager();
                $EntityManager->persist($Analytic);
                $EntityManager->flush();

                $configuration = $Session->get('configuration');
                $user_id = $Session->get('user_id'); 
                $Session->clear();          
		        $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('');             
                return $this->redirect($settings['formurl'] . '?purchase=1&user=' . $user_id . '&configuration=' . $configuration . '&product=' . $product);
            }
            ,$this->generateUrl('root', ['_render'=>$_render])
			,$_render
		);

    }

    public function abandonAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {  
                $form_data = $Request->query->all();
                $category = $form_data['category'];
                $timestamp = $form_data['timestamp'];
                $duration = time() - $timestamp;

                $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));
                $Analytic = new Analytics($DBSession, 'duration', 'Visit: ' . $Session->get('checkout_visit'), $category . '_Duration');
                $Analytic->setTime($duration);
                $EntityManager = $this->getDoctrine()->getManager();
                $EntityManager->persist($Analytic);
                $EntityManager->flush();
 
                $Analytic = new Analytics($DBSession, 'unload', 'Visit: ' . $Session->get('checkout_visit'), $category . '_Time_End');
                $EntityManager = $this->getDoctrine()->getManager();
                $EntityManager->persist($Analytic);
                $EntityManager->flush();
               
                $configuration = $Session->get('configuration');
                $user_id = $Session->get('user_id'); 
                $Session->clear();
                $messages[] = ViewMessage::constructMessage('Your cart has been abandoned.', 'danger', null);        
          
		        $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('');  
                return $this->redirect($settings['formurl'] . '?abandon=1&user=' . $user_id . '&configuration=' . $configuration);
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
                    $duration = ParseData::setArray($form_data, 'duration', null);

                    if(empty($type) || empty($label))
                        throw new \Exception('Label and category must be specified');

                    $DBSession = $this->getDoctrine()->getRepository('AppBundle:Session')->find($Session->get('SessionID'));
                    $Analytic = new Analytics($DBSession, $type, $label, $category);
                    if($type == 'duration')
                        $Analytic->setTime($duration);

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

    private function getCurrentVisit($page_id, $Session, $Request)
    {
        $visit = empty($Session->get($page_id)) ? 0 : $Session->get($page_id);
        if(!$Request->isMethod('POST'))
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

    protected function getSeed($Session)
    {
        $seed = empty($Session->get('seed')) ? null : $Session->get('seed');
        if(!empty($seed))
            return $seed;
		list($usec, $sec) = explode(' ', microtime());
		$seed =  $sec + $usec * 1000000;        
		$Session->set('seed', $seed);
		return $seed;
    }

	protected function fisherYatesShuffle(&$items, $seed)
	{
		@mt_srand($seed);
		$items = array_values($items);
		for ($i = count($items) - 1; $i > 0; $i--)
		{
			$j = @mt_rand(0, $i);
			$tmp = $items[$i];
			$items[$i] = $items[$j];
			$items[$j] = $tmp;
		}
	}
}
