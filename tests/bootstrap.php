<?php

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$loader->addPsr4('Yavin\Symfony\Form\Type\Tests\\', __DIR__);
