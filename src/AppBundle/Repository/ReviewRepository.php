<?php

namespace AppBundle\Repository;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Product;

/**
 * ReviewRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReviewRepository extends ApplicationMasterRepository
{
    protected $filter_property = 'reviewer';
    protected $FactoryType = 'AppBundle\Factory\ReviewFactory';

    public function findByProductAverages($products)
    {       
        $formatted_result = [];
		if(is_a($products, 'Doctrine\Common\Collections\ArrayCollection'))
	        $product_collection = $products; 
		else
			$product_collection = new ArrayCollection($products);

        $query = $this->createQueryBuilder('r')
                    ->select('AVG(r.rating), p.id')
                    ->innerJoin('r.Product','p')
                    ->where('r.Product in (:products)')
                    ->groupBy('r.Product')
                    ->setParameter(':products',$product_collection);
        $result = $query->getQuery()->getResult();            

        for($i = 0; $i < count($result); $i++)
        {
            $formatted_result[$result[$i]['id']] = $result[$i][1];
        }

        return $formatted_result;
    }

    public function findByProductAndValue(Product $Product)
    {
        $rating_sums = [];
        $ratings = [];
        $total = 0;
        $query = $this->createQueryBuilder('r')
                   ->select('r.rating')
                   ->where('r.Product = :product')
                   ->setParameter(':product',$Product);
        $result = $query->getQuery()->getResult();
        for($i = 0; $i < count($result); $i++)
        {
           if(empty($rating_sums[floor($result[$i])]))
               $rating_sums[floor($result[$i])] = 0;

           $rating_sums[floor($result[$i])]++;
           $total++;
        }

        foreach($rating_sums as $rating=>$tally)
        {
            $ratings[$rating] = [
                'total' => $tally
                ,'percent' => ($total > 0 ? ($tally / $total) : 0)
            ];
        }

        return $ratings;
    }
}
