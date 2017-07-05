<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Setting
 */
class Configuration
{
    /**
     * @var int
     */
    private $id;

    private $created;

    private $label;

    private $settings;
    private $products;

    public function __construct()
    {
        $this->setCreated(time());
        $this->setSettings(new ArrayCollection());
        $this->setProducts(new ArrayCollection());
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function setSettings(ArrayCollection $settings)
    {
        $this->settings = $settings;
        return $this;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function addSetting(ConfigurationSetting $ConfigurationSetting)
    {
        if($this->settings->contains($ConfigurationSetting))
            return $this;

        $this->settings->add($ConfigurationSetting);
        return $this;
    }

    public function removeSetting(ConfigurationSetting $ConfigurationSetting)
    {
        if($this->settings->contains($ConfigurationSetting))
            $this->settings->remove($ConfigurationSetting);

        return $this;
    }

    public function setProducts(ArrayCollection $products)
    {
        $this->products = $products;
        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(Product $Product)
    {
        if($this->products->contains($Product))
            return $this;

        $this->products->add($Product);
        return $this;
    }

    public function removeProduct(Product $Product)
    {
        if($this->products->contains($Product))
            $this->products->remove($Product);

        return $this;
    }

}

