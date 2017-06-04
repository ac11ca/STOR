<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use AppBundle\Entity\Subscription;
use AppBundle\Classes\ViewMessage;
use CYINT\ComponentsPHP\Classes\ParseData;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\RouteEntity;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\Machine;

class DefaultController extends PhotoATMMasterController
{
    /**
     * @Route("/", name="homepage")
     */
    public function rootAction(Request $Request, $machine)
    {
        return $this->redirect($this->generateUrl('index', ['machine' => $machine]));
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


    public function indexAction(Request $Request, $machine, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $machine)
            {       
                $this->initializeAccessToken();
                $TransactionService = $this->get('app.transaction');      
                $TransactionService->abandonTransaction();
                $Session->invalidate();
                $TransactionService->setMachineById($machine);
                $Machine = $TransactionService->getMachine();
                
                if(!in_array(Machine::STATUS_RUNNING, $Machine->getStatus()))
                    return $this->redirect($this->generateUrl('support_message'));

                $Transaction = $TransactionService->getCurrentTransaction();
                $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('checkout');
                $price = $settings['photo_fee'];

                return $this->renderRoute(
                    '/default/index.html.twig'
                    ,[
                        'machine' => $machine
                        ,'price' => $price
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('index', ['_render'=>$_render, 'machine'=>$machine])
			,$_render
		);
    }


    public function searchAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {    
                $InstagramService = $this->get('app.instagram');
                $TransactionService = $this->get('app.transaction');
                $term = null;
                $search_results = null;
                $CartService = $this->get('app.cart');

                $cart_data = $CartService->getCart();

                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $term = ParseData::setArray($form_data,'term',null);              
                    return $this->redirect($this->generateUrl('instagram_search', ['_render'=>$_render, 'term'=>$term ])); 
                }
                else
                {
                    $query_data = $Request->query->all();
                    $term = ParseData::setArray($query_data, 'term', null);
                }

                if(empty($term))
                {
                   $Machine = $TransactionService->getMachine();
                   return $this->redirect($this->generateUrl('root', ['machine'=>$Machine->getId()]));
                }

                $term = str_replace('#','',$term);
                $Session->set('term', $term);

                $access_token = $this->getAccessToken(); 
                $user_results = $InstagramService->searchForUser($term, $access_token);                    
                $tag_results  = $InstagramService->searchForTag($term, $access_token);                     

                $Session->set('user_list', $InstagramService->prepareUserResults($user_results));

                return $this->renderRoute(
                    '/default/search_tag.html.twig'
                    ,[
                        'term'=>$term
                        ,'user_results'=>$user_results
                        ,'tag_results'=>$tag_results
                        ,'cart_data'=>$cart_data
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('instagram_search', ['_render'=>$_render, 'term'=>null])
			,$_render
		);

    }

    public function igCallbackAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {    
                $InstagramService = $this->get('app.instagram');
                $form_data = $Request->query->all();               
                $code = ParseData::setArray($form_data, 'code', null);
                $mode = ParseData::setArray($form_data, 'mode', 'redirect');

                if(empty($code))
                   throw new \Exception('Invalid result');

                $Session->set('auth_code', $code);
                $result = $InstagramService->requestAccessTokenFromAuth($code);
                 
                if(empty($result['access_token']))
                    throw new \Exception('Invalid authorization code.');

                $Session->set('access_token', $result['access_token']);
                $Session->set('user', $result['user']);

                if($mode == 'redirect')
                    return $this->redirect($this->generateUrl('instagram_media', ['mode'=>'user', 'usertag'=>$result['user']['id']]));
                else
                    die($result['access_code']);
            }
            ,$this->generateUrl('instagram_callback', ['_render'=>$_render])
			,$_render
		);

    }


    public function igLoginRedirectAction(Request $Request, $_render = 'HTML')
    {
        $InstagramService = $this->get('app.instagram');
        return $this->redirect($InstagramService->getAuthenticationUrl());
    }

