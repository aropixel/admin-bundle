### Entity Customization

The AropixelAdminBundle allows you to customize its base entities (User, Image, File, etc.) to add properties or modify their behavior.

This system relies on the use of Doctrine's `MappedSuperclass` and `ResolveTargetEntityListener`.

#### 1. Extending an Entity

To add fields to a bundle entity, you must create an entity in your application that inherits from the bundle's entity.

For example, to add a phone number to the `User` entity:

```php
// src/Entity/User.php
namespace App\Entity;

use Aropixel\AdminBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_user')]
class User extends BaseUser
{
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }
}
```

#### 2. Configuring the Bundle

Once your entity is created, you must inform the bundle to use your class instead of its own. This is done in the `config/packages/aropixel_admin.yaml` configuration file:

```yaml
aropixel_admin:
    entities:
        Aropixel\AdminBundle\Entity\UserInterface: App\Entity\User
```

The bundle will then handle:
1. Replacing all relations to `UserInterface` with your `App\Entity\User` class.
2. Not creating the bundle's default table for the `User` entity, as it is replaced by yours.

#### 3. Customizing the Form

If you add properties, you will likely want to modify the editing form in the administration.

The recommended method is to use a Symfony **Form Extension**, which avoids having to rewrite the entire form.

```php
// src/Form/Extension/UserTypeExtension.php
namespace App\Form\Extension;

use Aropixel\AdminBundle\Form\Type\UserType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('phoneNumber', TextType::class, [
            'label' => 'Phone',
            'required' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [UserType::class];
    }
}
```

If you need total control, you can also completely replace the form class in the configuration:

```yaml
aropixel_admin:
    forms:
        Aropixel\AdminBundle\Entity\UserInterface: App\Form\Admin\CustomUserType
```

#### Customizable Entities List

Here are the interfaces you can configure in the `entities` section:

| Interface | Default Class |
| --- | --- |
| `Aropixel\AdminBundle\Entity\UserInterface` | `Aropixel\AdminBundle\Entity\User` |
| `Aropixel\AdminBundle\Entity\ImageInterface` | `Aropixel\AdminBundle\Entity\Image` |
| `Aropixel\AdminBundle\Entity\FileInterface` | `Aropixel\AdminBundle\Entity\File` |
| `Aropixel\AdminBundle\Entity\UserImageInterface` | `Aropixel\AdminBundle\Entity\UserImage` |
