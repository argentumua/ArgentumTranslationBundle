<?php

namespace Argentum\TranslationBundle\Admin;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * TranslationDomainAdmin
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationDomainAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('translations', 'a2lix_translations', array(
                'translation_domain' => $this->translationDomain
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('title')
            ->add('units', 'string', array(
                'template' => 'ArgentumTranslationBundle::list_units_link.html.twig'
            ));
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterParameters()
    {
        $this->datagridValues['_sort_by'] = 'name';

        return parent::getFilterParameters();
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);

        if ($context == 'list') {
            $query->addSelect('t');
            $query->leftJoin('o.translations', 't', Join::WITH, 't.locale = :locale');
            $query->setParameter('locale', $this->getRequest()->getLocale());
        }

        return $query;
    }
}
