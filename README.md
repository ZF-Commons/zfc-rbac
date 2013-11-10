# ZfcRbac

[![Master Branch Build Status](https://secure.travis-ci.org/ZF-Commons/ZfcRbac.png?branch=master)](http://travis-ci.org/ZF-Commons/ZfcRbac)
[![Coverage Status](https://coveralls.io/repos/ZF-Commons/ZfcRbac/badge.png)](https://coveralls.io/r/ZF-Commons/ZfcRbac)
[![Latest Stable Version](https://poser.pugx.org/zf-commons/ZfcRbac/v/stable.png)](https://packagist.org/packages/zf-commons/ZfcRbac)
[![Total Downloads](https://poser.pugx.org/zf-commons/ZfcRbac/downloads.png)](https://packagist.org/packages/zf-commons/ZfcRbac)

ZfcRbac is an access control module for Zend Framework 2, based on the RBAC permission model.

## Requirements

- PHP 5.4 or higher
- [Zend Framework 2.2 or higher](http://www.github.com/zendframework/zf2)

## Optional

- [DoctrineModule](https://github.com/doctrine/DoctrineModule): if you want to use some built-in role and permission providers.
- [ZendDeveloperTools](https://github.com/zendframework/ZendDeveloperTools): if you want to have useful stats added to
the Zend Developer toolbar.

## Installation

ZfcRbac only officially supports installation through Composer. For Composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

Install the module:

```sh
$ php composer.phar require zf-commons/zfc-rbac:~1.0
```

Enable the module by adding `ZfcRbac` key to your `application.config.php` file. Customize the module by copy-pasting
the `zfc_rbac.global.php.dist` file to your `config/autoload` folder.

## Documentation

The official documentation has been moved to the [repository wiki](https://github.com/ZF-Commons/ZfcRbac/wiki).
