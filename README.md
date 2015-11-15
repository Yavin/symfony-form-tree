# Symfony Form Tree extension

[![Build Status](https://travis-ci.org/Yavin/symfony-form-tree.png?branch=master)](https://travis-ci.org/Yavin/symfony-form-tree)

This extension provide displaying doctrine tree entity types in synfony forms. It add a prefix to option names in select list that indicates tree level.

It is tested and should work with symfony 2.3-2.8

![](doc/example.png)

```html
<select name="..." data-level-prefix="-">
    <option value="1">Motors</option>
    <option value="2">Electronics</option>
    <option value="3">-Cell phones</option>
    <option value="4">--Samsung</option>
    <option value="5">-Computers</option>
    <option value="6">Fasion</option>
</select>
```

## Instalation
1. With composer.json
   ```
   composer require yavin/symfony-form-tree:0.3
   ```

2. Add services in your bundle services file `Resources/config/services.xml`:
   ```xml
   <service class="Yavin\Symfony\Form\Type\TreeType">
       <argument type="service" id="property_accessor"/>
       <tag name="form.type" alias="y_tree"/>
   </service>
   <service class="Yavin\Symfony\Form\Type\TreeTypeGuesser">
       <argument type="service" id="doctrine"/>
       <tag name="form.type_guesser"/>
   </service>
   ```

   or if you have `services.yml`:
   ```yml
   services:
       symfony.form.type.tree:
           class: Yavin\Symfony\Form\Type\TreeType
           arguments: [ "@property_accessor" ]
           tags:
               - { name: form.type, alias: y_tree }
       symfony.form.type_guesser.tree:
           class: Yavin\Symfony\Form\Type\TreeTypeGuesser
           arguments: [ "@doctrine" ]
           tags:
               - { name: form.type_guesser }
   ```
3. Then add field to tree model. In this example
    ```php
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('category'); //extension will guess field type

        //or this is full example with default options:

        $builder->add('category', 'y_tree', array(
            'class' => 'Acme\DemoBundle\Entity\Category', // tree class
            'levelPrefix' => '-',
            'orderFields' => array('treeLeft' => 'asc'),
            'prefixAttributeName' => 'data-level-prefix',
            'treeLevelField' => 'treeLevel',
        ));
    }
    ```

    This extension assume that in tree model You have `treeLeft` and `treeLevel` fields.
    It can be changed in field options.

    [Here](tests/Yavin/Symfony/Form/Type/Tests/Fixtures/Category.php) is example tree entity.

## Other
* [Custom, callback provided prefix](doc/custom_prefix.md)
* [Set default options to all tree select fields](doc/default_options.md)

## Lincense
[MIT](https://opensource.org/licenses/MIT)
