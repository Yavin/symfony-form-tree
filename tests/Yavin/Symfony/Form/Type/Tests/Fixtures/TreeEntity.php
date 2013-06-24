<?php

namespace Yavin\Symfony\Form\Type\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @Gedmo\Tree(type="nested")
 */
class TreeEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="tree_left", type="integer")
     * @Gedmo\TreeLeft
     */
    public $treeLeft;

    /**
     * @var integer
     *
     * @ORM\Column(name="tree_right", type="integer")
     * @Gedmo\TreeRight
     */
    public $treeRight;

    /**
     * @var integer
     *
     * @Gedmo\TreeLevel
     * @ORM\Column(name="tree_level", type="integer")
     */
    public $treeLevel;

    /**
     * @var integer
     *
     * @Gedmo\TreeRoot
     * @ORM\Column(name="tree_root", type="integer", nullable=true)
     */
    public $treeRoot;

    /**
     * @var Category
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="TreeEntity", inversedBy="treeChildren")
     * @ORM\JoinColumn(name="tree_parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    public $treeParent;

    /**
     * @var Category
     *
     * @ORM\OneToMany(targetEntity="TreeEntity", mappedBy="treeParent")
     * @ORM\OrderBy({"treeLeft" = "ASC"})
     */
    public $treeChildren;


    public function __construct($id, $name, $treeParent)
    {
        $this->id = $id;
        $this->name = $name;
        $this->treeParent = $treeParent;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}

