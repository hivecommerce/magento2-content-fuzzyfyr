# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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