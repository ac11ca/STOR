<?php

namespace AppBundle\Repository;

/**
 * ConfigurationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ConfigurationRepository extends ApplicationMasterRepository
{
    protected $filter_property = 'label';
    protected $FactoryType = 'AppBundle\Factory\ConfigurationFactory';
}
