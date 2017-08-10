<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CYINT\ComponentsPHP\Classes\ViewMessage;
use CYINT\ComponentsPHP\Classes\ParseData;
use CYINT\ComponentsPHP\Bundles\SettingsBundle\Entity\Setting;
use CYINT\ComponentsPHP\Classes\Excel;
use CYINT\ComponentsPHP\Services\ExcelService;
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


    public function messagesAction(Request $Request)
    {
        $Session = $this->get('session');
        $messages = empty($Session->get('messages')) ? [] : $Session->get('messages');
        $Session->set('messages', null);
        return $this->render('admin/messages.html.twig', ['messages'=>$messages]);
    }


    public function cacheAction(Request $Request)
    {
        exec('/var/www/mockecommerce/console cache:clear', $response);
 
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

                $categories = $Doctrine->getRepository('AppBundle:Analytics')->findCategoryNames();
                $events = $Doctrine->getRepository('AppBundle:Analytics')->findEventNames();

                return $this->renderRoute("admin/reports/generate.html.twig", [
                    'dimension_mappings' => $dimension_mappings
                    ,'conditional_mappings' => $conditional_mappings
                    ,'categories' => $categories
                    ,'events' => $events
                    ,'x_mappings' => $x_mappings
                    ,'y_mappings' => $y_mappings
                ], $_render);               
            }
        ,
        $this->generateUrl('admin_reports')
        );
    }

    public function reportSummaryAction(Request $Request, $_render = 'HTML')
    {
        return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {  
                //$ExcelService = $this->get('app.excel');
                $ExcelService = $this->get('app.excel');
                $summary_data = [];
                $form_data = $Session->get('report_form_data');
                $report_data = $this->prepareReport($form_data, 'findByReportRaw');               
                $ReportService = $this->get('app.reportservice');
                $summary_data = $ReportService->summarizeReport($report_data['results']);
                $filename = $ExcelService->convertToExcel($summary_data, 'Summary');
                return $this->sendExcelFileResponse($Request, $filename, 'Summary');
            }
        ,
        $this->generateUrl('admin_reports')
        );
    }


    public function reportExportAction(Request $Request, $_render='HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {                               
                $ExcelService = $this->get('app.excel');
                $form_data = $Session->get('report_form_data');
                $report_data = $this->prepareReport($form_data);
                $filename = $ExcelService->convertToExcel($report_data['results'], 'Report');
                return $this->sendExcelFileResponse($Request, $filename, 'Report');
            }
            ,
            $this->generateUrl('admin_report_export')

         );      
    }

    public function reportExportRawAction(Request $Request, $_render='HTML')
    {
         return $this->handleErrors(
            function ($Session, $messages) use ($Request, $_render)
            {                               
                $ExcelService = $this->get('app.excel');
                $form_data = $Session->get('report_form_data');
                $report_data = $this->prepareReport($form_data, 'findByReportRaw');
                $filename = $ExcelService->convertToExcel($report_data['results'], 'Raw_Analytics');
                return $this->sendExcelFileResponse($Request, $filename, 'Report');
            }
            ,
            $this->generateUrl('admin_report_export_raw')
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
                    $Session->set('report_form_data', $form_data);
                    $report_data = $this->prepareReport($form_data);
                    
                }
                else
                    throw new \Exception('Invalid request', 403);


                $labels = AnalyticsFactory::getFilterLabels($report_data['dimension'], $report_data['condition'], $report_data['x'], $report_data['y']);    
               
                return $this->renderRoute("admin/reports/view.html.twig", [
                    'results'=>$report_data['results']
                    ,'chart' => $report_data['chart']
                    ,'dimensions' => $labels['dimensions']
                    ,'conditions' => $labels['conditions']                
                    ,'values' => $report_data['value']
                    ,'x' => $labels['x']
                    ,'y' => $labels['y']
                    ,'from' => $report_data['from']
                    ,'to' => $report_data['to']
                    ,'from_hour' => $report_data['from_hour']
                    ,'to_hour' => $report_data['to_hour']
                    ,'from_minute' => $report_data['from_minute']
                    ,'to_minute' => $report_data['to_minute']
                    ,'operators' => $report_data['operator']
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
                        $settings_array[$Setting->getSettingKey()] = $Setting->getValue();
                    }       
                }


                if($Request->isMethod('POST'))
                {
                    $form_data = $Request->request->all();
                    $EntityManager = $Doctrine->getManager();            
                    $Configuration = new Configuration();                    
                    $title = ParseData::setArray($form_data,'label', 'Configuration');
                    $Configuration->setLabel($title);
                    unset($form_data['label']);
                    unset($form_data['submit']);

                    $EntityManager->persist($Configuration);
                    
                    foreach($form_data as $key=>$value)
                    {
                        $Setting = new ConfigurationSetting($Configuration);
                        $Setting->setSettingKey($key);
                        $settings_array[$key] = $Setting;                                                
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
            $this->generateUrl('configuration_settings', ['id'=>$id])
        );
    }

    protected function prepareReport($form_data, $method='findByReport')
    {
        $from = ParseData::setArray($form_data, 'from', null);
        $to = ParseData::setArray($form_data, 'to', null);
        $from_hour = ParseData::setArray($form_data, 'from_hour', null);
        $to_hour = ParseData::setArray($form_data, 'to_hour', null);
        $from_minute = ParseData::setArray($form_data, 'from_minute', null);
        $to_minute = ParseData::setArray($form_data, 'to_minute', null);

        $y = ParseData::setArray($form_data, 'y', null);
        $x = ParseData::setArray($form_data, 'x', null);
        $dimension = ParseData::setArray($form_data, 'dimension', []);
        $condition = ParseData::setArray($form_data, 'condition', []);
        $value = ParseData::setArray($form_data, 'value', []);
        $operator = ParseData::setArray($form_data, 'operator', []);
        $chart = ParseData::setArray($form_data, 'charttype', 'line'); 
        $results = $this->getDoctrine()->getRepository('AppBundle:Analytics')->$method(
            strtotime($from . ' ' . $from_hour . ':'  . $from_minute)
            ,strtotime($to . ' ' . $to_hour . ':' . $to_minute)
            ,$y
            ,$x
            ,$dimension
            ,$condition
            ,$value
            ,$operator
        );

        return [
            'from' => $from
            ,'to' => $to
            ,'from_hour' => $from_hour
            ,'to_hour' => $to_hour
            ,'from_minute' => $from_minute
            ,'to_minute' => $to_minute
            ,'from_hour' => $from_hour
            ,'to_hour' => $to_hour
            ,'x' => $x
            ,'y' => $y
            ,'dimension' => $dimension
            ,'condition' => $condition
            ,'value' => $value
            ,'operator' => $operator
            ,'chart' => $chart
            ,'results' => $results
        ];
    }

    public function preRenderRoute()
    {

    }

    public function logoutAction() {}

}
