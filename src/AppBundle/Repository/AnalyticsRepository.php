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
    public function findByReport($from = null, $to = null, $y = null, $x = null, $dimension = [], $condition = [], $value = [], $operator = [])
    {
        $query = $this->createQueryBuilder('a');
        $graph_data = [];                

        $query->innerJoin('a.Session', 's')
            ->innerJoin('s.User', 'u');

        if(!empty($from))
            $query->andWhere('a.created >= :from')
                ->setParameter(':from', $from);

        if(!empty($to))
            $query->andWhere('a.created <= :to')
                ->setParameter(':to', $to);

        switch($y)
        {
           case 'duration':
            $query->select('a as duration');
           break;

           case 'frequency':
            $query->select('count(a) as frequency,' . $x);
           break; 

           case 'avgduration':
            $query->select('a as avgduration');
           break;

           case 'avgfrequency':
            $query->select('count(a) as avgfrequency,' . $x);
           break;
        }

        $query->groupBy($x);

        for($i = 0; $i < count($dimension); $i++)
        {
            $dimensional = $dimension[$i];
            $conditional = $condition[$i];
            $valueset = $value[$i];
            $clause = $dimensional . ' ' . $conditional . ' :value';

            if($i > 0 && $operator[$i-1] == 1)
                $query->orWhere($clause);
            else
                $query->andWhere($clause);

            if($conditional == 'like')
                $query->setParameter(':value', "%$valueset%");
            else
                $query->setParameter(':value', $valueset);
        }

        $results = $query->getQuery()->getResult();

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
}
