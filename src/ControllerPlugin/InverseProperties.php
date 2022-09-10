<?php
namespace InverseProperties\ControllerPlugin;

use InverseProperties\Entity\InversePropertiesPropertyPair;
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

    public function getPropertyPairs() : array
    {
        $dql = 'SELECT pp FROM InverseProperties\Entity\InversePropertiesPropertyPair pp';
        $propertyPairEntities = $this->entityManager->createQuery($dql)->getResult();
        $propertyPairs = [];
        foreach ($propertyPairEntities as $propertyPairEntity) {
            $propertyPairs[] = [
                'p1' => $propertyPairEntity->getP1()->getId(),
                'p2' => $propertyPairEntity->getP2()->getId(),
            ];
        }
        return $propertyPairs;
    }

    public function setPropertyPairs(array $propertyPairs) : void
    {
        $dqlSelect = '
        SELECT pp
        FROM InverseProperties\Entity\InversePropertiesPropertyPair pp
        WHERE (pp.p1 = :p1 AND pp.p2 = :p2)
        OR (pp.p1 = :p2 AND pp.p2 = :p1)';
        $dqlDelete = '
        DELETE FROM InverseProperties\Entity\InversePropertiesPropertyPair pp
        WHERE pp.id NOT IN (:ids)';
        // We must set a nonexistent ID or no persistent property pairs will be
        // deleted if the user deletes all property pairs in the UI.
        $retainIds = [0];
        foreach ($propertyPairs as $propertyPair) {
            if ($propertyPair['p1'] == $propertyPair['p2']) {
                // A property cannot be an inverse of itself.
                continue;
            }
            $p1 = $this->entityManager->find('Omeka\Entity\Property', $propertyPair['p1']);
            $p2 = $this->entityManager->find('Omeka\Entity\Property', $propertyPair['p2']);
            if (!($p1 && $p2)) {
                // The properties must exist.
                continue;
            }
            $propertyPairEntities = $this->entityManager
                ->createQuery($dqlSelect)
                ->setParameter('p1', $propertyPair['p1'])
                ->setParameter('p2', $propertyPair['p2'])
                ->getResult();
            if ($propertyPairEntities) {
                $retainIds[] = $propertyPairEntities[0]->getId();
            } else {
                $propertyPairEntity = new InversePropertiesPropertyPair;
                $propertyPairEntity->setP1($p1);
                $propertyPairEntity->setP2($p2);
                $this->entityManager->persist($propertyPairEntity);
                $this->entityManager->flush();
                $retainIds[] = $propertyPairEntity->getId();
            }
        }
        $this->entityManager
            ->createQuery($dqlDelete)
            ->setParameter('ids', $retainIds)
            ->execute();
    }
}
