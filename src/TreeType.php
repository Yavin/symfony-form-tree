<?php

namespace Yavin\Symfony\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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

    public function getParent()
    {
        return EntityType::class;
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
            if (is_callable($levelPrefix)) {
                $choice->label = $levelPrefix($choice->label, $level, $dataObject);
            } else {
                $choice->label = str_repeat($levelPrefix, $level) . $choice->label;
            }
        }

        if (is_string($levelPrefix) && !empty($options['prefixAttributeName'])) {
            $view->vars['attr'][$options['prefixAttributeName']] = $levelPrefix;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $queryBuilder = function (Options $options) {
            return function (EntityRepository $repository) use ($options) {
                $qb = $repository->createQueryBuilder('a');
                foreach ($options['orderFields'] as $columnName => $order) {
                    $qb->addOrderBy(sprintf('a.%s', $columnName), $order);
                }
                return $qb;
            };
        };

        $resolver->setDefaults([
            'query_builder' => $queryBuilder,
            'expanded' => false,
            'levelPrefix' => '-',
            'orderFields' => ['treeLeft' => 'asc'],
            'prefixAttributeName' => 'data-level-prefix',
            'treeLevelField' => 'treeLevel',
        ]);

        $resolver->setAllowedTypes('levelPrefix', ['string', 'callable']);
        $resolver->setAllowedTypes('orderFields', ['array']);
        $resolver->setAllowedTypes('prefixAttributeName', ['string', 'null']);
        $resolver->setAllowedTypes('treeLevelField', ['string']);
    }
}
