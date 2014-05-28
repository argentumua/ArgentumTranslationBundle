<?php

namespace Argentum\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * TranslationDomain Translation
 *
 * @ORM\Table(name="translation_domain_translation")
 * @ORM\Entity()
 */
class TranslationDomainTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;


    /**
     * Sets title.
     *
     * @param string $title
     *
     * @return TranslationDomainTranslation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
