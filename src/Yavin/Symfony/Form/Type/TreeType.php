<?php

namespace Yavin\Symfony\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Yavin\Symfony\Form\Type\Loader\TreeTypeLoader;

class TreeType extends AbstractType
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    public function getName()
    {
        return 'y_tree';
    }

    public function getParent()
    {
        return 'entity';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $levelPrefix = $options['levelPrefix'];

        if (empty($levelPrefix)) {
            return;
        }

        foreach ($view->vars['choices'] as $choice) {

            $dataObject = $choice->data;
            $level = $this->propertyAccessor->getValue($dataObject, $options['treeLevelField']);

            $choice->label = str_repeat($levelPrefix, $level) . $choice->label;
        }

        if (!empty($options['prefixAttributeName'])) {
            $view->vars['attr'][$options['prefixAttributeName']] = $levelPrefix;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $type = $this;

        $loader = function (Options $options) use ($type) {
            if (null !== $options['query_builder']) {
                return new TreeTypeLoader($options, $options['query_builder'], $options['em'], $options['class']);
            }
        };

        $queryBuilder = function (EntityRepository $repository, Options $options) {
            $qb = $repository->createQueryBuilder('a');
            foreach ($options['orderFields'] as $columnName) {
                $qb->addOrderBy(sprintf('a.%s', $columnName));
            }
            return $qb;
        };

        $resolver->setDefaults(array(
            'loader' => $loader,
            'query_builder' => $queryBuilder,
            'expanded' => false,
            'levelPrefix' => '--',
            'orderFields' => array('treeRoot', 'treeLeft'),
            'prefixAttributeName' => 'data-level-prefix',
            'treeLevelField' => 'treeLevel',
        ));

        $resolver->setAllowedTypes(array(
            'levelPrefix' => 'string',
            'orderFields' => 'array',
            'prefixAttributeName' => array('string', 'null'),
            'treeLevelField' => 'string',
        ));
    }
}
