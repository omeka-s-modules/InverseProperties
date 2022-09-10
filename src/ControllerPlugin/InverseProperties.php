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

    /**
     * Get property pairs.
     */
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

    /**
     * Set property pairs.
     */
    public function setPropertyPairs(array $propertyPairs) : void
    {
        // Note that owl:inverseOf is a symmetric property, meaning that the
        // (p1,p2) pair is semantically equivalent to the (p2,p1) pair. We must
        // account for this when selecting the pair from the database or we risk
        // duplicating semantically equivalent pairs.
        $dqlSelect = 'SELECT pp
        FROM InverseProperties\Entity\InversePropertiesPropertyPair pp
        WHERE (pp.p1 = :p1 AND pp.p2 = :p2)
        OR (pp.p1 = :p2 AND pp.p2 = :p1)';
        $selectQuery = $this->entityManager->createQuery($dqlSelect);
        // We must set a nonexistent ID (0) or no existing property pairs will
        // be deleted if the user deletes all property pairs in the UI.
        $retainIds = [0];
        foreach ($propertyPairs as $propertyPair) {
            if (!(is_array($propertyPair) && isset($propertyPair['p1']) && isset($propertyPair['p2']))) {
                // Invalid format.
                continue;
            }
            if ($propertyPair['p1'] == $propertyPair['p2']) {
                // A property cannot be an inverse of itself.
                continue;
            }
            $p1 = $this->entityManager->find('Omeka\Entity\Property', $propertyPair['p1']);
            $p2 = $this->entityManager->find('Omeka\Entity\Property', $propertyPair['p2']);
            if (!($p1 && $p2)) {
                // Both properties must exist.
                continue;
            }
            $propertyPairEntities = $selectQuery
                ->setParameter('p1', $propertyPair['p1'])
                ->setParameter('p2', $propertyPair['p2'])
                ->getResult();
            if ($propertyPairEntities) {
                // This pair already exists.
                $retainIds[] = $propertyPairEntities[0]->getId();
            } else {
                // This pair does not exist. Create it.
                $propertyPairEntity = new InversePropertiesPropertyPair;
                $propertyPairEntity->setP1($p1);
                $propertyPairEntity->setP2($p2);
                $this->entityManager->persist($propertyPairEntity);
                // Must flush here so Doctrine generates the ID.
                $this->entityManager->flush();
                $retainIds[] = $propertyPairEntity->getId();
            }
        }
        // Delete all pairs did not already exist and weren't newly created.
        $dqlDelete = 'DELETE FROM InverseProperties\Entity\InversePropertiesPropertyPair pp WHERE pp.id NOT IN (:ids)';
        $this->entityManager
            ->createQuery($dqlDelete)
            ->setParameter('ids', $retainIds)
            ->execute();
    }
}
