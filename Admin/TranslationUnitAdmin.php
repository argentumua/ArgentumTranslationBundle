<?php

namespace Argentum\TranslationBundle\Admin;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * TranslationUnitAdmin
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationUnitAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('domain')
            ->add('token')
            ->add('translations', 'a2lix_translations', array(
                'translation_domain' => $this->translationDomain,
                'fields' => array(
                    'content' => array(
                        'field_type' => 'textarea',
                        'attr' => array(
                            'rows' => 5
                        )
                    )
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('domain')
            ->add('token')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('token')
            ->add('content')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterParameters()
    {
        $this->datagridValues['domain']['value'] = 1;
        $this->datagridValues['_sort_by'] = 'token';

        return parent::getFilterParameters();
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        /** @var \Argentum\TranslationBundle\Entity\TranslationUnit $object */
        $object = parent::getNewInstance();

        $filters = $this->getFilterParameters();
        if (isset($filters['domain'])) {
            /** @var \Argentum\TranslationBundle\Entity\TranslationDomain $domain */
            $domain = $this->getModelManager()
                ->find('ArgentumTranslationBundle:TranslationDomain', $filters['domain']['value']);
            if ($domain) {
                $object->setDomain($domain);
            }
        }

        return $object;
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
