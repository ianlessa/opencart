[![Maintainability](https://api.codeclimate.com/v1/badges/6b6afaf79c86cdf5487e/maintainability)](https://codeclimate.com/github/mundipagg/opencart/maintainability) 
[![Build Status](https://travis-ci.org/mundipagg/opencart.svg?branch=master)](https://travis-ci.org/mundipagg/opencart)

# Opencart/Mundipagg Integration module
This is the official Opencart module for Mundipagg integration

# Documentation
Refer to [module documentation](https://github.com/mundipagg/opencart/wiki)

# Compatibility
This module supports OpenCart version 3.0+

# Dependencies
* ```PHP``` Version 5.5+

# Install
There are two different ways to install our module

## Using modman
Modman is a project which helps developers to centralize extension code when
the environment forces you to mix your code with the core files. For more
information, refer to [modman](https://github.com/colinmollenhour/modman).

```bash
modman init
modman clone https://github.com/mundipagg/opencart
```

## OpenCart Installer
Download the Mundipagg.ocmod.zip file from last module [release](https://github.com/mundipagg/opencart/releases) and paste the upload folder content into your OpenCart root folder.

# Development

## Up workplace
```bash
git clone https://github.com/mundipagg/opencart
cd opencart
cp .env.example .env
composer install -vvv --prefer-dist --dev
vendor/bin/robo opencart:setup
```
## Run tests
First, up workplace.
```bash
composer test
```
# Generate package

## Bump version

```
vendor/bin/robo opencart:bump <new_version>
```

## Packing

Run the follow command replacing `version_of_package` by new version if you need
bump version. If you don't need bump version, don't pass the version to command

```
vendor/bin/robo opencart:pack [version_of_package]
```

# How can I contribute?
Please, refer to [CONTRIBUTING](CONTRIBUTING.md)

# Found something strange or need a new feature?
Open a new Issue following our issue template [ISSUE-TEMPLATE](ISSUE-TEMPLATE.md)

# Changelog
See in [releases](https://github.com/mundipagg/opencart/releases)

