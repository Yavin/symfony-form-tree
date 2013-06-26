<?php

namespace Yavin\Symfony\Form\Type\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Gedmo\Tree\TreeListener;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yavin\Symfony\Form\Type\TreeType;
use Yavin\Symfony\Form\Type\TreeTypeExtension;
use Yavin\Symfony\Form\Type\TreeTypeGuesser;
use Yavin\Symfony\Form\Type\Tests\Fixtures\TreeEntity;
use Yavin\Symfony\Form\Type\Tests\Fixtures\NotTreeEntity;


class TreeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tree type entity class
     */
    const TREE_ENTITY_CLASS = 'Yavin\\Symfony\\Form\\Type\\Tests\\Fixtures\\TreeEntity';

    /**
     * Not three type entity class
     */
    const NOT_TREE_ENTITY_CLASS = 'Yavin\\Symfony\\Form\\Type\\Tests\\Fixtures\\NotTreeEntity';

    /**
     * name of form field type
     */
    const FORM_TYPE_NAME = 'y_tree';

    /**
     * @var TreeType
     */
    protected $treeType;

    /**
     * @var TreeTypeGuesser
     */
    protected $treeTypeGuesser;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EventManager
     */
    protected $evm;

    /**
     * @var FormFactoryInterf
     */
    protected $factory;


    public function setUp()
    {
        $this->checkRequirements();

        $this->em = $this->createTestEntityManager();
        $this->emRegistry = $this->createRegistryMock('default', $this->em);
        $this->evm = $this->em->getEventManager();
        $this->evm->addEventSubscriber(new TreeListener());

        $schemaTool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata(self::TREE_ENTITY_CLASS),
            $this->em->getClassMetadata(self::NOT_TREE_ENTITY_CLASS),
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

    public function testTreeElementsWithNoParents()
    {
        $this->createFormFactory();

        $entity1 = new TreeEntity(1, 'Foo', null);
        $entity2 = new TreeEntity(2, 'Bar', null);

        $this->persist(array($entity1, $entity2));

        $field = $this->factory->createNamed('name', self::FORM_TYPE_NAME, null, array(
            'em' => 'default',
            'class' => self::TREE_ENTITY_CLASS,
            'required' => false,
            'property' => 'name'
        ));

        $formViewChoices = $field->createView()->vars['choices'];
        $this->assertEquals($formViewChoices, array(
            1 => new ChoiceView($entity1, '1', 'Foo'),
            2 => new ChoiceView($entity2, '2', 'Bar'),
        ));
    }

    public function testTreeElementsWithParents()
    {
        $this->createFormFactory();

        $entity1 = new TreeEntity(1, 'Foo', null);
        $entity2 = new TreeEntity(2, 'Bar', $entity1);

        $this->persist(array($entity1, $entity2));

        $field = $this->factory->createNamed('name', self::FORM_TYPE_NAME, null, array(
            'em' => 'default',
            'class' => self::TREE_ENTITY_CLASS,
            'required' => false,
            'property' => 'name'
        ));

        $formViewChoices = $field->createView()->vars['choices'];
        $this->assertEquals($formViewChoices, array(
            1 => new ChoiceView($entity1, '1', 'Foo'),
            2 => new ChoiceView($entity2, '2', '--Bar'),
        ));
    }

    public function testTreeElementsThreeDeepTest()
    {
        $this->createFormFactory();

        $entity1 = new TreeEntity(1, 'Foo', null);
        $entity2 = new TreeEntity(2, 'Bar', $entity1);
        $entity3 = new TreeEntity(3, 'Baz', $entity2);

        $this->persist(array($entity1, $entity2, $entity3));

        $field = $this->factory->createNamed('name', self::FORM_TYPE_NAME, null, array(
            'em' => 'default',
            'class' => self::TREE_ENTITY_CLASS,
            'required' => false,
            'property' => 'name'
        ));

        $formViewChoices = $field->createView()->vars['choices'];
        $this->assertEquals($formViewChoices, array(
            1 => new ChoiceView($entity1, '1', 'Foo'),
            2 => new ChoiceView($entity2, '2', '--Bar'),
            3 => new ChoiceView($entity3, '3', '----Baz'),
        ));
    }

    public function testCustomPrefix()
    {
        $this->createFormFactory(array(
            'levelPrefix' => '+',
        ));

        $entity1 = new TreeEntity(1, 'Foo', null);
        $entity2 = new TreeEntity(2, 'Bar', $entity1);
        $entity3 = new TreeEntity(3, 'Baz', $entity2);

        $this->persist(array($entity1, $entity2, $entity3));

        $field = $this->factory->createNamed('name', self::FORM_TYPE_NAME, null, array(
            'em' => 'default',
            'class' => self::TREE_ENTITY_CLASS,
            'required' => false,
            'property' => 'name'
        ));

        $formViewChoices = $field->createView()->vars['choices'];
        $this->assertEquals($formViewChoices, array(
            1 => new ChoiceView($entity1, '1', 'Foo'),
            2 => new ChoiceView($entity2, '2', '+Bar'),
            3 => new ChoiceView($entity3, '3', '++Baz'),
        ));
    }

    public function testPrefixAttributeName()
    {
        $this->createFormFactory();

        $entity1 = new TreeEntity(1, 'Foo', null);
        $this->persist(array($entity1));

        $field = $this->factory->createNamed('name', self::FORM_TYPE_NAME, null, array(
            'em' => 'default',
            'class' => self::TREE_ENTITY_CLASS,
            'required' => false,
            'property' => 'name'
        ));

        $formViewAttributes = $field->createView()->vars['attr'];

        $this->assertEquals($formViewAttributes['data-level-prefix'], '--');
    }

    /**
     * @expectedException Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testInvalidTreeLevelField()
    {
        $this->createFormFactory(array(
            'treeLevel' => 'invalidName',
        ));

        $entity1 = new TreeEntity(1, 'Foo', null);
        $entity2 = new TreeEntity(2, 'Bar', $entity1);

        $this->persist(array($entity1, $entity2));

        $field = $this->factory->createNamed('name', self::FORM_TYPE_NAME, null, array(
            'em' => 'default',
            'class' => self::TREE_ENTITY_CLASS,
            'required' => false,
            'property' => 'name'
        ));
    }

    /**
     * @expectedException Doctrine\ORM\Query\QueryException
     */
    public function testInvalidSortFieldName()
    {
        $this->createFormFactory(array(
            'orderColumns' => array('invalidFoo'),
        ));

        $entity1 = new TreeEntity(1, 'Foo', null);
        $entity2 = new TreeEntity(2, 'Bar', $entity1);

        $this->persist(array($entity1, $entity2));

        $field = $this->factory->createNamed('name', self::FORM_TYPE_NAME, null, array(
            'em' => 'default',
            'class' => self::TREE_ENTITY_CLASS,
            'required' => false,
            'property' => 'name'
        ));

        $field->createView();
    }

    /**
     * @expectedException Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testInvalidOptionsType()
    {
        $this->createFormFactory(array(
            'levelPrefix' => 123, //integer
        ));
    }

    public function testQueryBuilder()
    {
        $this->createFormFactory();

        $optionsResolver = new OptionsResolver();
        $this->treeType->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve(array());

        $this->assertArrayHasKey('query_builder', $options);
        $this->assertInstanceOf('\Closure', $options['query_builder']);

        $repository = $this->em->getRepository(self::TREE_ENTITY_CLASS);
        $qb = $options['query_builder']($repository);

        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $qb);
    }

    public function testExpandedOptions()
    {
        $this->createFormFactory();

        $optionsResolver = new OptionsResolver();
        $this->treeType->setDefaultOptions($optionsResolver);

        $options = $optionsResolver->resolve(array());

        $this->assertFalse($options['expanded']);
    }

    public function testGetParent()
    {
        $this->createFormFactory();

        $this->assertSame($this->treeType->getParent(), 'entity');
    }

    public function testGetName()
    {
        $this->createFormFactory();

        $this->assertSame($this->treeType->getName(), 'y_tree');
    }

    protected function checkRequirements()
    {
        $classes = array(
            'PDO',
            'Doctrine\Common\Version',
            'Doctrine\DBAL\Platforms\MySqlPlatform',
            'Doctrine\ORM\EntityManager',
            'Symfony\Component\EventDispatcher\EventDispatcher',
        );

        foreach ($classes as $class) {
            if (!class_exists($class)) {
                $this->markTestSkipped(printf('class %s must be available', $class));
            }
        }

        if (!in_array('sqlite', \PDO::getAvailableDrivers())) {
            $this->markTestSkipped('This test requires SQLite support in your environment');
        }
    }

    protected function persist(array $entities)
    {
        foreach ($entities as $entity) {
            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    protected function createTestEntityManager($paths = array())
    {
        $config = new \Doctrine\ORM\Configuration();
        $config->setEntityNamespaces(array('SymfonyTestsDoctrine' => self::TREE_ENTITY_CLASS));
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('SymfonyTests\Doctrine');
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());

        $params = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        return EntityManager::create($params, $config);
    }

    protected function createRegistryMock($name, $em)
    {
        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())
                 ->method('getManager')
                 ->with($this->equalTo($name))
                 ->will($this->returnValue($em));

        return $registry;
    }

    protected function createFormFactory(array $options = array())
    {
        $this->treeType = new TreeType($options);

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addType($this->treeType)
            ->getFormFactory();
    }

    protected function getExtensions()
    {
        return array(
            new DoctrineOrmExtension($this->emRegistry),
        );
    }
}
