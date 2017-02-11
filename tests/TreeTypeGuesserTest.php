<?php

namespace Yavin\Symfony\Form\Type\Tests;

use Gedmo\Tree\TreeListener;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Yavin\Symfony\Form\Type\Tests\Fixtures\TestBase;
use Yavin\Symfony\Form\Type\TreeType;
use Yavin\Symfony\Form\Type\TreeTypeGuesser;

class TreeTypeGuesserTest extends TestBase
{
    /**
     * @var TreeTypeGuesser
     */
    protected $treeTypeGuesser;

    public function setUp()
    {
        parent::setUp();

        $this->manager->getEventManager()->addEventSubscriber(new TreeListener());
    }

    protected function createFormFactoryBuilder()
    {
        $this->treeTypeGuesser = new TreeTypeGuesser($this->managerRegistry);

        return parent::createFormFactoryBuilder()
            ->addTypeGuesser($this->treeTypeGuesser);
    }

    public function testEntityIsTree()
    {
        /** @var TypeGuess $guess */
        $guess = $this->treeTypeGuesser->guessType(self::POST_CLASS, 'category');

        $this->assertInstanceOf('Symfony\Component\Form\Guess\TypeGuess', $guess);
        $this->assertEquals(TreeType::class, $guess->getType());

        $guessOptions = $guess->getOptions();
        $this->assertEquals(self::CATEGORY_CLASS, $guessOptions['class']);
        $this->assertEquals(Guess::VERY_HIGH_CONFIDENCE, $guess->getConfidence());
        $this->assertFalse($guessOptions['multiple']);
    }

    public function testEntityIsNotATree()
    {
        $guess = $this->treeTypeGuesser->guessType(self::POST_CLASS, 'name');
        $this->assertNull($guess);

        $guess = $this->treeTypeGuesser->guessType(self::POST_CLASS, 'tags');
        $this->assertNull($guess);
    }

    public function testSuperclassIsATree()
    {
        /** @var TypeGuess $guess */
        $guess = $this->treeTypeGuesser->guessType(self::POST_CLASS, 'sections');

        $this->assertInstanceOf('Symfony\Component\Form\Guess\TypeGuess', $guess);
        $this->assertEquals(TreeType::class, $guess->getType());

        $guessOptions = $guess->getOptions();
        $this->assertEquals(self::SECTION_CLASS, $guessOptions['class']);
        $this->assertEquals(Guess::VERY_HIGH_CONFIDENCE, $guess->getConfidence());
        $this->assertTrue($guessOptions['multiple']);
    }

    public function testGuessMaxLength()
    {
        $this->assertNull($this->treeTypeGuesser->guessMaxLength(self::POST_CLASS, 'category'));
    }

    public function testGuessPattern()
    {
        $this->assertNull($this->treeTypeGuesser->guessPattern(self::POST_CLASS, 'category'));
    }

    public function testIntegration()
    {
        $formBuilder = $this->formFactory->createBuilder(FormType::class, null, [
            'data_class' => self::POST_CLASS,
        ]);

        $formBuilder->add('category');
        $form = $formBuilder->getForm();

        $this->assertInstanceOf(
            TreeType::class,
            $form->get('category')->getConfig()->getType()->getInnerType()
        );
    }
}
