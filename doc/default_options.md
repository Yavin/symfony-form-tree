### Default options for all selects

If you pass some options as defaults to all `TreeType::class` fields, you should create [form type extension](http://symfony.com/doc/current/cookbook/form/create_form_type_extension.html):

```php
namespace Acme\DemoBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Yavin\Symfony\Form\Type\TreeType;

class YTreeTypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return TreeType::class;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional([
            'treeLevelField' => 'lvl'
        ]);
    }
}
```

and add service:

```xml
<service class="Acme\DemoBundle\Form\Extension\YTreeTypeExtension">
    <tag name="form.type_extension" extended_type="Yavin\Symfony\Form\Type\TreeType"/>
</service>
```
