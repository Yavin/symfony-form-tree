<?php

namespace Yavin\Symfony\Form\Type\Tests\Fixtures;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Yavin\Symfony\Form\Type\TreeType;

class TreeTypeTest extends TestBase
{
    public function testTextProperties()
    {
        $treeType = new TreeType();

        $this->assertEquals(EntityType::class, $treeType->getParent());
    }

    public function testRequiredClassOption()
    {
        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\MissingOptionsException');
        $this->formFactory->createNamed('category', TreeType::class);
    }

    public function testPrefixAndPrefixAttribute()
    {
        $formView = $this->formFactory->createNamed('category', TreeType::class, null, [
            'class' => self::CATEGORY_CLASS,
        ])->createView();

        $this->assertArrayHasKey('attr', $formView->vars);
        $this->assertArrayHasKey('data-level-prefix', $formView->vars['attr']);
        $this->assertEquals('-', $formView->vars['attr']['data-level-prefix']);

        $formView = $this->formFactory->createNamed('category', TreeType::class, null, [
            'class' => self::CATEGORY_CLASS,
            'levelPrefix' => '~',
            'prefixAttributeName' => 'data-lorem',
        ])->createView();

        $this->assertEquals('~', $formView->vars['attr']['data-lorem']);
    }

    public function testDefaultOrderColumnsAddedToQueryBuilder()
    {
        $form = $this->formFactory->createNamed('category', TreeType::class, null, [
            'class' => self::CATEGORY_CLASS,
        ]);

        /** @var \Doctrine\ORM\QueryBuilder|\Closure $queryBuilder */
        $queryBuilder = $form->getConfig()->getOption('query_builder');

        //symfony < 2.7
        if ($queryBuilder instanceof \Closure) {

            $entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                ->disableOriginalConstructor()
                ->getMock();

            $queryBuilderMock = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                ->disableOriginalConstructor()
                ->getMock();

            $entityRepository
                ->expects($this->any())
                ->method('createQueryBuilder')
                ->with('a')
                ->willReturn($queryBuilderMock);

            $queryBuilderMock
                ->expects($this->once())
                ->method('addOrderBy')
                ->with('a.treeLeft', 'asc');

            $queryBuilder = $queryBuilder($entityRepository);
        }
        //symfony >= 2.7
        else {
            $orderBy = $queryBuilder->getDQLPart('orderBy');
            $this->assertCount(1, $orderBy);

            $parts = $orderBy[0]->getParts();
            $this->assertEquals($parts[0], 'a.treeLeft asc');
        }

        $this->assertInstanceOf('\Doctrine\ORM\QueryBuilder', $queryBuilder);
    }

    public function testOrderColumnsAddedToQueryBuilder()
    {
        $form = $this->formFactory->createNamed('category', TreeType::class, null, [
            'class' => self::CATEGORY_CLASS,
            'orderFields' => [
                'treeRight' => 'desc',
                'treeLevel' => 'asc',
            ],
        ]);

        /** @var \Doctrine\ORM\QueryBuilder|\Closure $queryBuilder */
        $queryBuilder = $form->getConfig()->getOption('query_builder');

        //symfony < 2.7
        if ($queryBuilder instanceof \Closure) {

            $entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                ->disableOriginalConstructor()
                ->getMock();

            $queryBuilderMock = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                ->disableOriginalConstructor()
                ->getMock();

            $entityRepository
                ->expects($this->any())
                ->method('createQueryBuilder')
                ->with('a')
                ->willReturn($queryBuilderMock);

            $queryBuilderMock
                ->expects($this->at(0))
                ->method('addOrderBy')
                ->with('a.treeRight', 'desc');

            $queryBuilderMock
                ->expects($this->at(1))
                ->method('addOrderBy')
                ->with('a.treeLevel', 'asc');

            $queryBuilder = $queryBuilder($entityRepository);
        } else {
            $orderBy = $queryBuilder->getDQLPart('orderBy');
            $this->assertCount(2, $orderBy);

            $parts = $orderBy[0]->getParts();
            $this->assertEquals($parts[0], 'a.treeRight desc');
            $parts = $orderBy[1]->getParts();
            $this->assertEquals($parts[0], 'a.treeLevel asc');
        }

        $this->assertInstanceOf('\Doctrine\ORM\QueryBuilder', $queryBuilder);
    }

    public function testTreeLevel()
    {
        $categoryClass = self::CATEGORY_CLASS;

        /** @var Category $category */
        $category = new $categoryClass();
        $category->setId(3);
        $category->setName('Lorem');
        $this->manager->persist($category);

        /** @var Category $category2 */
        $category2 = new $categoryClass();
        $category2->setId(5);
        $category2->setName('Ipsum');
        $category2->setParent($category);
        $this->manager->persist($category2);

        /** @var Category $category3 */
        $category3 = new $categoryClass();
        $category3->setId(6);
        $category3->setName('Dolor');
        $category3->setParent($category2);
        $this->manager->persist($category3);

        $this->manager->flush();

        $formView = $this->formFactory->createNamed('category', TreeType::class, $category, [
            'class' => self::CATEGORY_CLASS,
        ])->createView();

        $this->assertCount(3, $formView->vars['choices']);
        reset($formView->vars['choices']);
        $this->assertEquals('Lorem', current($formView->vars['choices'])->label);
        $this->assertEquals('-Ipsum', next($formView->vars['choices'])->label);
        $this->assertEquals('--Dolor', next($formView->vars['choices'])->label);
    }
}
