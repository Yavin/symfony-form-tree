<?php

namespace Yavin\Symfony\Form\Type\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Post
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * @var Section[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Section")
     */
    private $sections;

    /**
     * @var Tag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Tag")
     */
    private $tags;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->tags= new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Yavin\Symfony\Form\Type\Tests\Fixtures\Section[] $sections
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Yavin\Symfony\Form\Type\Tests\Fixtures\Section[]
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Yavin\Symfony\Form\Type\Tests\Fixtures\Tag[] $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Yavin\Symfony\Form\Type\Tests\Fixtures\Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }
}
