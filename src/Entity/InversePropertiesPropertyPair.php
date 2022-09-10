<?php
namespace InverseProperties\Entity;

use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Property;
use Omeka\Entity\User;

/**
 * @Entity
 */
class InversePropertiesPropertyPair extends AbstractEntity
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
     *     targetEntity="Omeka\Entity\Property"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $p1;

    public function setP1(Property $p1) : void
    {
        $this->p1 = $p1;
    }

    public function getP1() : Property
    {
        return $this->p1;
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
    protected $p2;

    public function setP2(Property $p2) : void
    {
        $this->p2 = $p2;
    }

    public function getP2() : Property
    {
        return $this->p2;
    }
}
