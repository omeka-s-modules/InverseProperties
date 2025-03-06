<?php
namespace InverseProperties\Stdlib;

use Omeka\Entity;
use InverseProperties\Entity\InversePropertiesInverse;
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
    public function getEntity(string $entityName, int $entityId): ?Entity\EntityInterface
    {
        return $this->entityManager->find($entityName, $entityId);
    }

    /**
     * Get inverse entities for a resource template.
     */
    public function getInverses(int $resourceTemplateId): array
    {
        return $this->entityManager
            ->getRepository('InverseProperties\Entity\InversePropertiesInverse')
            ->findBy(['resourceTemplate' => $resourceTemplateId]);
    }

    /**
     * Set inverse properties for a resource template.
     */
    public function setInverseProperties(int $resourceTemplateId, array $inversePropertyIds): void
    {
        $resourceTemplate = $this->getEntity('Omeka\Entity\ResourceTemplate', $resourceTemplateId);
        if (!$resourceTemplate) {
            // This resource template does not exist.
            return;
        }
        // We must set a nonexistent ID (0) or no existing inverses will be
        // deleted if the user unsets all inverse properties in the UI.
        $retainIds = [0];
        foreach ($inversePropertyIds as $resourceTemplatePropertyId => $inversePropertyId) {
            if (!(is_numeric($resourceTemplatePropertyId) && is_numeric($inversePropertyId))) {
                // Invalid format.
                continue;
            }
            $resourceTemplateProperty = $this->getEntity('Omeka\Entity\ResourceTemplateProperty', $resourceTemplatePropertyId);
            if (!$resourceTemplateProperty) {
                // This resource template property does not exist.
                continue;
            }
            $inverseProperty = $this->getEntity('Omeka\Entity\Property', $inversePropertyId);
            if (!$inverseProperty) {
                // This property does not exist.
                continue;
            }
            $inverse = $this->entityManager
                ->getRepository('InverseProperties\Entity\InversePropertiesInverse')
                ->findOneBy(['resourceTemplateProperty' => $resourceTemplateProperty]);
            if ($inverse) {
                // This inverse already exists.
                $inverse->setInverseProperty($inverseProperty);
            } else {
                // This inverse does not exist. Create it.
                $inverse = new InversePropertiesInverse;
                $inverse->setResourceTemplate($resourceTemplate);
                $inverse->setResourceTemplateProperty($resourceTemplateProperty);
                $inverse->setInverseProperty($inverseProperty);
                $this->entityManager->persist($inverse);
            }
            // Must flush here so Doctrine generates the ID.
            $this->entityManager->flush();
            $retainIds[] = $inverse->getId();
        }
        // Delete all inverse properties that did not already exist and weren't
        // newly created above.
        $dql = 'DELETE FROM InverseProperties\Entity\InversePropertiesInverse i
        WHERE i.resourceTemplate = :resourceTemplate
        AND i.id NOT IN (:ids)';
        $this->entityManager
            ->createQuery($dql)
            ->setParameter('resourceTemplate', $resourceTemplate)
            ->setParameter('ids', $retainIds)
            ->execute();
    }

    /**
     * Set inverse property values for a resource entity.
     */
    public function setInversePropertyValues(Entity\Resource $resource): void
    {
        $resourceTemplate = $resource->getResourceTemplate();
        if (!$resourceTemplate) {
            // This resource has no resource template.
            return;
        }
        $inverses = $this->getInverses($resourceTemplate->getId());
        if (!$inverses) {
            // This resource template has no inverses.
            return;
        }
        // Cache the a) property entities and b) property ID / inverse property
        // ID pairs.
        $properties = [];
        $inversePropertyIds = [];
        foreach ($inverses as $inverse) {
            $property = $inverse->getResourceTemplateProperty()->getProperty();
            $inverseProperty = $inverse->getInverseProperty();
            $properties[$property->getId()] = $property;
            $properties[$inverseProperty->getId()] = $inverseProperty;
            $inversePropertyIds[$property->getId()] = $inverseProperty->getId();
        }
        $resourceDataTypes = ['resource', 'resource:item', 'resource:itemset', 'resource:media'];
        // Iterate this resource's values.
        foreach ($resource->getValues() as $value) {
            $valueDataType = $value->getType();
            if (!in_array($valueDataType, $resourceDataTypes)) {
                // This is not a resource data type.
                continue;
            }
            $valuePropertyId = $value->getProperty()->getId();
            if (!array_key_exists($valuePropertyId, $inversePropertyIds)) {
                // This property has no inverse.
                continue;
            }
            // This is a resource value with an inverse property. Now determine
            // whether the resource value already has the inverse value.
            $hasInverse = false;
            foreach ($value->getValueResource()->getValues() as $resourceValue) {
                $resourceValueDataType = $resourceValue->getType();
                if (!in_array($resourceValueDataType, $resourceDataTypes)) {
                    // This is not a resource data type.
                    continue;
                }
                $resourceValuePropertyId = $resourceValue->getProperty()->getId();
                if ($resourceValuePropertyId !== $inversePropertyIds[$valuePropertyId]) {
                    // This property is not the inverse.
                    continue;
                }
                if ($resource !== $resourceValue->getValueResource()) {
                    // This resource value's resource value is not the original resource.
                    continue;
                }
                // This resource value already has the inverse value.
                $hasInverse = true;
                break;
            }
            if ($hasInverse) {
                // An inverse value already exists.
                continue;
            }
            // An inverse value does not exist. Create it.
            $inverseProperty = $properties[$inversePropertyIds[$valuePropertyId]];
            $inverseValue = new Entity\Value;
            $inverseValue->setResource($value->getValueResource());
            $inverseValue->setProperty($inverseProperty);
            $inverseValue->setType('resource');
            $inverseValue->setValueResource($value->getResource());
            $this->entityManager->persist($inverseValue);
        }
        $this->entityManager->flush();
    }
}
