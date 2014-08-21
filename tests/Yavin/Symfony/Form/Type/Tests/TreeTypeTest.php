<?php

namespace Yavin\Symfony\Form\Type\Tests\Fixtures;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\Options;
use Yavin\Symfony\Form\Type\TreeType;

class TreeTypeTest extends TestBase
{
    public function testTextProperties()
    {
        $treeType = new TreeType();

        $this->assertEquals('y_tree', $treeType->getName());
        $this->assertEquals('entity', $treeType->getParent());
    }

    public function testRequiredClassOption()
    {
        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\MissingOptionsException');
        $this->formFactory->createNamed('category', 'y_tree');
    }

    public function testPrefixAndPrefixAttribute()
    {
        $formView = $this->formFactory->createNamed('category', 'y_tree', null, array(
            'class' => self::CATEGORY_CLASS,
        ))->createView();

        $this->assertArrayHasKey('attr', $formView->vars);
        $this->assertArrayHasKey('data-level-prefix', $formView->vars['attr']);
        $this->assertEquals('--', $formView->vars['attr']['data-level-prefix']);

        $formView = $this->formFactory->createNamed('category', 'y_tree', null, array(
            'class' => self::CATEGORY_CLASS,
            'levelPrefix' => '~',
            'prefixAttributeName' => 'data-lorem',
        ))->createView();

        $this->assertEquals('~', $formView->vars['attr']['data-lorem']);
    }

    public function testOrderColumnsPassedToQueryBuilderCallback()
    {
        $test = $this;

        $this->formFactory->createNamed('category', 'y_tree', null, array(
            'class' => self::CATEGORY_CLASS,
            'orderFields' => array('root', 'left'),
            'query_builder' => function(EntityRepository $repository, Options $options) use ($test) {
                $test->assertEquals(array('root', 'left'), $options['orderFields']);
                return $repository->createQueryBuilder('a'); //to not throw exception
            }
        ));
    }

    public function testOrderColumnsAddedToQueryBuilder()
    {
        $form = $this->formFactory->createNamed('category', 'y_tree', null, array(
            'class' => self::CATEGORY_CLASS,
        ));

        /** @var \Closure $queryBuilderCallback */
        $queryBuilderCallback = $form->getConfig()->getOption('query_builder');
        $this->assertInstanceOf('\Closure', $queryBuilderCallback);

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder
            ->expects($this->at(0))
            ->method('addOrderBy')
            ->with('a.root')
            ->willReturnSelf();

        $queryBuilder
            ->expects($this->at(1))
            ->method('addOrderBy')
            ->with('a.left')
            ->willReturnSelf();

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);

        $options = $this->getMock('Symfony\Component\OptionsResolver\Options');
        $options->expects($this->once())
            ->method('offsetGet')
            ->with('orderFields')
            ->willReturn(array('root', 'left'));

        $this->assertEquals($queryBuilder, $queryBuilderCallback($repository, $options));
    }

    public function testTreeLevel()
    {
        $categoryClass = self::CATEGORY_CLASS;
        /** @var Category $category */
        $category = new $categoryClass();
        $category->setId(3);
        $category->setName('Lorem');
        $category->setTreeLevel(4);
        $this->manager->persist($category);
        /** @var Category $category2 */
        $category2 = new $categoryClass();
        $category2->setId(5);
        $category2->setName('Ipsum');
        $category2->setTreeLevel(2);
        $this->manager->persist($category2);

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder
            ->expects($this->any())
            ->method('addOrderBy')
            ->willReturnSelf();

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->setMethods(array('execute'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('execute')
            ->willReturn(array($category, $category2));

        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $formView = $this->formFactory->createNamed('category', 'y_tree', array($category), array(
            'class' => self::CATEGORY_CLASS,
            'query_builder' => function() use($queryBuilder) {
                return $queryBuilder;
            }
        ))->createView();

        $this->assertCount(2, $formView->vars['choices']);
        $this->assertEquals('--------Lorem', $formView->vars['choices'][3]->label);
        $this->assertEquals('----Ipsum', $formView->vars['choices'][5]->label);
    }
}
