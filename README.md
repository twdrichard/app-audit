# 📄 Application Audit

## 🌟 Overview

App Audit is a command line PHP application that provides a quick overview of a PHP or JS application,
showing security alerts, out of date components and log lines.

It recognizes WordPress, Laravel and Yii applications and will list WordPress plugins and any updates needed.

It works remotely using SSH, so doesn't need to be installed on a server (although it can analyse local applications too.)
The output can be sent to email as html.

## ℹ️ Getting started

To install you'll need composer (from getcomposer.org)

composer install

## 🚀 Usage instructions

then run app audit from the command line

```bash
./application audit ssh-target-name

```

![example](./screenshot.png)

## ℹ️ Requirements

PHP 8.2 or greater
Composer

## License

Open-source software licensed under the MIT license.
