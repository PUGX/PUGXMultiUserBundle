##Newton-Labs Version

This version has been modified to resolve the symfony bug with [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle/issues/2048).

Install this version:
``` bash
$ php composer.phar require fcpauldiaz/multi-user-bundle
```

I have added a show action in the profile controller included in this bundle. See the [commit](https://github.com/Newton-Labs/PUGXMultiUserBundle/commit/a6c07c11c832db2b7a8074520e1536d6ba73bcd1)

Nothing else has been modified.

PUGXMultiUserBundle
===================

The PUGXMultiUserBundle extends FOSUserBundle adding the feature to handle users of different types.
For now only supports the ORM db driver and is based on the doctrine2 inheritance.

[![Latest Stable Version](https://poser.pugx.org/pugx/multi-user-bundle/v/stable.png)](https://packagist.org/packages/pugx/multi-user-bundle) [![Total Downloads](https://poser.pugx.org/pugx/multi-user-bundle/downloads.png)](https://packagist.org/packages/pugx/multi-user-bundle) [![Latest Unstable Version](https://poser.pugx.org/pugx/multi-user-bundle/v/unstable.png)](https://packagist.org/packages/pugx/multi-user-bundle) [![License](https://poser.pugx.org/pugx/multi-user-bundle/license.png)](https://packagist.org/packages/pugx/multi-user-bundle)

Documentation
-------------

[Read the Documentation](https://github.com/PUGX/PUGXMultiUserBundle/blob/master/Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](https://github.com/PUGX/PUGXMultiUserBundle/blob/master/Resources/doc/index.md).

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE

About
-----

PUGXMultiUserBundle is a [PUGX](https://github.com/PUGX) initiative.


Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/PUGX/PUGXMultiUserBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
with FOSUser installed, to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
