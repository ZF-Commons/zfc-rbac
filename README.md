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