# phpunit-port-canary [![Packagist](https://img.shields.io/packagist/dt/willwashburn/phpunit-port-canary.svg?style=flat-square)](https://packagist.org/packages/willwashburn/phpunit-port-canary/stats) [![Packagist](https://img.shields.io/packagist/v/willwashburn/phpunit-port-canary.svg?style=flat-square)](https://packagist.org/packages/willwashburn/phpunit-port-canary) [![MIT License](https://img.shields.io/packagist/l/willwashburn/phpunit-port-canary.svg?style=flat-square)](https://github.com/willwashburn/phpunit-port-canary/blob/master/license.txt) 
:bird: Find tests that are making external requests

## Why
Not all tests are written perfectly. This library intends to help you spot when your phpunit tests are making requests across ports when you don't want them to.

>Note: This is in a super alpha bootleg version. Try it out, but.. uh, no promises.

## Installation

```
composer require willwashburn/phpunit-port-canary
```

Alternatively, add "willwashburn/phpunit-port-canary": "0.0.1" to your composer.json

## Configuration
 Add this to your phpunit.xml and any test that crosses a port should throw an error, very abrubtly.
```XML
    <listeners>
        <listener class="\WillWashburn\PortListener"/>
    </listeners>
```

## Change Log
- v0.0.1 - MVP bootleg version
