# Symfony Form Tree extension
This extension provide displaying doctrine tree entity types in synfony forms. It add a prefix to option names in select list that indicates tree level.

```html
<select name="field-name" data-level-prefix="--">
    <option value="">root node 1</option>
    <option value="">root node 2</option>
    <option value="">--node level 1</option>
    <option value="">----node level 2</option>
    <option value="">--another node level 1</option>
</select>
```

## Usage
Register form type as service in symfony config
```
services:
    yavin.symfony.form.tree:
        class: "Yavin\\Symfony\\Form\\Type\\TreeType"
        tags:
            - { name: form.type, alias: y_tree }
```

Then you can use this as it in your form types
```php
namespace Acme\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('categories', 'y_tree', array(
                'class' => 'Acme\\DemoBundle\\Entity\\Category',
            ))
        ;
    }

    public function getName()
    {
        return 'acme_page';
    }
}
```

Extension asume that your tree entities have tree properties named: `treeLevel`, `treeRoot`, `treeLeft`.
If your entities have different names, just pass it as options to service like that:
```
services:
    yavin.symfony.form.tree:
        class: "Yavin\\Symfony\\Form\\Type\\TreeType"
        arguments:
            -
                treeLevelField: "lvl"
        tags:
            - { name: form.type, alias: y_tree }
```


## Options
This is the list of options you can pass in constructor like in above example.

* `levelPrefix`, default: `--` - prefix string for level
* `treeLevelField`, default: `treeLevel` - tree level field name
* `prefixAttributeName`, default: `data-level-prefix` - attribute name that is added to select field that holds prefix string. Can be usefull for some javascript widgets.
* `orderColumns`, default: `array('treeRoot', 'treeLeft')` - sorting columns names

## Type guesser
You can also add type guesser, so tree type entities can be auto detected.
```
services:
    yavin.symfony.form.tree_guesser:
        class: "Yavin\\Symfony\\Form\\Type\\TreeTypeGuesser"
        arguments: [ "@doctrine.orm.entity_manager", "@annotation_reader" ]
        tags:
            - { name: form.type_guesser }
```
Then You can ommit field type and class option:
```php
namespace Acme\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('categories')
        ;
    }

    public function getName()
    {
        return 'acme_page';
    }
}
```
