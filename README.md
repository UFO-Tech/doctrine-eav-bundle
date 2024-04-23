# DoctrineEAVBundle

The **DoctrineEAVBundle** is designed to integrate the Entity-Attribute-Value (EAV) model with Doctrine ORM in Symfony applications. This bundle facilitates the handling of highly dynamic data models and is ideal for scenarios where the data schema is not fixed, such as in customizable e-commerce products or complex data platforms.

## Features

- **Dynamic Entity Management**: Manage entities with an arbitrary number of attributes without altering the database schema.
- **Scalable Data Modeling**: Optimize your database for scalability in applications with extensive attribute sets.
- **Flexible Querying**: Utilize Doctrine ORM to query your EAV data efficiently, maintaining the power and security of DQL.
- **Configurable**: Easily configure entity attributes on the fly, adapting to your data needs without service interruption.

## Installation

Install the **DoctrineEAVBundle** using Composer:

```bash
composer require ufo-tech/doctrine-eav-bundle
