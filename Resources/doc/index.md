PUGXMultiUserBundle Documentation
==================================

PUGXMultiUserBundle came by the need to use different types of users using only one fos_user service.
In practice it is an hack that forces FOSUser bundle through custom UserManager, controllers, and forms handlers.

It's just a lazy way to use for free most of the functionality of FOSUserBundle.

This bundle has been realized as a part of a real application that uses doctrine orm,
so for now it only supports the ORM db driver.

!!! IMPORTANT !!!
=================
This version was heavily modified to decouple the controllers. !!!

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


### 1. Download PUGXMultiUserBundle

**Using composer**

Add the following lines in your composer.json:

```
{
    "require": {
        "pugx/multi-user-bundle": "master-dev"
    }
}

```

Now, run the composer to download the bundle:

``` bash
$ php composer.phar update pugx/multi-user-bundle
```


### 2. Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new PUGX\MultiUserBundle\PUGXMultiUserBundle(),
    );
}
```

### 3. Create your Entities

Create entities using Doctrine2 inheritance.

Abstract User that directly extends FOS\UserBundle\Entity\User

``` php
<?php

namespace Acme\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

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
see [Overriding Default FOSUserBundle Forms] (https://github.com/FriendsOfSymfony/FOSUserBundle/blob/1.1.0/Resources/doc/overriding_forms.md)

### 4. Configure the FOSUserBundle (PUGXMultiUserBundle params)

Keep in mind that PUGXMultiUserBundle overwrites user_class via UserDiscriminator
but it does it only in controllers and forms handlers; in the other cases (command, sonata integration, etc)
it still uses the user_class configured in the config.

``` yaml
# Acme/app/Resources/config/config.yml
fos_user:
    db_driver: orm
    firewall_name: main
    user_class: Acme\UserBundle\Entity\User
    service:
        user_manager: pugx_user_manager
    registration:
        form:
            handler: pugx_user_registration_form_handler
    profile:
        form:
            handler: pugx_user_profile_form_handler
```

### 5. Configure parameters for UserDiscriminator

``` yaml
# Acme/UserBundle/Resources/config/config.yml

parameters:
  pugx_user_discriminator_parameters:
    classes:
        user_one:
            entity: Acme\UserBundle\Entity\UserOne
            registration: Acme\UserBundle\Form\Type\RegistrationUserOneFormType
            profile: Acme\UserBundle\Form\Type\ProfileUserOneFormType
            factory:
        user_two:
            entity: Acme\UserBundle\Entity\UserTwo
            registration: Acme\UserBundle\Form\Type\RegistrationUserTwoFormType
            profile: Acme\UserBundle\Form\Type\ProfileUserTwoFormType
            factory:
```

If you need to pass custom options to the form (like a validation groups)

``` yaml
# Acme/UserBundle/Resources/config/config.yml

parameters:
  pugx_user_discriminator_parameters:
    classes:
        user_one:
            entity: Acme\UserBundle\Entity\UserOne
            registration: Acme\UserBundle\Form\Type\RegistrationUserOneFormType
            registration_options: 
                validation_groups: [Registration, Default]
            profile: Acme\UserBundle\Form\Type\ProfileUserOneFormType
            profile_options: 
                validation_groups: [Profile, Default]
            factory:
        user_two:
            entity: Acme\UserBundle\Entity\UserTwo
            registration: Acme\UserBundle\Form\Type\RegistrationUserTwoFormType
            profile: Acme\UserBundle\Form\Type\ProfileUserTwoFormType
            factory:
```

### 6. Create your controllers

Route configuration

``` yaml
# Acme/UserBundle/Resources/config/routing.yml

user_one_registration:
    pattern:  /register/user-one
    defaults: { _controller: AcmeUserBundle:RegistrationUserOne:register }

user_two_registration:
    pattern:  /register/user-two
    defaults: { _controller: AcmeUserBundle:RegistrationUserTwo:register }
```

You can disable the default route registration coming from FOSUser or you have to manage it for prevent incorrect registration

Controller

RegistrationUserOne

``` php
<?php

namespace Acme\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class RegistrationUserOne extends BaseController
{
    public function registerAction()
    {
        $handler = $this->container->get('pugx_multi_user.controller.handler');
        $discriminator = $this->container->get('pugx_user_discriminator');

        $return = $handler->registration('Acme\UserBundle\Entity\UserOne');
        $form = $discriminator->getRegistrationForm();

        if ($return instanceof RedirectResponse) {
            return $return;
        }
        
        //This is bad at the moment
        //Needs to be changed as binding the form twice is stupid
        //Any idea??
        if ('POST' === $request->getMethod()) {

            $form->bind($request);
        }
        return $this->container->get('templating')->renderResponse('AcmeUserBundle:Registration:user_one.form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
```

RegistrationUserTwo

``` php
<?php

namespace Acme\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class RegistrationUserTwo extends BaseController
{
    public function registerAction()
    {
        $handler = $this->container->get('pugx_multi_user.controller.handler');
        $discriminator = $this->container->get('pugx_user_discriminator');

        $return = $handler->registration('Acme\UserBundle\Entity\UserTwo');
        $form = $discriminator->getRegistrationForm();

        if ($return instanceof RedirectResponse) {
            return $return;
        }
        
        //This is bad at the moment
        //Needs to be changed as binding the form twice is stupid
        //Any idea??
        if ('POST' === $request->getMethod()) {

            $form->bind($request);
        }

        return $this->container->get('templating')->renderResponse('AcmeUserBundle:Registration:user_two.form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
```
