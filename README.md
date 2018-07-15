# Content Fuzzyfyr Module for Magento® 2

The **Content Fuzzyfyr** module for *Magento® 2* fills up empty content fields - and if needed - switches real content with dummy content. 
This is for development purposes, e.g. save time to prepare test data and matching GDPR restrictions.


## Features:
* Fill up empty content fields with dummy content - completely automated
* Match GDPR restrictions for development, when using production data
* Use parameters to control the sections required to be filled up with dummy content

## Installation
The preferred way of installing `allindata/magento2-content-fuzzyfyr` is through Composer. 
Simply add `allindata/magento2-content-fuzzyfyr` as a dependency:

```
composer.phar require allindata/magento2-content-fuzzyfyr
```

Optional you can download the latest version [here](https://github.com/allin-data/magento2-content-fuzzyfyr/releases) 
and install the decompressed code in your projects directory under *app/code/AllInData/ContentFuzzyfyr*.  

## Post-Install

After the installment of the module source code, the module has to be enabled by the *Magento® 2* CLI.

```
bin/magento module:enable AllInData_ContentFuzzyfyr
```

## System Upgrade

After enabling the module, the *Magento® 2* system must be upgraded. 

If the system mode is set to *production*, run the *compile* command first. 
This is not necessary for the *developer* mode.
```
bin/magento setup:di:compile
```

To upgrade the system, the *upgrade* command must be run.
```
bin/magento setup:upgrade
```

# User Guide
Find the complete user guide [here](./docs/UserGuide.pdf "User Guide").

## How to use

### CLI
#### Content Fuzzyfyr

The **Content Fuzzyfyr** Module for *Magento® 2* provides an *Magento® 2* CLI command *aid:content:fuzzyfyr* to modify 
existing content (or only empty fields with the *--only-empty* flag) to be switched with some defined default value:

    bin/magento aid:content:fuzzyfyr [options]
    
**Note:** Be aware the command only runs in non-production mode to avoid messing up production data on mistake.

You may want to switch to *default* or *developer* mode to run the command:
   
    bin/magento deploy:mode:set developer
    
#### List of flags

Option | Description
--- | ---
--only-empty | Use dummy content only if the original data is equal to empty
--categories | Apply dummy content to categories (content, meta description)
--cms-blocks | Apply dummy content to CMS Blocks (content)
--cms-pages | Apply dummy content to CMS Pages (content, meta description)
--customers | Apply dummy content to customers (Last name, address, email)
--products | Apply dummy content to products (description)
--users | Apply dummy content to users (Last name, email)

#### List of options

Option | Value | Description
--- | --- | ---
--dummy-content-text | String | Used as dummy text content. Defaults to 'Lorem ipsum.'
--dummy-content-email | String | Used as dummy email content. Defaults to 'lorem.ipsum.%1$s@test.localhost'
--dummy-content-url | String | Used as dummy url content. Defaults to 'https://lor.emips.um/foo/bar/'
--dummy-content-phone | String | Used as dummy phone content. Defaults to '+49 (0) 600 987 654 32'

#### How to apply custom code

An event is triggered, called *aid_content_fuzzyfyr_event*. Listening to this event, expect the following parameters
in the *Observer* data:

    'configuration' => \AllInData\ContentFuzzyfyr\Model\Configuration


#### Content Export

The **Content Fuzzyfyr** Module for *Magento® 2* provides an *Magento® 2* CLI command *aid:content:export* to export an
database dump with already fuzzyfied content for existing content (or only empty fields with the *--only-empty* flag)  
switched with some defined default value:

    bin/magento aid:content:export [options]
    
**Note:** Running the code activates maintenance mode. The maintenance mode will be disabled, if the command has run
successfully or erroneous, but it is recommended to check manually on production system.
    
#### List of flags

Option | Description
--- | ---
--only-empty | Use dummy content only if the original data is equal to empty
--categories | Apply dummy content to categories (content, meta description)
--cms-blocks | Apply dummy content to CMS Blocks (content)
--cms-pages | Apply dummy content to CMS Pages (content, meta description)
--customers | Apply dummy content to customers (Last name, address, email)
--products | Apply dummy content to products (description)
--users | Apply dummy content to users (Last name, email)

#### List of options

Option | Value | Description
--- | --- | ---
--dummy-content-text | String | Used as dummy text content. Defaults to 'Lorem ipsum.'
--dummy-content-email | String | Used as dummy email content. Defaults to 'lorem.ipsum.%1$s@test.localhost'
--dummy-content-url | String | Used as dummy url content. Defaults to 'https://lor.emips.um/foo/bar/'
--dummy-content-phone | String | Used as dummy phone content. Defaults to '+49 (0) 600 987 654 32'
--backup-output | String | Path where the database dump is placed. Defaults to './var/backup/'

#### How to apply custom code

An event is triggered, called *aid_content_export_event*. Listening to this event, expect the following parameters
in the *Observer* data:

    'configuration' => \AllInData\ContentFuzzyfyr\Model\Configuration

## Contribution
Feel free to contribute to this module by reporting issues or create some pull requests for improvements.

## License
The **Content Fuzzyfyr** Module for *Magento® 2* is released under the Apache 2.0 license.
