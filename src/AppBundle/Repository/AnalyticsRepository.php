<?php

namespace AppBundle\Repository;

/**
 * AnalyticsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnalyticsRepository extends ApplicationMasterRepository
{
    protected $FactoryType = 'AppBundle\Factory\AnalyticsFactory';
    protected $filter_property = 'created'; 
    public function findByReport($from = null, $to = null, $y = null, $x = null, $dimension = [], $condition = [], $value = [], $operator = [], $raw = false)
    {
        $graph_data = [];                

        $query = $this->prepareReportQuery($from, $to, $y);
        $xisdate = false;
		$datemods = [
			'day' => 'DAY'
            ,'month' => 'MONTH'
		];

        $DoctrineConfig = $this->getEntityManager()->getConfiguration(); 
        $DoctrineConfig->addCustomNumericFunction('Round', 'DoctrineExtensions\Query\Mysql\Round');

        if($x == 'day' || $x == 'week' || $x == 'month')
        {
            $xisdate = true;
            $DoctrineConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
            $DoctrineConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');
            $DoctrineConfig->addCustomDatetimeFunction('FROM_UNIXTIME', 'DoctrineExtensions\Query\Mysql\UnixTimestamp');
			$x = $datemods[$x] . '(FROM_UNIXTIME(a.created)) as x';
        }

        switch($y)
        {
           case 'duration':
            $query->select('sum(a.time) as duration, ' . $x);
           break;

           case 'frequency':
            $query->select('count(a) as frequency,' . $x);
           break; 

           case 'avgduration':
            $query->select('Round(avg(a.time),3) as avgduration, ' . $x);
           break;

           case 'avgfrequency':
            $query->select('Round(avg(a),3) as avgfrequency,' . $x);
           break;
        }
  
        if($xisdate)
            $query->groupBy('x');
        else
            $query->groupBy($x);                  

        $query = $this->constructQueryFilter($dimension, $condition, $operator, $value, $query);        
        $results = $query->getQuery()->getResult();

        if(!empty($raw))
            return $results;
        
        if(count($results) > 0)
        {
            foreach($results as $result)
            {
                $a = array_pop($result);
                $b = array_pop($result);
                $graph_data[] = ['x'=>$a, 'y'=>$b];
            }

        }
    
        return $graph_data;
    }

    public function findByReportRaw($from = null, $to = null, $y = null, $x = null, $dimension = [], $condition = [], $value = [], $operator = [], $raw = false)
    {
        $query = $this->prepareReportQuery($from, $to, $y);
        $query->addSelect('u');
        $query->addSelect('s');
        $query->addSelect('c');
        $query->orderBy('u.id ASC , a.category ASC');
        $query = $this->constructQueryFilter($dimension, $condition, $operator, $value, $query);
        
        $results = $query->getQuery()->getResult();
        $result_array = [];
        
        foreach($results as $Result)
        {
//            if('36c70699-de21-11e7-b514-027c6b0a5697' == $Result->getSession()->getUser()->getId()){
            $result_array[] = [
                'id' => $Result->getId()      
                ,'event' => $Result->getEventType()
                ,'category' => $Result->getCategory()
                ,'label' => $Result->getLabel()
                ,'time' => $Result->getTime()
                ,'created' => $Result->getCreated()
                ,'session_id'=> $Result->getSession()->getId()
                ,'user_id'=> $Result->getSession()->getUser()->getId()
                ,'user_external_id'=> $Result->getSession()->getUser()->getExternalId()
                ,'user_ip'=> $Result->getSession()->getUser()->getIpAddress()
                ,'configuration_id'=> $Result->getSession()->getConfiguration()->getId()
                ,'configuration_settings' => json_encode($this->getConfigurationSettingsString($Result->getSession()->getConfiguration()->getSettings()))
            ];
//            }
        }
//print_r($result_array); exit;
        return $result_array;        
    }

    protected function constructQueryFilter($dimension, $condition, $operator, $value, $query)
    {
        if(!empty($dimension) && !empty($dimension[0]))
        {
            for($i = 0; $i < count($dimension); $i++)
            {
                $dimensional = $dimension[$i];
                $conditional = $condition[$i];
                $valueset = $value[$i];
                $clause = $dimensional . ' ' . $conditional . ' :value_' . $i;

                if($i > 0 && $operator[$i-1] == 1)
                    $query->orWhere($clause);
                else
                    $query->andWhere($clause);

                if($conditional == 'like')
                    $query->setParameter(':value_' . $i, "%$valueset%");
                else
                    $query->setParameter(':value_' . $i, $valueset);
  
            }

        }

        return $query;
    }

    public function prepareReportQuery($from, $to, $y)
    {
        $query = $this->createQueryBuilder('a');
       
        $query->innerJoin('a.Session', 's')
            ->innerJoin('s.User', 'u')
            ->innerJoin('s.Configuration', 'c');

        if(!empty($from))
            $query->andWhere('a.created >= :from')
                ->setParameter(':from', $from);

        if(!empty($to))
            $query->andWhere('a.created <= :to')
                ->setParameter(':to', $to);


        if(stristr($y, 'duration') > -1)
            $query->andWhere('a.event_type = \'duration\'');

        return $query;
    }

    public function getConfigurationSettingsString($configuration_settings)
    {
        $result = [];
        foreach($configuration_settings as $ConfigurationSetting) 
        {
            $result[$ConfigurationSetting->getSettingKey()] = $ConfigurationSetting->getValue();
        }

        return $result;
    }

    public function findCategoryNames()
    {
        $query = $this->createQueryBuilder('a');
        $query->select('a.category');
        $query->groupBy('a.category');
        $query->orderBy('a.category');

        return $query->getQuery()->getResult();
    }

    public function findEventNames()
    {
        $query = $this->createQueryBuilder('a');
        $query->select('a.event_type');
        $query->groupBy('a.event_type');
        $query->orderBy('a.event_type');

        return $query->getQuery()->getResult();
    }

}
