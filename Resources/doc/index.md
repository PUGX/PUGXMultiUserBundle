PUGXMultiUserBundle Documentation
==================================

PUGXMultiUserBundle came by the need to have users with different attributes using only one fos_user service.
In practice it is an hack that forces FOSUser bundle through custom UserManager, controllers, and forms handlers.

It's just a lazy way to use for free most of the functionality of FOSUserBundle.

This bundle has been realized as a part of a real application that uses doctrine orm,
so for now it only supports the ORM db driver.

## Prerequisites

This version of the bundle requires Symfony dev-master and FOSUserBundle dev-master

[FOSUserBundle] (https://github.com/FriendsOfSymfony/FOSUserBundle)

## Installation

1. Download PUGXMultiUserBundle
2. Enable the Bundle
3. Create your Entities
4. Configure the FOSUserBundle (PUGXMultiUserBundle params)
5. Configure parameters for UserDiscriminator
6. Create your controllers
7. Overriding forms
8. Using the User Manager


### 1. Download PUGXMultiUserBundle

**Using composer**

Add the following lines in your composer.json:

```
    {
        "require": {
            "friendsofsymfony/user-bundle": "2.0.*@dev",
            "pugx/multi-user-bundle": "3.0.*@dev"
        }
    }

```

Now, run the composer to download the bundle:

``` bash
    $ php composer.phar update pugx/multi-user-bundle
```


### 2. Enable the bundle

Enable the bundles in the kernel:

``` php
    <?php
    // app/AppKernel.php
    
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new PUGX\MultiUserBundle\PUGXMultiUserBundle(),
            new FOS\UserBundle\FOSUserBundle(),
        );
    }
```

### 3. Create your Entities

Create entities using Doctrine2 inheritance.

Abstract User that directly extends the model FOS\UserBundle\Model\User

``` php
    <?php
    
    namespace Acme\UserBundle\Entity;
    
    use Doctrine\ORM\Mapping as ORM;
    use FOS\UserBundle\Model\User as BaseUser;
    
    /**
     * @ORM\Entity
     * @ORM\Table(name="user")
     * @ORM\InheritanceType("JOINED")
     * @ORM\DiscriminatorColumn(name="type", type="string")
     * @ORM\DiscriminatorMap({"user_one" = "UserOne", "user_two" = "UserTwo"})
     *
     */
    abstract class User extends BaseUser
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;
    }
```

UserOne

``` php
    <?php
    
    namespace Acme\UserBundle\Entity;
    
    use Doctrine\ORM\Mapping as ORM;
    use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;
    
    /**
     * @ORM\Entity
     * @ORM\Table(name="user_one")
     * @UniqueEntity(fields = "username", targetClass = "Acme\UserBundle\Entity\User", message="fos_user.username.already_used")
     * @UniqueEntity(fields = "email", targetClass = "Acme\UserBundle\Entity\User", message="fos_user.email.already_used")
     */
    class UserOne extends User
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;
    }
```

UserTwo

``` php
    <?php
    
    namespace Acme\UserBundle\Entity;
    
    use Doctrine\ORM\Mapping as ORM;
    use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;
    
    /**
     * @ORM\Entity
     * @ORM\Table(name="user_two")
     * @UniqueEntity(fields = "username", targetClass = "Acme\UserBundle\Entity\User", message="fos_user.username.already_used")
     * @UniqueEntity(fields = "email", targetClass = "Acme\UserBundle\Entity\User", message="fos_user.email.already_used")
     */
    class UserTwo extends User
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;
    }
```

You must also create forms for your entities:
see [Overriding Default FOSUserBundle Forms] (https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/overriding_forms.md)

### 4. Configure the FOSUserBundle (PUGXMultiUserBundle params)

Keep in mind that PUGXMultiUserBundle overwrites user_class via UserDiscriminator
but it does it only in controllers and forms handlers; in the other cases (command, sonata integration, etc)
it still uses the user_class configured in the config.

``` yaml
    # Acme/UserBundle/Resources/config/config.yml
    fos_user:
        db_driver: orm
        firewall_name: main
        user_class: Acme\UserBundle\Entity\User
        service:
            user_manager: pugx_user_manager
```

**Note:**
> Acme\UserBundle\Entity\User must be an abstract class, because you don't have to use it.
In fact is the discriminator that has responsibility to get the user class depending on context.

### 5. Configure the PUGXMultiUserBundle

```yaml

    # Acme/UserBundle/Resources/config/config.yml
    
    pugx_multi_user:
      users:
        user_one:
            entity: 
              class: Acme\UserBundle\Entity\UserOne
    #          factory: 
            registration:
              form: 
                type: Acme\UserBundle\Form\Type\RegistrationUserOneFormType
                name: fos_user_registration_form
                validation_groups:  [Registration, Default]
              template: AcmeUserBundle:Registration:user_one.form.html.twig
            profile:
              form:
                type: Acme\UserBundle\Form\Type\ProfileUserOneFormType
                name: fos_user_profile_form
                validation_groups:  [Profile, Default]
    #         profile template is optional
              template: AcmeUserBundle:Profile:user_one.form.html.twig
    #       options- is optional for passing array of options to templates
            options:            
        user_two:
            entity: 
              class: Acme\UserBundle\Entity\UserTwo
            registration:
              form: 
                type: Acme\UserBundle\Form\Type\RegistrationUserTwoFormType
              template: AcmeUserBundle:Registration:user_two.form.html.twig
            profile:
              form: 
                type: Acme\UserBundle\Form\Type\ProfileUserTwoFormType
              template: AcmeUserBundle:Profile:user_one.form.html.twig
```


### 6. Create your controllers

#### Route configuration

``` yaml
# Acme/UserBundle/Resources/config/routing.yml

user_one_registration:
    pattern:  /register/user-one
    defaults: { _controller: AcmeUserBundle:RegistrationUserOne:register }

user_two_registration:
    pattern:  /register/user-two
    defaults: { _controller: AcmeUserBundle:RegistrationUserTwo:register }
```

**Note:**
> You have to disable the default route registration coming from FOSUser or you have to manage it for prevent incorrect registration 

#### Controllers

Sometimes your business logic requires that you render more than one template when you register a user. You can create an array of the necessary templates in the controller.  The array of templates is passed with the variable `$templates`.  It can then be rendered in your registration template (e.g., `AcmeUserBundle:Registration:user_one.form.html.twig` using the following snippet:

```twig

    {% for template in templates %}
        {% include template %}
    {% endfor %}
```

Just be sure to render all the necessary `fos_user` user fields in the resulting templates.

If you do not require multiple templates, the `$templates` variable should be omitted in the controller.

RegistrationUserOneController

``` php

    <?php

    namespace Acme\UserBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

        class RegistrationUserOneController extends Controller
        {
            public function registerAction()
            {
                if (condition1) {
                    $templates[] = 'AcmeUserBundle:Registration:first.form.html.twig';
                }
                 if (condition2) {
                    $templates[] = 'AcmeUserBundle:Registration:second.form.html.twig';
                }
                
               return $this->container
                        ->get('pugx_multi_user.registration_manager')
                        ->register('Acme\UserBundle\Entity\UserOne', $templates);
            }
        }
```

RegistrationUserTwoController

``` php

    <?php

    namespace Acme\UserBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class RegistrationUserTwoController extends Controller
    {
        public function registerAction()
        {
            return $this->container
                    ->get('pugx_multi_user.registration_manager')
                    ->register('Acme\UserBundle\Entity\UserTwo');
        }
    }
```

ProfileController

The ProfileController also allows the addition of templates as in the RegistrationController.  If additional templates are not required it is not necessary to extend the ProfileController.

``` php

    <?php

    namespace Acme\UserBundle\Controller;


    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    /**
    * @Route("/profile")
    */
    class ProfileController extends Controller
    {
        /**
         * 
         * @Route("/edit")
         */
        public function editAction()
        {
            $user = $this->getUser();
            $class = get_class($user);
            switch ($class) {
                case 'Acme\UserBundle\Entity\UserOne':
                    return $this->userOneProfileAction();
                    break;
                case 'Acme\UserBundle\Entity\UserTwo':
                    return $this->userTwoProfileAction();
                    break;
                default:
                    break;
        }

        private function userOneProfileAction()
        {
            //create optional template array here
            //omit $templates parameter below if not used
            return $this->container
                    ->get('pugx_multi_user.profile_manager')
                    ->edit('Acme\UserBundle\Entity\UserOne', $templates);
        }
    
        private function userTwoProfileAction()
        {
            //create optional template array here
            //omit $templates parameter below if not used
            return $this->container
                    ->get('pugx_multi_user.profile_manager')
                    ->edit('Acme\UserBundle\Entity\UserTwo', $templates);
        }
    }
```


**Note:**

> Remember to create the templates for registration form with correct routes

something like this, if you are extending fosub

```

    {% extends "FOSUserBundle::layout.html.twig" %}

    {% block fos_user_content %}
        {% trans_default_domain 'FOSUserBundle' %}

        <form action="{{ path('user_one_registration') }}" {{ form_enctype(form) }} method="POST">
        {{ form_widget(form) }}
        <div>
            <input type="submit" value="{{ 'registration.submit'|trans }}" />
        </div>
    </form>
    {% endblock fos_user_content %}
```

### 7. Overriding forms

**Note:**

> For now only registration and profile form factories are wrapped; 
if you need to create a custom FormType you have to inject the discriminator.

**Overriding FOS\UserBundle\Form\Type\RegistrationFormType, FOS\UserBundle\Form\Type\ProfileFormType forms**

You can override these forms as described in the FOSUserBundle documentation if you do __NOT__ use the `options:` option in the `pugx_multi_user:` section of `config.yml`.  If you intend to override those forms and are using  the `options:` option in the `pugx_multi_user:` section of `config.yml`, you __MUST__ instead override **PUGX\MultiUserBundle\Form\RegistrationFormType** and/or **PUGX\MultiUserBundle\Form\ProfileFormType**.


```php

    use PUGX\MultiUserBundle\Form\RegistrationFormType as BaseType;

    class NewRegistrationFormType extends BaseType
    {
    ...
    }
```

The options you configure in `config.yml` become available with `$this->options` in your new form.  For example:

if `config.yml` contains:

```yaml

    pugx_multi_user:
       user: user_one
            options:
                option1: true
                option2: false
```

Then in your form you can use `$this->options['option1']` to retrieve `true`. Here's an example from a form type's listener:

```php

        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
            $form = $event->getForm();
            if ($this->options['option1']) {
                $form->add('field1');
            };
            if ($this->options['option2']) {
                $form->add('field2');
            };
        });
```

### 8. Using the User Manager

Creating a new UserOne:

``` php

    $discriminator = $this->container->get('pugx_user.manager.user_discriminator');
    $discriminator->setClass('Acme\UserBundle\Entity\UserOne');

    $userManager = $this->container->get('pugx_user_manager');

    $userOne = $userManager->createUser();

    $userOne->setUsername('admin');
    $userOne->setEmail('admin@mail.com');
    $userOne->setPlainPassword('123456');
    $userOne->setEnabled(true);

    $userManager->updateUser($userOne, true);
```
