<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Analytics;

class AnalyticsFactory extends ApplicationMasterFactory
{ 
    protected $fieldKeys = [''];
    protected $EntityType = 'AppBundle\Entity\Analytics';

    public function __construct($Repository, $Doctrine, $Manager)
    {
        $this->setDoctrine($Doctrine);
        //Case mut match that used in the getter and setter method as 'get'and 'set' wll be appended to the keys.
         $this->setFields([
            'Id' => $this->initializeField(
                'none', null, null, null, null
            )
            ,'EventType' => $this->initializeField(
                'text','Event','',''
            )
            ,'SessionId' => $this->initializeField(
                'text','Session','',''
            )
            ,'Label' => $this->initializeField(
                'text', 'Label','',''
            )
        ]);

        $this->settings = ['cloudinary' => $Doctrine->getRepository('CYINTSettingsBundle:Setting')->findByNamespace('cloudinary') ];

        parent::__construct($Repository, $Doctrine, $Manager);
    }

    public static function getConditionalMappings()
    {
        return [
            '=' => 'Equals'
           ,'like' => 'Contains'
           ,'<' => 'Is less than'
           ,'<=' => 'Is less than or equal to'
           ,'>' => 'Is greater than'
           ,'>=' => 'Is greater than or equal to'
        ];
    }

    public static function getConditionalMappingLabel($value)
    {
        $mappings = self::getConditionalMappings();
        return empty($mappings[$value]) ? '' : $mappings[$value];
    }


    public static function getDimensionMappings()
    {
        return [
            '' => ''
           ,'u.externalId' => 'User external id'
           ,'u.ip_address' => 'User IP'
           ,'s.id' => 'Session ID'
           ,'' => 'Configuration ID'
           ,'a.event_type' => 'Event Type'
           ,'a.category' => 'Category'
          ,'a.label' => 'Label'
        ];
    }

    public static function getDimensionMappingLabel($value)
    {
        $mappings = self::getDimensionMappings();
        return empty($mappings[$value]) ? '' : $mappings[$value];
    }

    public static function getXOptions()
    {
        return [
            'a.time' => 'Date'
            ,'a.event_type' => 'Event'
            ,'a.category' => 'Category'
            ,'a.label' => 'Label'
            ,'c.id' => 'Configuration ID'
            ,'s.id' => 'Session ID'
            ,'u.externalId' => 'External ID'
        ];
    }

    public static function getYOptions()
    {
        return [
            'frequency' => 'Frequency'
            ,'avgfrequency' => 'Average Frequency'
            ,'duration' => 'Duration'
            ,'avgduration' => 'Average Duration'
        ];
    }

    public static function getYOptionLabel($value)
    {
        $mappings = self::getYOptions();
        return empty($mappings[$value]) ? '' : $mappings[$value];
    }

    public static function getXOptionLabel($value)
    {
        $mappings = self::getXOptions();
        return empty($mappings[$value]) ? '' : $mappings[$value];
    }

    public static function getFilterLabels($dimensions, $conditions, $x, $y)
    {
        $dimension_labels = [];
        $condition_labels = [];
        $x_label = self::getXOptionLabel($x);
        $y_label = self::getYOptionLabel($y);

        if(!empty($dimensions))
        {
            foreach($dimensions as $dimension)
            {
                $dimension_labels[] = self::getDimensionMappingLabel($dimension);
            }
        }

        if(!empty($conditions))
        {
            foreach($conditions as $condition)
            {
                $condition_labels[] = self::getConditionalMappingLabel($condition);
            }
        }


        return [
            'dimensions' => $dimension_labels
            ,'conditions' => $condition_labels
            ,'x' => $x_label
            ,'y' => $y_label
        ];
    } 
    
}
