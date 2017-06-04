<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

class Pagination
{

    private $id;
    private $name;
    private $url;

    public function __construct($subset, $pages, $total, $offset, $page)
    {
        $this->setSubset($subset);
        $this->setPages($pages);
        $this->setTotal($total);
        $this->setOffset($offset);
        $this->setPage($page);

    }

    public function setSubset($subset)
    {
        $this->subset = $subset;
        return $this;
    }

    public function getSubset()
    {
        return $this->subset;
    }

    public function getSubsetArray()
    {
        $subset_collection = $this->getSubset();

        $subset_array = [];

        foreach($subset_collection as $Subset)
        {
            $subset_array[] = $Subset->toArray();
        }

        return $subset_array;
    }



    public function setPages($pages)
    {
        $this->pages = $pages;
        return $this;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setpage($page)
    {
        $this->page = $page;
        return $this;
    }

    public function getpage()
    {
        return $this->page;
    }

   
    public function toArray()
    {
        return [
            'subset' => empty($this->getSubset()) ? null : $this->getSubsetArray()
            ,'pages' => $this->getPages()
            ,'total' => $this->getTotal()
            ,'offset' => $this->getOffset()
            ,'page' => $this->getPage()
        ];
    }
}

