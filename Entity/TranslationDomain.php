<?php

namespace Argentum\TranslationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TranslationDomain
 *
 * @ORM\Table(name="translation_domain")
 * @ORM\Entity(repositoryClass="Argentum\TranslationBundle\Entity\TranslationDomainRepository")
 */
class TranslationDomain
{
    use ORMBehaviors\Translatable\Translatable,
        ORMBehaviors\Timestampable\Timestampable
        ;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $name;

    /**
     * @var TranslationUnit[]
     *
     * @ORM\OneToMany(targetEntity="TranslationUnit", mappedBy="domain", cascade={"persist", "remove"})
     */
    private $units;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->units = new ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TranslationDomain
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add units
     *
     * @param TranslationUnit $unit
     *
     * @return TranslationDomain
     */
    public function addUnit(TranslationUnit $unit)
    {
        $this->units[] = $unit;
    
        return $this;
    }

    /**
     * Remove units
     *
     * @param TranslationUnit $unit
     */
    public function removeUnit(TranslationUnit $unit)
    {
        $this->units->removeElement($unit);
    }

    /**
     * Get units
     *
     * @return ArrayCollection
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Returns a string representation of the entity.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Returns translatable title.
     *
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale = null)
    {
        return $this->translate($locale)->getTitle();
    }
}
