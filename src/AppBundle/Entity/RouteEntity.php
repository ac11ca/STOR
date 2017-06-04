<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Route
 */
class RouteEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;
    private $url;

    public function __construct($name, $url)
    {
        $this->setName($name);
        $this->setUrl($url);
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }


    public function toArray()
    {
        return [
            'name'=> $this->getName()
            ,'url' => $this->getUrl()
        ];
    }
}

