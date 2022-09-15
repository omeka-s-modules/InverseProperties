<?php
namespace InverseProperties\Stdlib;

use Omeka\Entity;
use InverseProperties\Entity\InversePropertiesInverseProperty;
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
     * Get an entity.
     */
    public function getEntity(string $entityName, int $entityId) : ?Entity\EntityInterface
    {
        return $this->entityManager->find($entityName, $entityId);
    }

    /**
     * Get inverse properties for a resource template.
     */
    public function getInverseProperties(int $resourceTemplateId) : array
    {
        return $this->entityManager
            ->getRepository('InverseProperties\Entity\InversePropertiesInverseProperty')
            ->findBy(['resourceTemplate' => $resourceTemplateId]);
    }

    /**
     * Set inverse properties for a resource template.
     */
    public function setInverseProperties(int $resourceTemplateId, array $inversePropertyIds) : void
    {
        $resourceTemplate = $this->getEntity('Omeka\Entity\ResourceTemplate', $resourceTemplateId);
        if (!$resourceTemplate) {
            // This resource template does not exist.
            return;
        }
        // We must set a nonexistent ID (0) or no existing inverse properties
        // will be deleted if the user deletes all inverse properties in the UI.
        $retainIds = [0];
        foreach ($inversePropertyIds as $resourceTemplatePropertyId => $propertyId) {
            if (!(is_numeric($resourceTemplatePropertyId) && is_numeric($propertyId))) {
                // Invalid format.
                continue;
            }
            $resourceTemplateProperty = $this->entityManager->find('Omeka\Entity\ResourceTemplateProperty', $resourceTemplatePropertyId);
            if (!$resourceTemplateProperty) {
                // This resource template property does not exist.
                continue;
            }
            $property = $this->getEntity('Omeka\Entity\Property', $propertyId);
            if (!$property) {
                // This property does not exist.
                continue;
            }
            if ($resourceTemplateProperty->getProperty()->getId() === $property->getId()) {
                // A property cannot be an inverse of itself.
                continue;
            }
            $inverseProperty = $this->entityManager
                ->getRepository('InverseProperties\Entity\InversePropertiesInverseProperty')
                ->findOneBy(['resourceTemplateProperty' => $resourceTemplateProperty]);
            if ($inverseProperty) {
                // This inverse property already exists.
                $inverseProperty->setProperty($property);
                $retainIds[] = $inverseProperty->getId();
            } else {
                // This inverse property does not exist. Create it.
                $inverseProperty = new InversePropertiesInverseProperty;
                $inverseProperty->setResourceTemplate($resourceTemplate);
                $inverseProperty->setResourceTemplateProperty($resourceTemplateProperty);
                $inverseProperty->setProperty($property);
                $this->entityManager->persist($inverseProperty);
                // Must flush here so Doctrine generates the ID.
                $this->entityManager->flush();
                $retainIds[] = $inverseProperty->getId();
            }
        }
        // Delete all inverse properties that did not already exist and weren't
        // newly created above.
        $dqlDelete = 'DELETE FROM InverseProperties\Entity\InversePropertiesInverseProperty ip WHERE ip.id NOT IN (:ids)';
        $this->entityManager
            ->createQuery($dqlDelete)
            ->setParameter('ids', $retainIds)
            ->execute();
    }

    /**
     * Set inverse property values for a resource entity.
     */
    public function setInversePropertyValues(Entity\Resource $resourceEntity) : void
    {
    }
}
