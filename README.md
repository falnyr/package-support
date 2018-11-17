# Package Support

Package Support provides an automated way of checking versions of the project dependencies and displays warnings/errors
based on its configuration. Unlike commands like `composer outdated` you can get detailed information about the 
end of life, security support, deprecations etc.

## Requirements
PHP needs to be a minimum version of PHP 5.3.29.

## Installation

### Locally (Manual)

Download the [package-support.phar](#) file and store it somewhere on your computer.

### Locally (Composer)

```
composer require --dev falnyr/package-support
```

### Globally

To install Package Support, install [Composer](https://getcomposer.org/download/) and issue the following command:

```
composer global require falnyr/package-support
```

Then make sure you have the global Composer binaries directory in your `PATH`. This directory is platform-dependent, see Composer [documentation](https://getcomposer.org/doc/03-cli.md#composer-home) for details. Example for some Unix systems:

```
export PATH="$PATH:$HOME/.composer/vendor/bin"
```

### Usage

```
package-support check --precision 5 --silent composer.lock

#### Available statuses

| Precision | Status       | Description                                      |
|-----------|--------------|--------------------------------------------------|
| -         | Up to date   | No new versions available                        |
| 8         | Outdated     | New bugfix is available                          |
| 7         | Old          | New minor version is available                   |
| 6         | Obsolete     | New major version is available                   |
| 5         | Deprecated   | Official support ends in < 6 months              |
| 4         | Unsupported  | Official support ended, security fixes available |
| 3         | Legacy       | Security fixes end in < 6 months                 |
| 2         | Vulnerable   | Security support ended                           |
| 1         | Discontinued | Project abandoned                                | 

## Contributing

Please read [CONTRIBUTING.md](https://gist.github.com/PurpleBooth/b24679402957c63ec426) for details on our code of conduct, and the process for submitting pull requests to us. 

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details