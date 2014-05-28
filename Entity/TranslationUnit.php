<?php

namespace Argentum\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TranslationUnit
 *
 * @ORM\Table(name="translation_unit")
 * @ORM\Entity(repositoryClass="Argentum\TranslationBundle\Entity\TranslationUnitRepository")
 */
class TranslationUnit
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
     * @var TranslationDomain
     *
     * @ORM\ManyToOne(targetEntity="TranslationDomain", inversedBy="units")
     * @ORM\JoinColumn(nullable=false)
     */
    private $domain;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $token;


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
     * Set token
     *
     * @param string $token
     *
     * @return TranslationUnit
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set domain
     *
     * @param TranslationDomain $domain
     *
     * @return TranslationUnit
     */
    public function setDomain(TranslationDomain $domain = null)
    {
        $this->domain = $domain;
    
        return $this;
    }

    /**
     * Get domain
     *
     * @return TranslationDomain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Returns a string representation of the entity.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getToken();
    }

    /**
     * Returns translatable content.
     *
     * @param string $locale
     *
     * @return string
     */
    public function getContent($locale = null)
    {
        return $this->translate($locale)->getContent();
    }
}
