# SpiffySecurity Module for Zend Framework 2

SpiffySecurity is an ACL module for Zend Framework 2 geared towards quick & easy setup. Getting ACL
working should take you less than 5 minutes.

## Requirements
 - PHP 5.3 or higher
 - [Zend Framework 2](http://www.github.com/zendframework/zf2)

## Installation

Installation of SpiffySecurity uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

#### Installation steps

  1. `cd my/project/directory`
  2. create a `composer.json` file with following contents:

     ```json
     {
         "require": {
             "spiffy/spiffy-security": "dev-master"
         }
     }
     ```
  3. install composer via `curl -s http://getcomposer.org/installer | php` (on windows, download
     http://getcomposer.org/installer and execute it with PHP)
  4. run `php composer.phar install`
  5. open `my/project/directory/configs/application.config.php` and add the following key to your `modules`:

     ```php
     'SpiffySecurity',
     ```

## Providers

To configure your roles SpiffySecurity uses what's called "providers." Providers offer a generic way
to access your roles. Shipped providers include:

  - Doctrine DBAL: Uses Doctrine DBAL. Can be used out of the box with DoctrineORMModule.
  - PDO: Uses PHP PDO connection.
  - InMemory: Primarily used for testing. Allows you to setup roles directly in configuration.

See the module.config.php file for sample setups.

## Firewalls

Firewalls protect your resources by allowing access only to the roles you specify. By default, two
firewall types are provided:

  - Route: Protects your routes.
  - Controller: Protects controllers.

By default, only controller access is enabled. See the module.config.php file for sample setups.

## Setting the active role

The active role can be set by using the `role` key in configuration. The role is determined in the following manner:

  - If role is a string, the instance is grabbed from the Service Locator. If that alias does not exist then a new
    role is created using that string name. This is good for testing when you want to hard code yourself to a role.
  - If an object is retrieved from the service manager and it is not an instance of RoleInterface then
    a "getIdentity" method is checked. If that method exists, then the role is pulled using that method.
  - Finally, if no role is found using the above methods then it is assume that no authentication is available and
    the anonymous role is used as specified by the `anonymous_role` option.

## Sample configuration

```php
return array(
    'security' => array(
        'template' => 'error/403',
        'role' => 'zfcuser_auth_service',

        'firewall' => array(
            'controller' => array(
                array('controller' => 'profiles', 'action' => 'index', 'roles' => 'member')
            ),
            'route' => array(
                array('route' => 'profiles', 'roles' => 'member'),
                array('route' => 'admin', 'roles' => 'administrator')
            ),
        ),

        'provider' => array(
            'in_memory' => array(
                'test_role' => 'test_parent',
            ),
            'doctrine_dbal' => array(
                'connection'         => 'doctrine.connection.orm_default',
                'table'              => 'role',
                'role_id_column'     => 'id',
                'role_name_column'   => 'name',
                'parent_join_column' => 'parent_role_id'
            )
        )
    ),
);
```