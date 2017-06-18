<?php

namespace AppBundle\Repository;

class AuthorRepository extends ApplicationMasterRepository
{
    protected $filter_property = 'name';
    protected $FactoryType = 'AppBundle\Factory\AuthorFactory';
}
