<?php

namespace Argentum\TranslationBundle\Translation;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * DatabaseResource
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class DatabaseResource implements ResourceInterface
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $domain;

    /**
     * Constructor.
     *
     * @param string $locale
     * @param string $domain
     */
    public function __construct($locale, $domain)
    {
        $this->locale = $locale;
        $this->domain = $domain;
    }

    /**
     * Returns a string representation of the Resource.
     *
     * @return string A string representation of the Resource
     */
    public function __toString()
    {
        return (string) $this->getResource();
    }

    /**
     * Returns the resource tied to this Resource.
     *
     * @return mixed The resource
     */
    public function getResource()
    {
        return sprintf('%s:%s', $this->locale, $this->domain);
    }

    /**
     * Returns true if the resource has not been updated since the given timestamp.
     *
     * @param integer $timestamp The last time the resource was loaded
     *
     * @return Boolean true if the resource has not been updated, false otherwise
     */
    public function isFresh($timestamp)
    {
        return true;
    }
}
