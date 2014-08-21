<?php

namespace Yavin\Symfony\Form\Type\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Section extends SectionSuperclass
{
    /**
     * @var Category[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\OrderBy({"treeLeft" = "ASC"})
     */
    private $children;
}
