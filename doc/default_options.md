### Default options for all selects

If you pass some options as defaults to all `y_tree` fields, you should create [form type extension](http://symfony.com/doc/current/cookbook/form/create_form_type_extension.html):

```php
namespace Acme\DemoBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class YTreeTypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'y_file';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'treeLevelField' => 'lvl'
        ));
    }
}
```

and add service:

```xml
<service class="Acme\DemoBundle\Form\Extension\YTreeTypeExtension">
    <tag name="form.type_extension" alias="y_tree" />
</service>
```
