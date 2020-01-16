# ZfcRbac

[![Develop Branch Build Status](https://travis-ci.org/ZF-Commons/zfc-rbac.svg?branch=develop)](http://travis-ci.org/ZF-Commons/zfc-rbac)
[![Coverage Status](https://coveralls.io/repos/github/ZF-Commons/zfc-rbac/badge.svg?branch=develop)](https://coveralls.io/github/ZF-Commons/zfc-rbac?branch=develop)
[![Latest Stable Version](https://poser.pugx.org/zf-commons/zfc-rbac/v/stable)](https://packagist.org/packages/zf-commons/zfc-rbac)
[![Total Downloads](https://poser.pugx.org/zf-commons/zfc-rbac/downloads)](https://packagist.org/packages/zf-commons/zfc-rbac)
[![Latest Unstable Version](https://poser.pugx.org/zf-commons/zfc-rbac/v/unstable)](https://packagist.org/packages/zf-commons/zfc-rbac)
[![License](https://poser.pugx.org/zf-commons/zfc-rbac/license)](https://packagist.org/packages/zf-commons/zfc-rbac)
[![Join the chat at https://gitter.im/ZFCommons/zfc-rbac](https://badges.gitter.im/ZFCommons/zfc-rbac.svg)](https://gitter.im/ZFCommons/zfc-rbac?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

ZfcRbac is an access control library based on the RBAC permission model.

**Work In Progress**; *you are looking at the next version, for stable visit the master branch.*

## Requirements

- PHP 7.1 or higher

> If you are looking for older version of ZfcRbac, please refer to the 2.x branch.
> If you are using ZfcRbac 2.0, please upgrade to 3.0.

## Optional

- [DoctrineModule](https://github.com/doctrine/DoctrineModule): if you want to use some built-in role and permission providers.
- [Laminas\DeveloperTools](https://github.com/zendframework/Laminas\DeveloperTools): if you want to have useful stats added to
the Zend Developer toolbar.

## Upgrade

You can find an [upgrade guide](UPGRADE.md) to quickly upgrade your application from major versions of ZfcRbac.

## Installation

ZfcRbac only officially supports installation through Composer. For Composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

Install the module:

```sh
$ php composer.phar require zf-commons/zfc-rbac:^3.0
```

Enable the module by adding `ZfcRbac` key to your `application.config.php` file. Customize the module by copy-pasting
the `zfc_rbac.global.php.dist` file to your `config/autoload` folder.

## Documentation

The official documentation is available in the [/docs](docs/) folder.

You can also find some Doctrine entities in the [/data](data/) folder that will help you to more quickly take advantage
of ZfcRbac.

## Support

- File issues at https://github.com/ZF-Commons/zfc-rbac/issues.
- Ask questions in the [zf-common gitter](https://gitter.im/ZFCommons/zfc-rbac) chat.
