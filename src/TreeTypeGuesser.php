<?php

namespace Yavin\Symfony\Form\Type;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Gedmo\Tree\TreeListener;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class TreeTypeGuesser extends DoctrineOrmTypeGuesser
{
    const TREE_ANNOTATION = '\\Gedmo\\Mapping\\Annotation\\Tree';

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if (!$this->isTree($class, $property)) {
            return;
        }

        $metadata = $this->getSingleMetadata($class);
        $associationMapping = $metadata->getAssociationMapping($property);
        $multiple = $metadata->isCollectionValuedAssociation($property);

        return new TypeGuess(
            TreeType::class,
            ['class' => $associationMapping['targetEntity'], 'multiple' => $multiple],
            Guess::VERY_HIGH_CONFIDENCE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern($class, $property)
    {

    }

    /**
     * @param string $class
     * @return ClassMetadataInfo
     */
    protected function getSingleMetadata($class)
    {
        $metadata = parent::getMetadata($class);

        if (!empty($metadata)) {
            return $metadata[0];
        }
    }

    protected function isTree($class, $property)
    {
        $metadata = $this->getSingleMetadata($class);

        if (!$metadata->hasAssociation($property)) {
            return false;
        }

        $associationMapping = $metadata->getAssociationMapping($property);
        $targetEntityClass = $associationMapping['targetEntity'];

        $manager = $this->registry->getManagerForClass($targetEntityClass);
        $listeners = $manager->getEventManager()->getListeners();

        if (empty($listeners['loadClassMetadata'])) {
            return false;
        }

        foreach ($listeners['loadClassMetadata'] as $listener) {
            if ($listener instanceof TreeListener) {
                try {
                    $strategy = $listener->getStrategy($manager, $targetEntityClass);
                } catch (\Gedmo\Exception $e) {
                    return false;
                }
                if (!empty($strategy)) {
                    return true;
                }
            }
        }

        return false;
    }
}
