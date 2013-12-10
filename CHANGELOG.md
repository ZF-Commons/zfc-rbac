# CHANGELOG

## 1.0.2

* Fixed a bug in the Zend developer toolbars when collecting role objects ([#115](https://github.com/ZF-Commons/ZfcRbac/pull/115)).
* Now always check if a role already has a permission before adding it to a role ([#113](https://github.com/ZF-Commons/ZfcRbac/pull/113))

## 1.0.1

* Fixed an issue where identity roles would not get properly converted to string ([#111](https://github.com/ZF-Commons/ZfcRbac/pull/111))

## 1.0.0

* ZfcRbac has been completely rewritten and IS NOT backward compatible with previous versions. Please refer to
the official documentation.
* [BC] PHP dependency has been raised to PHP 5.4 (because we need to go forward). People that need compatibility
with PHP 5.3 can still use the 0.2 branch
* [BC] Zend Framework 2 dependency has been raised to 2.2.
