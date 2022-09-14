<?php
namespace InverseProperties\Stdlib;

use Omeka\Entity\Resource;
use InverseProperties\Entity\InversePropertiesPropertyPair;
use Zend\ServiceManager\ServiceLocatorInterface;

class InverseProperties
{
    protected $services;

    protected $entityManager;

    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
        $this->entityManager = $this->services->get('Omeka\EntityManager');
    }

    /**
     * Set inverse property values for a resource entity.
     */
    public function setInversePropertyValues(Resource $resourceEntity) : void
    {
    }
}
