### Custom prefix

If you want to provide custom prefix to options you can set `levelPrefix` option to callback that will be invoked for each option and it should return label.

```php
use Yavin\Symfony\Form\Type\TreeType;

public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('category', TreeType::class, [
        'class' => Category::class, // tree class
        'levelPrefix' => function ($label, $level, $data) {
            //$label - original label
            //$level - tree level
            //$data - entity instance

            return 'your custom prefix';
        },
    ]);
}
```
