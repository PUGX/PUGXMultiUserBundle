MassilMultiUserBundle Documentation
==================================

MassilMultiUserBundle came by the need to use different types of users using only one fos_user service.
In practice it is an hack that forces FOSUser bundle through custom UserManager, controllers, and forms handlers.

It's just a lazy way to use for free most of the functionality of FOSUserBundle.

This bundle has been realized as a part of a real application that uses doctrine orm,
so for now it only supports the ORM db driver.

## Prerequisites

This version of the bundle requires Symfony dev-master and FOSUserBundle dev-master

[FOSUserBundle] (https://github.com/FriendsOfSymfony/FOSUserBundle)

## Installation

1. Download MassilMultiUserBundle
2. Enable the Bundle
3. Create your Entities
4. Configure the FOSUserBundle (MassilMultiUserBundle params)
5. Configure parameters for UserDiscriminator
6. Create your controllers
7. Using the User Manager


### 1. Download MassilMultiUserBundle

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
        new Massil\MultiUserBundle\MassilMultiUserBundle(),
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
use Massil\MultiUserBundle\Validator\Constraints\UniqueEntity;

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
use Massil\MultiUserBundle\Validator\Constraints\UniqueEntity;

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
see [Overriding Default FOSUserBundle Forms] (https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/overriding_forms.rst)

### 4. Configure the FOSUserBundle (MassilMultiUserBundle params)

Keep in mind that MassilMultiUserBundle overwrites user_class via UserDiscriminator
but it does it only in controllers and forms handlers; in the other cases (command, sonata integration, etc)
it still uses the user_class configured in the config.

``` yaml
# Acme/UserBundle/Resources/config/config.yml
fos_user:
    db_driver: orm
    firewall_name: main
    user_class: Acme\UserBundle\Entity\User
    service:
        user_manager: massil_user_manager
```

**Note:**
> Acme\UserBundle\Entity\User must be an abstract class, because you don't have to use it.
In fact is the discriminator that has responsibility to get the user class depending on context.

### 5. Configure the MassilMultiUserBundle

``` yaml
# Acme/UserBundle/Resources/config/config.yml

massil_multi_user:
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

RegistrationUserOneController

``` php
<?php

namespace Acme\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RegistrationUserOneController extends Controller
{
    public function registerAction()
    {
        return $this->container
                    ->get('massil_multi_user.registration_manager')
                    ->register('Acme\UserBundle\Entity\UserOne');
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
                    ->get('massil_multi_user.registration_manager')
                    ->register('Acme\UserBundle\Entity\UserTwo');
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

**Note:**

> For now only registration and profile form factories are wrapped; 
if you need creat a custom FormType you have to inject the discriminator.

### 7. Using the User Manager

Creating a new UserOne:

``` php
$discriminator = $this->container->get('massil_user.manager.user_discriminator');
$discriminator->setClass('Acme\UserBundle\Entity\UserOne');

$userManager = $this->container->get('massil_user_manager');

$userOne = $userManager->createUser();

$userOne->setUsername('admin');
$userOne->setEmail('admin@mail.com');
$userOne->setPlainPassword('123456');
$userOne->setEnabled(true);

$userManager->updateUser($userOne, true);
```
