# ZfcRbac Module for Zend Framework 2 [![Master Branch Build Status](https://secure.travis-ci.org/ZF-Commons/ZfcRbac.png?branch=master)](http://travis-ci.org/ZF-Commons/ZfcRbac)

[![Latest Stable Version](https://poser.pugx.org/zf-commons/zfc-rbac/v/stable.png)](https://packagist.org/packages/zf-commons/zfc-rbac)

ZfcRbac is an access control module for Zend Framework 2 geared towards quick & easy setup. Getting access control
working should take you less than 5 minutes.

## Requirements
 - PHP 5.3 or higher
 - [Zend Framework 2](http://www.github.com/zendframework/zf2)

## Installation

Installation of ZfcRbac uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

#### Installation steps

  1. `cd my/project/directory`
  2. create a `composer.json` file with following contents:

     ```json
     {
         "require": {
             "zf-commons/zfc-rbac": "dev-master"
         }
     }
     ```
  3. install composer via `curl -s http://getcomposer.org/installer | php` (on windows, download
     http://getcomposer.org/installer and execute it with PHP)
  4. run `php composer.phar install`
  5. open `my/project/directory/configs/application.config.php` and add the following key to your `modules`:

     ```php
     'ZfcRbac',
     ```

## Providers

Providers are listeners that hook into various events to provide roles and permissions. ZfcRbac ships with
several providers that you can use out of the box:

  - Generic Providers:
    - Permission (Generic\DoctrineDbal): Uses DoctrineDBAL to configure permissions.
    - Permission (Generic\InMemory): In memory permission adapter used primarily for testing or small sites.
    - Role (Generic\InMemory): In memory role adapter used primarily for testing or small sites.
    - Role (AdjacencyList\Role): Used for pre-loading roles in an adjacency list style.
    - Lazy (NestedSet\DoctrineDbal): Used to lazy-load permissions/roles from DoctrineDBAL. This is used to for sites
                                     with lots of permissions/roles so that the entire tree isn't in memory. It also
                                     uses the nested set model rather than adjacency list for performant tree reads.
                                     It's recommended to use this adapter standalone.

See the module.config.php file for sample setups.

If you're willing to use a provider requiring SQL. You can use the `data/schema.sql` file to help you install the tables.

## Firewalls

Firewalls protect your resources by allowing access only to the roles you specify. By default, two
firewall types are provided:

  - Route: Protects your routes.
  - Controller: Protects controllers.

By default, only controller access is enabled. See the module.config.php file for sample setups.

## Setting the identity provider

The identity provider is a service alias setup to provide a working identity to ZfcRbac. The default alias is
to use Zend\Authentication\AuthenticationService, but can be changed via the `identity_provider` key in configuration.
The object returned by the identity provider must implement `ZfcRbac\Identity\IdentityInterface`.

## View helper and controller plugin

An `isGranted($permission)` view helper and controller plugin is available. To use, simply pass a permission to check
for access.

## Sample configuration

Add this code in a `zfcrbac.global.php` file into your `config/autoload` directory.

```php
<?php
return array(
    'zfcrbac' => array(
        'firewalls' => array(
            'ZfcRbac\Firewall\Controller' => array(
                array('controller' => 'index', 'actions' => 'index', 'roles' => 'guest')
            ),
            'ZfcRbac\Firewall\Route' => array(
                array('route' => 'profiles/add', 'roles' => 'member'),
                array('route' => 'admin/*', 'roles' => 'administrator')
            ),
        ),
        'providers' => array(
            'ZfcRbac\Provider\AdjacencyList\Role\DoctrineDbal' => array(
                'connection' => 'doctrine.connection.orm_default',
                'options' => array(
                    'table'         => 'rbac_role',
                    'id_column'     => 'role_id',
                    'name_column'   => 'role_name',
                    'join_column'   => 'parent_role_id'
                )
            ),
            'ZfcRbac\Provider\Generic\Permission\DoctrineDbal' => array(
                'connection' => 'doctrine.connection.orm_default',
                'options' => array(
                    'permission_table'      => 'rbac_permission',
                    'role_table'            => 'rbac_role',
                    'role_join_table'       => 'rbac_role_permission',
                    'permission_id_column'  => 'perm_id',
                    'permission_join_column'=> 'perm_id',
                    'role_id_column'        => 'role_id',
                    'role_join_column'      => 'role_id',
                    'permission_name_column'=> 'perm_name',
                    'role_name_column'      => 'role_name'
                )
            ),
        ),
        'identity_provider' => 'standard_identity'
    ),
    'service_manager' => array(
        'factories' => array(
            'standard_identity' => function ($sm) {
                $roles = array('guest','member','admin');
                $identity = new \ZfcRbac\Identity\StandardIdentity($roles);
                return $identity;
            },
        )
    ),
);
```

## Protecting your services

Protecting your services is as easy as injecting the ZfcRbac service into your services. You can then use
the provided `isGranted($role)` method to check if access is allowed.

For example,

```php
<?php
class NewService
{
    protected $rbac;

    public function __construct(\ZfcRbac\Service\Rbac $rbac)
    {
        $this->rbac = $rbac;
    }

    public function createPost()
    {
        if (!$this->rbac->isGranted('PERMISSION_MANAGE_NEWS')) {
            // code
        }

        // code
    }
}
```

## Dynamic assertions

Dynamic assertions are available by passing an instance of ZfcRbac\AssertionInterface or a Closure to
isGranted() as the second parameter. For example,

```php
<?php
$event = new \My\Event;
$event->setUserId(1);

// Verify the user has both event.update permission and that the user id matches the event user id
$rbac->isGranted('event.update', function($rbac) use ($event) {
    return $rbac->getIdentity()->getId() === $event->getUserId();
});
```
