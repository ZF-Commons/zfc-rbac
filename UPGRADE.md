# Upgrade guide

## From v1 to v2

Here are the major breaking changes from ZfcRbac 1 to ZfcRbac 2:

- Dependency to the ZF2 RBAC component has been replaced in favour of a ZF3 prototype which fixes a lot
of design issues.
- ZfcRbac no longer accepts multiple role providers. Therefore, the option `role_providers` has been renamed
to `role_provider`
- Permission providers are gone (hence, the options `permission_providers` as well as `permission_manager` should
be removed). Instead, roles now embed all the necessary information
- The `redirect_to_route` option for the `RedirectStrategy` is gone. Instead, we now have two options:
`redirect_to_route_connected` and `redirect_to_route_disconnected`. This solves an issue when people used to have
a guard on `login` for non-authenticated users only, which leaded to circular redirections.
