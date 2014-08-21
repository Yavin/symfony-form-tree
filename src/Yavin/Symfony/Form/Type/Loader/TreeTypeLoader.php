<?php

namespace Yavin\Symfony\Form\Type\Loader;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\OptionsResolver\Options;
use Yavin\Symfony\Form\Type\Exception\UnexpectedTypeException;

class TreeTypeLoader extends ORMQueryBuilderLoader
{
    public function __construct(Options $options, $queryBuilder, $manager = null, $class = null)
    {
        if ($queryBuilder instanceof \Closure) {
            if (!$manager instanceof EntityManager) {
                throw new UnexpectedTypeException($manager, 'Doctrine\ORM\EntityManager');
            }

            $queryBuilder = $queryBuilder($manager->getRepository($class), $options);
        }

        parent::__construct($queryBuilder, $manager, $class);
    }
}
