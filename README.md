# SpiffySecurity Module for Zend Framework 2

SpiffySecurity is an access control module for Zend Framework 2 geared towards quick & easy setup. Getting access control
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

Providers are listeners that hook into various events to provide roles and permissions. SpiffySecurity ships with
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

## Firewalls

Firewalls protect your resources by allowing access only to the roles you specify. By default, two
firewall types are provided:

  - Route: Protects your routes.
  - Controller: Protects controllers.

By default, only controller access is enabled. See the module.config.php file for sample setups.

## Setting the identity provider

The identity provider is a service alias setup to provide a working identity to SpiffySecurity. The default alias is
my_identity_provider but can be changed via the `identity_provider` key in configuration. The object returned by
the identity provider must implement `SpiffySecurity\Identity\IdentityInterface`.

## View helper and controller plugin

An `isGranted($permission)` view helper and controller plugin is available. To use, simply pass a permission to check
for access.

## Sample configuration

```php
<?php
return array(
    'security' => array(
        'firewalls' => array(
            'controller' => array(
                array('controller' => 'profile', 'action' => 'view', 'roles' => 'guest')
            ),
            'route' => array(
                array('route' => 'profiles/add', 'roles' => 'member'),
                array('route' => 'admin/*', 'roles' => 'administrator')
            ),
        ),

        'role_providers' => array(
            'SpiffySecurity\Provider\Role\InMemory' => array(
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

## Protecting your services

Protecting your services is as easy as injecting the SpiffySecurity service into your services. You can then use
the provided `isGranted($role)` method to check if access is allowed.

For example,

```php
<?php
class NewService
{
    protected $security;

    public function __construct(\SpiffySecurity\Service\Security $security)
    {
        $this->security = $security;
    }

    public function createPost()
    {
        if (!$this->security->isGranted('ROLE_NEWS_MANAGER')) {
            // code
        }

        // code
    }
}
```

## Protecting your objects

Coming soon, maybe...