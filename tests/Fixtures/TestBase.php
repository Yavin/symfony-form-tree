<?php

namespace Yavin\Symfony\Form\Type\Tests\Fixtures;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Gedmo\Tree\TreeListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Yavin\Symfony\Form\Type\TreeType;

class TestBase extends \PHPUnit_Framework_TestCase
{
    const POST_CLASS = 'Yavin\Symfony\Form\Type\Tests\Fixtures\Post';
    const CATEGORY_CLASS = 'Yavin\Symfony\Form\Type\Tests\Fixtures\Category';
    const SECTION_CLASS = 'Yavin\Symfony\Form\Type\Tests\Fixtures\Section';
    const TAG_CLASS = 'Yavin\Symfony\Form\Type\Tests\Fixtures\Tag';

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    public function setUp()
    {
        $this->checkEnviroment();

        $this->manager = DoctrineTestHelper::createTestEntityManager();
        $this->manager->getEventManager()->addEventSubscriber(new TreeListener());
        $this->managerRegistry = $this->createRegistryMock('default', $this->manager);
        $this->formFactory = $this->createFormFactoryBuilder()->getFormFactory();

        $schemaTool = new SchemaTool($this->manager);
        $classes = array(
            $this->manager->getClassMetadata(self::POST_CLASS),
            $this->manager->getClassMetadata(self::CATEGORY_CLASS),
            $this->manager->getClassMetadata(self::SECTION_CLASS),
        );

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception $e) {
        }
    }

    protected function createFormFactoryBuilder()
    {
        return Forms::createFormFactoryBuilder()
            ->addType(new EntityType($this->managerRegistry))
            ->addType(new TreeType());
    }

    protected function checkEnviroment()
    {
        if (!class_exists('Symfony\Component\Form\Form')) {
            $this->markTestSkipped('The "Form" component is not available');
        }

        if (!class_exists('Doctrine\DBAL\Platforms\MySqlPlatform')) {
            $this->markTestSkipped('Doctrine DBAL is not available.');
        }

        if (!class_exists('Doctrine\Common\Version')) {
            $this->markTestSkipped('Doctrine Common is not available.');
        }

        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('Doctrine ORM is not available.');
        }
    }

    protected function createRegistryMock($name, $em)
    {
        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->will($this->returnValue($em));

        $registry->expects($this->any())
            ->method('getManagers')
            ->willReturn(array($this->manager));

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($this->manager);

        return $registry;
    }
}
