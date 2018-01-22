_Up-to-date with version 2.3.* of ZfcRbac_

Welcome to the official ZfcRbac documentation. This documentation will help you quickly understand how to use
and extend ZfcRbac.

If you are looking for some information that is not listed in the documentation, please open an issue!

1. [Introduction](/docs/01.%20Introduction.md)
   1. [Why should I use an authorization module?](/docs/01.%20Introduction.md#why-should-i-use-an-authorization-module)
   2. [What is the Rbac model?](/docs/01.%20Introduction.md#what-is-the-rbac-model)
   3. [How can I integrate ZfcRbac into my application?](/docs/01.%20Introduction.md#how-can-i-integrate-zfcrbac-into-my-application)

2. [Quick Start](/docs/02.%20Quick%20Start.md)
   1. [Specifying an identity provider](/docs/02.%20Quick%20Start.md#specifying-an-identity-provider)
   2. [Adding a guard](/docs/02.%20Quick%20Start.md#adding-a-guard)
   3. [Adding a role provider](/docs/02.%20Quick%20Start.md#adding-a-role-provider)
   5. [Registering a strategy](/docs/02.%20Quick%20Start.md#registering-a-strategy)
   6. [Using the authorization service](/docs/02.%20Quick%20Start.md#using-the-authorization-service)

3. [Role providers](/docs/03.%20Role%20providers.md)
   1. [What are role providers?](/docs/03.%20Role%20providers.md#what-are-role-providers)
   2. [Identity providers](/docs/03.%20Role%20providers.md#identity-providers)
   3. [Built-in role providers](/docs/03.%20Role%20providers.md#built-in-role-providers)
   4. [Creating custom role providers](/docs/03.%20Role%20providers.md#creating-custom-role-providers)

4. [Guards](/docs/04.%20Guards.md)
   1. [What are guards and when to use them?](/docs/04.%20Guards.md#what-are-guards-and-when-to-use-them)
   2. [Built-in guards](/docs/04.%20Guards.md#built-in-guards)
   3. [Creating custom guards](/docs/04.%20Guards.md#creating-custom-guards)

5. [Strategies](/docs/05.%20Strategies.md)
   1. [What are strategies?](/docs/05.%20Strategies.md#what-are-strategies)
   2. [Built-in strategies](/docs/05.%20Strategies.md#built-in-strategies)
   3. [Creating custom strategies](/docs/05.%20Strategies.md#creating-custom-strategies)

6. [Using the Authorization Service](/docs/06.%20Using%20the%20Authorization%20Service.md)
   1. [Injecting the AuthorizationService](/docs/06.%20Using%20the%20Authorization%20Service.md#injecting-the-authorization-service)
   2. [Checking permissions](/docs/06.%20Using%20the%20Authorization%20Service.md#checking-permissions-in-a-service)
       1. [In a service](/docs/06.%20Using%20the%20Authorization%20Service.md#checking-permissions-in-a-service)
       2. [In a controller's action using the isGranted controller plugin](/docs/06.%20Using%20the%20Authorization%20Service.md#in-a-controller-)
       3. [In a view using the isGranted view helper](/docs/06.%20Using%20the%20Authorization%20Service.md#in-a-view-)
   3. [Permissions and Assertions](/docs/06.%20Using%20the%20Authorization%20Service.md#permissions-and-assertions)

7. [Cookbook](/docs/07.%20Cookbook.md)
   1. [A real world example](/docs/07.%20Cookbook.md#a-real-world-application)
   2. [Best practices](/docs/07.%20Cookbook.md#best-practices)
   3. [Using ZfcRbac with Doctrine ORM](/docs/07.%20Cookbook.md#using-zfcrbac-with-doctrine-orm)
   4. [How to deal with roles with lot of permissions?](/docs/07.%20Cookbook.md#how-to-deal-with-roles-with-lot-of-permissions)
   5. [Using ZfcRbac and ZF2 Assetic](/docs/07.%20Cookbook.md#using-zfcrbac-and-zf2-assetic)
   6. [Using ZfcRbac and ZfcUser](/docs/07.%20Cookbook.md#using-zfcrbac-and-zfcuser)
