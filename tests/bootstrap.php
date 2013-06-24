<?php

define('VENDOR_PATH', realpath(__DIR__ . '/../../..'));

if (!is_file($autoloadFile = VENDOR_PATH.'/autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}

$loader = require $autoloadFile;

//add test namespace
$loader->add('Yavin\Symfony\Form\Type\Tests', __DIR__);

//register doctrine annotations
Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
    VENDOR_PATH.'/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
);

//register gedmo annotations
Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
    VENDOR_PATH.'/gedmo/doctrine-extensions/lib/Gedmo/Mapping/Annotation/All.php'
);
