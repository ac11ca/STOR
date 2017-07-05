<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CYINT\ComponentsPHP\Classes\ViewMessage;
use CYINT\ComponentsPHP\Classes\ParseData;
use CYINT\ComponentsPHP\Bundles\SettingsBundle\Entity\Setting;
use AppBundle\Entity\Transaction; 
use Doctrine\DBAL\Exception\UniqueConstraintViolationException; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Factory\AnalyticsFactory;
use AppBundle\Entity\Configuration;
use AppBundle\Entity\ConfigurationSetting;

class AdminController extends ApplicationMasterController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $Request)
    {
        // replace this example code with whatever you need
        return $this->render('admin/index.html.twig');
    }


    public function userLoginAction(Request $Request, $id)
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request, $id)
            {

                $User = $this->getUser();
                if($User->getRole() != 'ROLE_ADMIN')
                {
                    $messages[] = ViewMessage::constructMessage('Access Denied', 'danger');
                    $Session->set('messages', $messages);
                    return $this->redirect($this->generateUrl('admin_universal_list', ['reponame'=>'User']));
                }

                $TargetUser = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

                if(empty($TargetUser))
                {
                    $messages[] = ViewMessage::constructMessage('Could not find user in system', 'danger');
                    $Session->set('messages', $messages);
                    return $this->redirect($this->generateUrl('admin_universal_list', ['reponame'=>'User']));
                }

                // Here, "public" is the name of the firewall in your security.yml
                $token = new UsernamePasswordToken($TargetUser, $TargetUser->getPassword(), "main", $TargetUser->getRoles());

                // For older versions of Symfony, use security.context here
                $this->get("security.token_storage")->setToken($token);

                // Fire the login event
                // Logging the user in above the way we do it doesn't do this automatically
                $event = new InteractiveLoginEvent($Request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                return $this->redirect($this->generateUrl('cart'));
            },
            $this->generateUrl('admin_user_login', ['id'=>$id])
        );
    }


    public function reportsTransactionAction(Request $Request, $machine = null)
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request, $machine)
            {
                $now = time();
                $month = date('m');
                $year = date('Y');
                $endmonth = date('m');
                $endyear = date('Y');
                $endmonth = ($endmonth + 1) > 12 ? 1 : ($endmonth + 1);
                $endyear = $endmonth == 1 ?  $endyear + 1 : $endyear;
                
                $current_year = $year;
                $current_endyear = $endyear;
                $now = strtotime($month . '/1/' . $year);

                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();

                    $month = empty($form_data['month']) ? $month : $form_data['month'];
                    $year = empty($form_data['year']) ? $year : $form_data['year'];
                    $endmonth = empty($form_data['endmonth']) ? $endmonth : $form_data['endmonth'];
                    $endyear = empty($form_data['endyear']) ? $endyear : $form_data['endyear'];
                }

                $last_day = date('t', $now);
                $start_date_string = $month .'/1/' . $year . ' 00:00';
                $start_timestamp = strtotime($start_date_string);
                $end_date_string = $endmonth .'/1/'. $year . ' 00:00';
                $end_timestamp = strtotime($end_date_string);

                if($start_timestamp >= $end_timestamp)
                    throw new \Exception('The start date must be less than the end date for this report');

                $Repository = $this->getDoctrine()->getRepository('AppBundle:Transaction');
                $transactions = $Repository->findByDate($start_timestamp, $end_timestamp, $machine);

                $totals = ['master'=>[]];     
                $totals['master']['amount']['total'] = 0;
                $totals['master']['amount']['pending'] = 0;
                $totals['master']['amount']['completed'] = 0;
                $totals['master']['amount']['abandoned'] = 0;
                $totals['master']['amount']['refunded'] = 0;
                $totals['master']['count']['total'] = 0;
                $totals['master']['count']['pending'] = 0;
                $totals['master']['count']['completed'] = 0;
                $totals['master']['count']['abandoned'] = 0;
                $totals['master']['count']['refunded'] = 0;
                $totals['summary']['amount']['revenue'] = 0;
     
                $bills = [];
                $billsRemoved = [];
                $billsRemaining = [];

                foreach($transactions as $Transaction)
                {        
                    $amount = $Transaction->getTotal();
                    $status = $Transaction->getStatus();  
                    if($status != Transaction::STATUS_ADMINISTRATIVE)
                    {                            
                        if($status != Transaction::STATUS_REFUND_ISSUED)                        
                            $totals['master']['amount']['total'] += $amount;                       
                        else
                            $totals['master']['amount']['total'] -= $amount;                       

                        $totals['master']['count']['total']  ++;
                    }       

                    switch($status)
                    {
                        case Transaction::STATUS_ABANDONED:
                            $totals['master']['amount']['abandoned'] += $amount;
                            $totals['master']['count']['abandoned']  ++;
                        break;

                        case Transaction::STATUS_PENDING:
                        case Transaction::STATUS_PAID:
                        case Transaction::STATUS_DISPENSING_CHANGE:
                            $totals['master']['amount']['pending'] += $amount;
                            $totals['master']['count']['pending']  ++;
                        break;

                        case Transaction::STATUS_PRINTED:
                            $totals['master']['amount']['completed'] += $amount;
                            $totals['master']['count']['completed']  ++;
                        break;

                        case Transaction::STATUS_REFUND_ISSUED:
                            $totals['master']['amount']['refunded'] += $amount;
                            $totals['master']['count']['refunded']  ++;
                        break;
                    }

                    if($status != Transaction::STATUS_ADMINISTRATIVE)
                    {
                            
                        $billsInserted = $Transaction->getBillsInserted();             

                        if(!empty($billsInserted->bills))
                        {
                            foreach($billsInserted->bills as $key=>$bill)   
                            {
                                if(empty($bills[$bill->denomination . ' &euro;']))
                                    $bills[$bill->denomination . ' &euro;'] = 0;
                                
                                $bills[$bill->denomination . ' &euro;'] += $bill->quantity;
                            }        
                        }
                    }
                    else
                    {
                        $billsInserted = $Transaction->getBillsInserted();             

                        if(!empty($billsInserted->bills))
                        {
                            foreach($billsInserted->bills as $key=>$bill)   
                            {
                                if(empty($billsRemoved[$bill->denomination . ' &euro;']))
                                    $billsRemoved[$bill->denomination . ' &euro;'] = 0;
                                
                                $billsRemoved[$bill->denomination . ' &euro;'] -= $bill->quantity;
                            }        
                        }
                    }
                }
  
                if(is_array($bills) && is_array($billsRemoved))
                {  
                    $billsRemaining = $bills;
                        
                    foreach($billsRemoved as $denomination => $removed)
                    {
                        $billsRemaining[$denomination] += $removed; 
                    }
                }
                else
                {
                    if(is_array($bills))
                        $billsRemaining = $bills;
                    else
                        $billsRemaining = null;
                }

                $totals['master']['amount']['revenue'] = $totals['master']['amount']['completed'] - $totals['master']['amount']['refunded'];
             
                return $this->render("admin/reports/transaction.html.twig", [
                    'month' => $month,
                    'year' => $year,
                    'endmonth' => $endmonth,
                    'endyear' => $endyear,                   
                    'current_year' => $current_year,
                    'current_endyear' => $current_endyear,
                    'transactions' => $transactions,
                    'bills' => $bills,
                    'billsRemoved' => $billsRemoved,
                    'billsRemaining' => $billsRemaining,
                    'machine' => $machine,
                    'totals' => $totals
                ]); 

            },
            $this->generateUrl('admin_reports_transaction', ['machine'=>$machine])
        );
    }

    public function collectionAction(Request $Request, $machine = null)
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request, $machine)
            {              
                $Machine = $this->getDoctrine()->getRepository('AppBundle:Machine')->find($machine);                
                if(empty($Machine))
                    throw new \Exception('Invalid machine ID');

                $transactions = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findByDate(null, null, $machine);

                $bills = [];
                $billsRemoved = [];
                $billsRemaining = ['bills' => []];

                foreach($transactions as $Transaction)
                {        
                    $amount = $Transaction->getTotal();
                    $status = $Transaction->getStatus();  

                    if($status != Transaction::STATUS_ADMINISTRATIVE)
                    {
                            
                        $billsInserted = $Transaction->getBillsInserted();             

                        if(!empty($billsInserted->bills))
                        {
                            foreach($billsInserted->bills as $key=>$bill)   
                            {
                                if(empty($bills[$bill->denomination . ' &euro;']))
                                    $bills[$bill->denomination . ' &euro;'] = 0;
                                
                                $bills[$bill->denomination . ' &euro;'] += $bill->quantity;
                            }        
                        }
                    }
                    else
                    {
                        $billsInserted = $Transaction->getBillsInserted();             

                        if(!empty($billsInserted->bills))
                        {
                            foreach($billsInserted->bills as $key=>$bill)   
                            {
                                if(empty($billsRemoved[$bill->denomination . ' &euro;']))
                                    $billsRemoved[$bill->denomination . ' &euro;'] = 0;
                                
                                $billsRemoved[$bill->denomination . ' &euro;'] -= $bill->quantity;
                            }        
                        }
                    }
                }

                if(is_array($bills) && is_array($billsRemoved))
                {  
                    $billsRemaining = $bills;
                        
                    foreach($billsRemoved as $denomination => $removed)
                    {
                        $billsRemaining[$denomination] += $removed; 
                    }
                }
                else
                {
                    if(is_array($bills))
                        $billsRemaining = $bills;
                    else
                        $billsRemaining = null;
                }

                if($Request->isMethod('POST'))
                {
                    $EntityManager = $this->getDoctrine()->getManager();
                    $form_data = $Request->request->all();
                    $bills = [];
                    $bills['bills'] = [];                          

                    if(!empty($form_data['bills']))
                    {
                        foreach($form_data['bills'] as $key=>$quantity)
                        {
                            $bills['bills'][] = ['denomination'=>str_replace(' &euro;','', $key), 'quantity'=>$quantity];
                        }
                    }

                    $Transaction = new Transaction($Machine);
                    $Transaction->setStatus(Transaction::STATUS_ADMINISTRATIVE);
                    $Transaction->setBillsInserted($bills);
                    $EntityManager->persist($Transaction);
                    $EntityManager->flush();

                    $messages[] = ViewMessage::constructMessage('Collection recorded as transaction: ' . $Transaction->getId(), 'success');
                    $Session->set('messages', $messages);
                    return $this->redirect($this->generateUrl('admin_collection', ['machine'=>$machine]));
                }
                                
                return $this->render("admin/Machine/collection.html.twig", [
                    'Machine' => $Machine
                    ,'machine' => $machine
                    ,'bills' => $billsRemaining
                ]); 
            },
            $this->generateUrl('admin_collection', ['machine'=>$machine])
        );
    }




    public function reportsMachineAction(Request $Request)
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request)
            {  
                $machines = $this->getDoctrine()->getRepository('AppBundle:Machine')->findAll(); 

                $totals = [];
                $totals['setup'] = 0;
                $totals['running'] = 0;
                $totals['nochange'] = 0;
                $totals['collectorfull'] = 0;
                $totals['nopaper'] = 0;
                $totals['paperjam'] = 0;
                $totals['failedcheckin'] = 0;

                if(!empty($machines))
                {
                    foreach($machines as $Machine)
                    {
                        $statuses = $Machine->getStatus();
                        if(!empty($statuses))
                        {
                            foreach($statuses as $status)
                            {
                                switch($status)
                                {
                                    case 0:
                                        $totals['setup']++;
                                    break;
                                    case 1:
                                        $totals['nochange']++;
                                    break;
                                    case 2:
                                        $totals['collectorfull']++;
                                    break;
                                    case 3:
                                        $totals['failedcheckin']++;
                                    break;
                                    case 4:
                                        $totals['nopaper']++;
                                    break;
                                    case 5:
                                        $totals['paperjam']++;
                                    break;
                                    case 6:
                                        $totals['decomissioned']++;
                                    break;
                                    case 7:
                                        $totals['running']++;
                                    break;                               
                                }
                            }
                        }    
                    }
                }

                return $this->render("admin/reports/machine.html.twig", [
                    'machines' => $machines
                    ,'totals' => $totals
                ]); 
            },
            $this->generateUrl('admin_reports_machine', [])
        );
    }


    public function messagesAction(Request $Request)
    {
        $Session = $this->get('session');
        $messages = empty($Session->get('messages')) ? [] : $Session->get('messages');
        $Session->set('messages', null);
        return $this->render('admin/messages.html.twig', ['messages'=>$messages]);
    }


    public function cacheAction(Request $Request)
    {
        exec('/var/www/photo-atm-web-application/console cache:clear', $response);
 
        return $this->render('admin/cache.html.twig', [
            'response' => $response
        ]); 
       
    }


    public function loginAction(Request $Request, $_render = 'HTML')
    {
        return $this->handleErrors(
            function ($Session, $messages) use($Request) 
            {
                $username = empty($Session->get('username')) ? '' : $Session->get('username');
                $Session->set('username',null);

                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $username = ParseData::setArray($form_data,'username','');
                    $password = ParseData::setArray($form_data,'password','');
                    $Session->set('username', $username);                                                    
  					$result = $this->authenticationHelper($username, $password);
                    $User = $result['User'];
                    $event = new InteractiveLoginEvent($Request, $result['token']);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                    $Session->set('username', null);
                     
                    if($User->getRole() == 'ROLE_ADMIN')
                    {
                        return $this->redirect($this->generateUrl('admin_index'));                                                            
                    }
                    else
                        throw new \Exception('Invalid user role');
                }             

                return $this->render("admin/login.html.twig", [
                    'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
                    'username' => $username                 
                ]);

            }
            ,$this->generateUrl('login')
        );
    }

    public function pricingAction(Request $Request)
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request)
            {
                $Doctrine = $this->getDoctrine();
                $EntityManager = $Doctrine->getManager();
                $settings = $Doctrine->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('pricing');
                $pricing_array = [0=>['denomination'=>0, 'quantity'=>2]];
              
                if(!empty($settings['array']))            
                    $pricing_array = json_decode($settings['array'], true);


                $fields = [
                    'PriceBreak' => [
                        'value' => $pricing_array
                        ,'default' => null
                        ,'type' => 'custom'
                        ,'label' => 'Price breaks'
                        ,'validation'=>[]         
                        ,'template'=>'priceBreak'
                    ]
                ];


                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();                                     
                    if(empty($form_data) || empty($form_data['PriceBreak']))
                        throw new \Exception('You must specify at least 1 price break');

                    $json = json_encode($form_data['PriceBreak']);

                    $Setting = $Doctrine->getRepository('CYINTSettingsBundle:Setting')->findOneBy(['settingKey'=>'pricing_array']);
                    if(empty($Setting))
                    {
                        $Setting = new Setting();
                        $Setting->setSettingKey('pricing_array');                    
                    }

                    $Setting->setValue($json);
                    $EntityManager->persist($Setting);
                    $EntityManager->flush();
                    $messages[] = ViewMessage::constructMessage('Pricing updated.', 'success');
                    $Session->set('messages', $messages);
                    return $this->redirect($this->generateUrl('admin_pricing'));
                }
                

                return $this->render("admin/settings/pricing.html.twig", [
                    'settings'=>$settings
                    ,'fields'=>$fields
                    ,'create' => false
                    ,'reponame'=>''
                    ,'parentid'=>0
                ]);
            },
            $this->generateUrl('admin_pricing')
        );
    }

    public function reportsAction(Request $Request, $_render = 'HTML')
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {  
                $Doctrine = $this->getDoctrine();    
                $dimension_mappings =  AnalyticsFactory::getDimensionMappings();                        
                $conditional_mappings =  AnalyticsFactory::getConditionalMappings();                        
                $x_mappings = AnalyticsFactory::getXOptions();                        
                $y_mappings = AnalyticsFactory::getYOptions();                        

                return $this->renderRoute("admin/reports/generate.html.twig", [
                    'dimension_mappings' => $dimension_mappings
                    ,'conditional_mappings' => $conditional_mappings
                    ,'x_mappings' => $x_mappings
                    ,'y_mappings' => $y_mappings
                ], $_render);               
            }
        ,
        $this->generateUrl('admin_reports')
        );
    }


    public function reportViewAction(Request $Request, $_render='HTML')
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {                               

                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $from = ParseData::setArray($form_data, 'from', null);
                    $to = ParseData::setArray($form_data, 'to', null);
                    $y = ParseData::setArray($form_data, 'y', null);
                    $x = ParseData::setArray($form_data, 'x', null);
                    $dimension = ParseData::setArray($form_data, 'dimension', []);
                    $condition = ParseData::setArray($form_data, 'condition', []);
                    $value = ParseData::setArray($form_data, 'value', []);
                    $operator = ParseData::setArray($form_data, 'operator', []);
                    $chart = ParseData::setArray($form_data, 'charttype', 'line'); 
                    $results = $this->getDoctrine()->getRepository('AppBundle:Analytics')->findByReport(
                        $from
                        ,$to
                        ,$y
                        ,$x
                        ,$dimension
                        ,$condition
                        ,$value
                        ,$operator
                    );

                }
                else
                    throw new \Exception(403, 'Invalid request');


                $labels = AnalyticsFactory::getFilterLabels($dimension, $condition, $x, $y);    
               
                return $this->renderRoute("admin/reports/view.html.twig", [
                    'results'=>$results
                    ,'chart' => $chart
                    ,'dimensions' => $labels['dimensions']
                    ,'conditions' => $labels['conditions']                
                    ,'values' => $value
                    ,'x' => $labels['x']
                    ,'y' => $labels['y']
                    ,'from' => $from
                    ,'to' => $to
                    ,'operators' => $operator
                ], $_render);               
            }
        ,
        $this->generateUrl('admin_report_view')
        );

    }

    public function configurationSettingsAction(Request $Request, $id = null)
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request, $id)
            {

                $Doctrine = $this->getDoctrine();
                $settings = null;
                $label = null;
                if(!empty($id)) 
                {
                    $settings = $Doctrine->getRepository('AppBundle:ConfigurationSetting')->findByConfiguration($id);
                    $Configuration = $Doctrine->getRepository('AppBundle:Configuration')->find($id);
                    $label = $Configuration->getLabel();
                }
                $settings_array = [];
                if(count($settings) > 0)
                {
                    foreach($settings as $Setting)
                    {
                        $settings_array[$Setting->getSettingKey()] = $Setting;
                    }       
                }
 
                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $EntityManager = $Doctrine->getManager();            

                    $Configuration = new Configuration();                    
                    $title = ParseData::setArray($form_data,'title', 'Configuration');
                    $Configuration->setLabel($title);
                    unset($form_data['title']);
                    $EntityManager->persist($Configuration);
                    
                    foreach($form_data as $key=>$value)
                    {
                        if(!isset($settings_array[$key]))
                        {
                            $Setting = new ConfigurationSetting($Configuration);
                            $Setting->setSettingKey($key);
                            $settings_array[$key] = $Setting;                    
                        }
                        else 
                        {
                            $Setting = $settings_array[$key];
                        }

                        $Setting->setValue($value);               
                        $EntityManager->persist($Setting);                   

                    }
                    $EntityManager->flush();
                    $messages[] = ViewMessage::constructMessage('Setting configuration created..', 'success');
                    $Session->set('messages', $messages);
                    return $this->redirect($this->generateUrl('admin_universal_list', ['reponame'=>'Configuration']));
                }

                return $this->render("admin/Configuration/configuration.html.twig", [
                    'settings'=>$settings_array
                    ,'label'=>$label
                ]);
            },
            $this->generateUrl('admin_settings', ['id'=>$id])
        );
    }


    public function preRenderRoute()
    {

    }

    public function logoutAction() {}

}
