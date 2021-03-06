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
    private $sessions;

    public function __construct()
    {
        $this->setCreated(time());
        $this->setSettings(new ArrayCollection());
        $this->setProducts(new ArrayCollection());
        $this->setSessions(new ArrayCollection());
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
        if($this->sessions->contains($Product))
            $this->sessions->remove($Product);

        return $this;
    }

    public function setSessions(ArrayCollection $sessions)
    {
        $this->sessions = $sessions;
        return $this;
    }

    public function getSessions()
    {
        return $this->sessions;
    }

    public function addSession(Session $Session)
    {
        if($this->sessions->contains($Session))
            return $this;

        $this->sessions->add($Session);
        return $this;
    }

    public function removeSession(Session $Session)
    {
        if($this->sessions->contains($Session))
            $this->sessions->remove($Session);

        return $this;
    }


    public function getAllConfigurationSettings()
    {
        $settings_formatted = [];
        $settings = $this->getSettings();
        if(!empty($settings))
        {
            foreach($settings as $ConfigurationSetting)
            {
                $settings_formatted[$ConfigurationSetting->getSettingKey()] = $ConfigurationSetting->getValue();
            }
        }

        return $settings_formatted;
    }
}

