<?php

namespace Argentum\TranslationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * TranslationUnit Translation
 *
 * @ORM\Table(name="translation_unit_translation")
 * @ORM\Entity()
 */
class TranslationUnitTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @var string $content
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;


    /**
     * Sets content.
     *
     * @param string $content
     *
     * @return TranslationUnitTranslation
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Returns content.
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }
}
