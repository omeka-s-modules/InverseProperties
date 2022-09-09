<?php
namespace InverseProperties\ControllerPlugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

class InverseProperties extends AbstractPlugin
{
    protected $services;

    protected $entityManager;

    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
        $this->entityManager = $this->services->get('Omeka\EntityManager');
    }

    public function getPropertyPairs()
    {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('InverseProperties\Entity\InverseProperties', 'ip');
    }
}
