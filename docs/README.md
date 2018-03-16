_Up-to-date with version 2.3.* of ZfcRbac_

Welcome to the official ZfcRbac documentation. This documentation will help you quickly understand how to use
and extend ZfcRbac.

If you are looking for some information that is not listed in the documentation, please open an issue!

1. [Introduction](01.%20Introduction.md)
   1. [Why should I use an authorization module?](01.%20Introduction.md#why-should-i-use-an-authorization-module)
   2. [What is the Rbac model?](01.%20Introduction.md#what-is-the-rbac-model)
   3. [How can I integrate ZfcRbac into my application?](01.%20Introduction.md#how-can-i-integrate-zfcrbac-into-my-application)

2. [Quick Start](02.%20Quick%20Start.md)
   1. [Specifying an identity provider](02.%20Quick%20Start.md#specifying-an-identity-provider)
   2. [Adding a guard](02.%20Quick%20Start.md#adding-a-guard)
   3. [Adding a role provider](02.%20Quick%20Start.md#adding-a-role-provider)
   5. [Registering a strategy](02.%20Quick%20Start.md#registering-a-strategy)
   6. [Using the authorization service](02.%20Quick%20Start.md#using-the-authorization-service)

3. [Role providers](03.%20Role%20providers.md)
   1. [What are role providers?](03.%20Role%20providers.md#what-are-role-providers)
   2. [Identity providers](03.%20Role%20providers.md#identity-providers)
   3. [Built-in role providers](03.%20Role%20providers.md#built-in-role-providers)
   4. [Creating custom role providers](03.%20Role%20providers.md#creating-custom-role-providers)

4. [Guards](04.%20Guards.md)
   1. [What are guards and when to use them?](04.%20Guards.md#what-are-guards-and-when-to-use-them)
   2. [Built-in guards](04.%20Guards.md#built-in-guards)
   3. [Creating custom guards](04.%20Guards.md#creating-custom-guards)

5. [Strategies](05.%20Strategies.md)
   1. [What are strategies?](05.%20Strategies.md#what-are-strategies)
   2. [Built-in strategies](05.%20Strategies.md#built-in-strategies)
   3. [Creating custom strategies](05.%20Strategies.md#creating-custom-strategies)

6. [Using the Authorization Service](06.%20Using%20the%20Authorization%20Service.md)
   1. [Injecting the AuthorizationService](06.%20Using%20the%20Authorization%20Service.md#injecting-the-authorization-service)
   2. [Checking permissions](06.%20Using%20the%20Authorization%20Service.md#checking-permissions-in-a-service)
       1. [In a service](06.%20Using%20the%20Authorization%20Service.md#checking-permissions-in-a-service)
       2. [In a controller's action using the isGranted controller plugin](06.%20Using%20the%20Authorization%20Service.md#in-a-controller-)
       3. [In a view using the isGranted view helper](06.%20Using%20the%20Authorization%20Service.md#in-a-view-)
   3. [Permissions and Assertions](06.%20Using%20the%20Authorization%20Service.md#permissions-and-assertions)

7. [Cookbook](07.%20Cookbook.md)
   1. [A real world example](07.%20Cookbook.md#a-real-world-application)
   2. [Best practices](07.%20Cookbook.md#best-practices)
   3. [Using ZfcRbac with Doctrine ORM](07.%20Cookbook.md#using-zfcrbac-with-doctrine-orm)
   4. [How to deal with roles with lot of permissions?](07.%20Cookbook.md#how-to-deal-with-roles-with-lot-of-permissions)
   5. [Using ZfcRbac and ZF2 Assetic](07.%20Cookbook.md#using-zfcrbac-and-zf2-assetic)
   6. [Using ZfcRbac and ZfcUser](07.%20Cookbook.md#using-zfcrbac-and-zfcuser)
