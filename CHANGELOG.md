# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.9.0

### Added

- [#77](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/77) Make module compatible with Magento 2.4.4
- [#75](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/75) Bump phpstan/phpstan to 1.5
- [#72](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/72) Bump bitexpert/captainhook-infection to 0.6.0
- [#64](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/64) Bump phpunit/phpunit to 9.5.20
- [#60](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/60) Bump guzzlehttp/psr7 to 1.8.5
- [#58](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/58) Bump bitexpert/phpstan-magento to 0.19.0
- [#51](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/51) Bump infection/infection to 0.26.6
- [#49](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/49) Bump captainhook/captainhook to 5.10.8
- [#37](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/37) Bump captainhook/plugin-composer to 5.3.3
- [#20](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/20) Bump squizlabs/php_codesniffer to 3.6.2
- [#17](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/17) Bump magento/magento-coding-standard to 15

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.8.1

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#16](https://github.com/hivecommerce/magento2-content-fuzzyfyr/pull/16) Turn captainhook-infection into dev dependency

## 1.8.0

### Added

- Add Mark Shust's Docker setup to simplify local development

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Made hivecommerce/magento2-content-fuzzyfyr compatible with Magento 2.4.0 - 2.4.3
- Migrated from allin-data/magento2-content-fuzzyfyr to hivecommerce/magento2-content-fuzzyfyr

## 1.7.0

- Extended customer fuzzyfier to fuzzyfy quote and order addresses.

## 1.6.0

- Extended customer fuzzyfyier to alter customer password.
- Added --force flag to fuzzyfyr command to allow execution in production mode.

## 1.5.0

- Extended backup UI in administration area to allow convenient export of database dump matching GDPR compliance

## 1.4.0

- Added feature to add dummy images for products and categories

## 1.3.0

- Added feature to export database with fuzzyfied content without altering current database content persistently.

## 1.2.0

- Added unit test
- Fixed validation check on products fuzzyfyr
- Added missing folder to development environment and build script

## 1.1.0

- Added default value for URLs
- Added possibility to extend data on configuration model
- Refactored fuzzyfyr structure
- Simplified extension of configuration model
- Added fuzzyfyr for CMS Pages
- Added fuzzyfyr for CMS Blocks
- Extended fuzzyfyr for Customers with address fuzzing
- Changed implementation of fuzzyfyr for Products and Categories to match with EQP of *Magento® 2*

## 1.0.1

- Extended documentation
- Placed DI configuration into global scope

## 1.0.0

- Initial release of the **Content Fuzzyfyr** module for *Magento® 2*.
