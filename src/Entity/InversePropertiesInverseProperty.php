<?php
namespace InverseProperties\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Property;
use Omeka\Entity\ResourceTemplate;
use Omeka\Entity\ResourceTemplateProperty;

/**
 * @Entity
 */
class InversePropertiesInverseProperty extends AbstractEntity
{
    /**
     * @Id
     * @Column(
     *     type="integer",
     *     options={
     *         "unsigned"=true
     *     }
     * )
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\ResourceTemplate"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $resourceTemplate;

    public function setResourceTemplate(ResourceTemplate $resourceTemplate) : void
    {
        $this->resourceTemplate = $resourceTemplate;
    }

    public function getResourceTemplate() : ResourceTemplate
    {
        return $this->resourceTemplate;
    }

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\ResourceTemplateProperty"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $resourceTemplateProperty;

    public function setResourceTemplateProperty(ResourceTemplateProperty $resourceTemplateProperty) : void
    {
        $this->resourceTemplateProperty = $resourceTemplateProperty;
    }

    public function getResourceTemplateProperty() : ResourceTemplateProperty
    {
        return $this->resourceTemplateProperty;
    }

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\Property"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $inverseProperty;

    public function setInverseProperty(Property $inverseProperty) : void
    {
        $this->inverseProperty = $inverseProperty;
    }

    public function getInverseProperty() : Property
    {
        return $this->inverseProperty;
    }
}