    public function igLoginAction(Request $Request, $_render = 'HTML', $user = null)
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $user)
            {    
                $InstagramService = $this->get('app.instagram');
                $term = empty($Session->get('term')) ? null : $Session->get('term');
                $user_data = $this->getUserDataFromSession($user);
                if(!is_array($user_data))
                    return $user_data; //Redirect               
 
                return $this->renderRoute(
                    '/default/ig_login.html.twig'
                    ,[
                        'user'=>$user
                        ,'term' => $term
                        ,'user_data'=>$user_data
                        ,'auth_url' => $InstagramService->getAuthenticationUrl()
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('instagram_login', ['_render'=>$_render, 'user'=>$user])
			,$_render
		);

    }

    public function igMediaAction(Request $Request, $_render = 'HTML', $mode = null, $usertag = null, $page = 1)
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $usertag, $mode, $page)
            {  
                $Session->set('media_mode', $mode);         
                $InstagramService = $this->get('app.instagram');
                $CartService = $this->get('app.cart');

                if(empty($usertag)) 
                    return $this->redirect($this->generateUrl('root'));

                $access_token = $this->getAccessToken(); 
               
                $media_results = $mode == 'user' ? $InstagramService->searchForUserMedia($usertag, $access_token) :  $InstagramService->searchForTagMedia($usertag, $access_token);

                if($mode == 'user' && $media_results['meta']['code'] == 400)
                    return $this->redirect($this->generateUrl('instagram_login', ['user'=>$usertag]));


                if($mode == 'user')            
                {
                    $Session->set('usertag', $usertag);
                    $user_data = $this->getUserDataFromSession($usertag);                   
                    if(!is_array($user_data))
                        return $user_data; //Redirect               

                }

                $cart_data = $CartService->getCart();
                 
                $term = $Session->get('term');                
        
                return $this->renderRoute(
                    '/default/ig_media.html.twig'
                    ,[
                        'tag'=> $mode == 'user' ? $user_data['username'] : $usertag
                        ,'user_data' => $mode == 'user' ? $user_data  : null
                        ,'media_results'=>$media_results
                        ,'cart_data' => $cart_data
                        ,'page' => 1                        
                        ,'type' => $mode
                        ,'term' => $term
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('instagram_media', ['_render'=>$_render, 'mode'=>'user', 'usertag'=>$usertag])
			,$_render
		);
    }

    public function mediaCartModifyAction(Request $Request, $_render = 'JSON', $mode = null)
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $mode)
            {   

                if($Request->isMethod('POST'))
                {
                    $CartService = $this->get('app.cart');
                    $TransactionService = $this->get('app.transaction');
                    $form_data = $Request->request->all();
                    $id = ParseData::setArray($form_data, 'id', null);
                    $thumbnail  = ParseData::setArray($form_data, 'thumbnail', null);
                    $standardres  = ParseData::setArray($form_data, 'standardres', null);
                    $lowres  = ParseData::setArray($form_data, 'lowres', null);
                    $data = ParseData::setArray($form_data, 'data', []);

                    if(empty($id))
                        throw new \Exception('Media id must be specified.');
 
                    $images = ['thumbnail' => $thumbnail, 'standardres'=>$standardres, 'lowres'=>$lowres];
                                                     
                    $cart_data = $CartService->modifyMediaQuantity($id, $mode, $images, $data);

                    $total = ceil($cart_data['total'] * $cart_data['price'] - $cart_data['discount']['amount']);
                    $total = $total > 0 ? $total : 0;

                    $TransactionService->updateTotal($total);

                }
                else
                    throw new \Exception('Access Denied', 403);

                return $this->renderRoute(
                    null
                    ,[
                        'cart_data' => $cart_data,
                        'media_id' => $id
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('media_cart_modify', ['_render'=>$_render, 'mode'=>$mode])
			,$_render
		);

    }

    public function mediaDeleteCartItemAction(Request $Request, $_render = 'JSON', $id = null, $slot = null)
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $id, $slot)
            {   

                $CartService = $this->get('app.cart');

                if(empty($id))
                    throw new \Exception('Media id must be specified.');
 
                if($slot === null)
                    throw new \Exception('Slot must be specified.');

                $cart_data = $CartService->deleteCartItem($id, $slot);

                return $this->renderRoute(
                    null
                    ,[
                        'cart_data' => $cart_data,
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('media_cart_modify', ['_render'=>$_render, 'id'=>$id, 'slot'=>$slot])
			,$_render
		);

    }


    public function cartImageAction(Request $Request, $_render = 'JSON', $target = null, $mode = null, $value = null, $slot = 0)
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render, $mode, $value, $target, $slot)
            {   
                if(empty($target))
                    throw new \Exception('Invalid target specified.');

                if(empty($slot))
                    $slot = 0;

                $CartService = $this->get('app.cart');

                switch($mode)
                {
                    case 'orientation':
                        $media =$CartService->modifyOrientation($value, $target, $slot);
                    break;
                    case 'rotate':
                        $media = $CartService->modifyRotation($value, $target, $slot);
                    break;
                    default:
                        $form_data = $Request->request->all();
                        $value = empty($form_data['value']) ? '' : $form_data['value'];
                        $media = $CartService->modifyData($value, $target, $slot, $mode);
                    break;
                }

                return $this->renderRoute(
                    null
                    ,[
                        'target' => $target
                        ,'media' => $media
                        ,'slot' => $slot
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('media_cart_modify', ['_render'=>$_render, 'mode'=>$mode])
			,$_render
		);

    }



    public function cartViewAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
                $CartService = $this->get('app.cart');
                $cart_data = $CartService->getCart();
                $term = empty($Session->get('term')) ? null : $Session->get('term');
                $usertag = $Session->get('usertag');
                $mode = $Session->get('media_mode');
                return $this->renderRoute(
                    '/default/cart_view.html.twig'
                    ,[
                        'cart_data' => $cart_data
                        ,'term' => $term
                        ,'usertag' =>$usertag
                        ,'mode' => $mode
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('media_cart_view', ['_render'=>$_render])
			,$_render
		);

    }

    public function cartCheckoutAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
                $CartService = $this->get('app.cart');
                $TransactionService = $this->get('app.transaction');
                $cart_data = $CartService->getCart();
                $term = empty($Session->get('term')) ? null : $Session->get('term');
                $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('checkout');               
                $helpline = $settings['helpline'];
                $email_settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('smtp');
                $Transaction = $TransactionService->getCurrentTransaction();
                $discount_failed = false;
                $machine = $Session->get('Machine');
                $TransactionService->setCartData($cart_data);

                if(empty($Transaction))
                {
                    $Machine = $this->getDoctrine()->getRepository('AppBundle:Machine')->find($machine);
                    return $this->redirect($this->generateUrl('index', ['machine'=>$Machine->getId()]));
                }

                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $discount = ParseData::setArray($form_data, 'promo', null);
                    if(!empty($discount))
                    {
                        $cart_data = $CartService->applyDiscountCode($discount);              
                    }
                }

                if($cart_data['total'] * $cart_data['price'] - $cart_data['discount']['amount'] <= 0)
                {               
                    if($Transaction->getChangeAmount() > 0)
                        $Transaction = $TransactionService->markDispensingChange();
                    else
                    {
                        $Transaction = $TransactionService->markPaid();                            
                        return $this->redirect($this->generateUrl('media_cart_print'));
                    }
                }

                return $this->renderRoute(
                    '/default/cart_checkout.html.twig'
                    ,[
                        'cart_data' => $cart_data
                        ,'discount_failed' => $discount_failed
                        ,'Transaction' => $Transaction
                        ,'total_inserted' => 0
                        ,'term' => $term
                        ,'helpline' => $helpline
                        ,'email_settings' => $email_settings
                        ,'machine' => $machine
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('media_cart_checkout', ['_render'=>$_render])
			,$_render
		);

    }


    public function cartPrintAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
                $CartService = $this->get('app.cart');
                $TransactionService = $this->get('app.transaction');
                $machine = $Session->get('Machine');

                $isPaid = $TransactionService->isTransactionPaid();

                if(!$isPaid)               
                {
                    $Machine = $TransactionService->getMachine();
                    return $this->redirect($this->generateUrl('cart_checkout'));    
                }

                //$Transaction = $TransactionService->markPrinted();                            

                $cart_data = $CartService->getCart();
                $mediaid = null;
                if(count($cart_data['media'] > 0))
                {
                    foreach($cart_data['media'] as $id=>$media)
                    {
                        if($media['quantity'] > 0 )
                        {
                            $mediaid = $id;
                            foreach($media['images'] as $slot=>$mediaimage)
                            {
                                $image = $mediaimage;
                                $current_slot = $slot;
                                break;
                            }
                            break;
                        }
                    }
                }

                $time = ceil($cart_data['total'] / 2) * 15000;
                
                $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('checkout');               
                $helpline = $settings['helpline'];
                $email_settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('smtp');

                $term = empty($Session->get('term')) ? null : $Session->get('term');
                return $this->renderRoute(
                    '/default/cart_print.html.twig'
                    ,[
                        'cart_data' => $cart_data
                        ,'current_image'=>$image
                        ,'current_slot' => $current_slot
                        ,'mediaid' =>$mediaid
                        ,'helpline' => $helpline
                        ,'email_settings' => $email_settings
                        ,'time' => $time
                        ,'machine' => $machine
                    ]
                    , $_render
                );
            }
            ,$this->generateUrl('media_cart_checkout', ['_render'=>$_render])
			,$_render
		);

    }

    public function cartClearAction(Request $Request, $_render = 'HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
                $CartService = $this->get('app.cart');
                $TransactionService = $this->get('app.transaction');
                $machine_id = $Session->get('Machine');
                $Machine = $this->getDoctrine()->getRepository('AppBundle:Machine')->find($machine_id);
                $cart_data = $CartService->initializeCart();
                return $this->redirect($this->generateUrl('index', ['machine'=>$Machine->getId()]));                
            }
            ,$this->generateUrl('media_cart_clear', ['_render'=>$_render])
			,$_render
		);

    }

    public function credentialsAction(Request $Request, $_render = 'JSON')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $token = $form_data['authentication'];                    
                    $credentials = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('credentials');
                    if($credentials['hopper_authtoken'] != $token)
                        throw new \Exception('Access Denied', 403);
                    
                    return $this->renderRoute(
                        null 
                        ,[
                            'credentials'=>[
                                'server' => $credentials['server']
                                ,'username' => $credentials['username']
                                ,'password' => $credentials['password']
                                ,'database' => $credentials['database']
                            ]
                        ]
                        , $_render
                    );
                }
    
                throw new \Exception('Access Denied', 403);
            }
            ,null
			,$_render
		);

    }

    public function pollTransactionAction(Request $Request, $_render = 'JSON')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
                $TransactionService = $this->get('app.transaction');

                if($Request->isMethod('POST'))
                {
                    $credentials = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('credentials');
                    $form_data = $Request->request->all();

                    $token = ParseData::setArray($form_data, 'authentication', null);
                    $machine = ParseData::setArray($form_data, 'machine', null);
                    if($credentials['hopper_authtoken'] != $token)
                        throw new \Exception('Access Denied', 403);

                    if(empty($machine))
                        throw new \Exception('Machine not set');
    
                    $TransactionService->setMachineById($machine);
                }

                $Transaction = $TransactionService->getCurrentTransaction();                   

                return $this->renderRoute(
                    null 
                    ,[
                        'Transaction'=>$Transaction
                    ]
                    , $_render
                );
            }
            ,null
			,$_render
		);

    }

    public function reportErrorAction(Request $Request, $_render = 'JSON')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {   
               
                if($Request->isMethod('POST'))
                {
                    $EmailService = $this->get('app.emailer');
                    $credentials = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('credentials');
                    $form_data = $Request->request->all();

                    $token = ParseData::setArray($form_data, 'authentication', null);
                    $machine = ParseData::setArray($form_data, 'machine', null);
                    $error = ParseData::setArray($form_data,'error', null);
                    $transaction = ParseData::setArray($form_data, 'transaction', null);

                    if($credentials['hopper_authtoken'] != $token)
                        throw new \Exception('Access Denied', 403);

                    if(empty($machine))
                        throw new \Exception('Machine not set');

                    if(empty($transaction))
                        throw new \Exception('Transaction not set');
    
                    $Machine = $this->getDoctrine()->getRepository('AppBundle:Machine')->find($machine);
                    $Transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->find($transaction);
                    if(empty($Machine) || empty($Transaction))
                        throw new \Exception('Missing entity');

                    $EmailService->sendErrorNotification($Transaction, $Machine, $error);
                }
                else
                    throw new \Exception('Access Denied.', 403);

                return $this->renderRoute(
                    null 
                    ,[
                        'sent'=>true
                    ]
                    , $_render
                );
            }
            ,null
			,$_render
		);

    }



   public function cartBarAction(Request $Request, $_render = 'HTML', $nouser =false )
   {
        $usertag = null;
        $user_data = null;
        $CartService = $this->get('app.cart');
        $Session = $this->get('session');
        $cart_data = $CartService->getCart();
        $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('checkout');
        $usertag =  $Session->get('usertag');

        if(!empty($usertag))
            $user_data = $this->getUserDataFromSession($usertag);

        return $this->render(
            'default/components/cartbar.html.twig'
            , [                
                'cart_data' => $cart_data
                ,'user_data' => $user_data,
                'nouser' => $nouser
            ]
        );
   }

   public function priceBarAction(Request $Request, $_render = 'HTML', $next = null, $back = null)
   {
        $CartService = $this->get('app.cart');
        $Session = $this->get('session');
        $cart_data = $CartService->getCart();
        $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('pricing');
        $pricing_array = empty($settings['array']) ? null : json_decode($settings['array'], true);

        if(empty($pricing_array))                   
            $pricing_array = [0=>['denomination'=>0,'pricing'=>1]];        

        return $this->render(
            'default/components/pricebar-data.html.twig'
            , [                
                'pricing' => $pricing_array
                ,'next' => $next
                ,'back' => $back
            ]
        );
   }

    private function getUserDataFromSession($user_id)
    {
        $Session = $this->get('session');
        $user_list = $Session->get('user_list');
            
        if(empty($user_list))
            return $this->redirect($this->generateUrl('root'));

        if(empty($user_list[$user_id]))
            return $this->redirect($this->generateUrl('root'));

        return $user_list[$user_id];
    }

    private function getAccessToken()
    {
        $Session = $this->get('session');
        $access_token = $Session->get('access_token');
        if(empty($access_token))
            $access_token = $this->initializeAccessToken();
        return $access_token;
    }

    private function initializeAccessToken()
    {
        $Session = $this->get('session');
        $InstagramService = $this->get('app.instagram');
        $access_token = $InstagramService->getBaseAccessToken();                   
        $Session->set('access_token',$access_token);
        return $access_token;
    }

    public function supportMessageAction(Request $Request, $_render = 'HTML')
    {
       $settings = $this->getDoctrine()->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('checkout');
       $helpline = $settings['helpline'];
       $machine = $this->get('Session')->get('Machine');
       return $this->renderRoute(
            '/default/maintenance.html.twig'
            ,[
                'helpline'=>$helpline,
                'machine'=>$machine
            ]
            , $_render
        );
    }

    public function preRenderRoute()
    {
        if(get_class($this) == 'AppBundle\Controller\DefaultController')
        {
            $Session = $this->get('session');
            $Request = $this->get('request_stack')->getCurrentRequest();
            $route = $Request->get('_route');
            if($Session->get('Machine') == null && ($route != 'routes' && $route != 'credentials' && $route != 'report_error'))
                throw new \Exception('Machine id not specified');
        }
    }

}
