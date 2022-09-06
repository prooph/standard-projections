# Standard Projections for Prooph EventStore

[![Continuous Integration](https://github.com/prooph/standard-projections/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/prooph/standard-projections/actions/workflows/continuous-integration.yml)
[![Coverage Status](https://coveralls.io/repos/prooph/standard-projections/badge.svg?branch=master&service=github)](https://coveralls.io/github/prooph/standard-projections?branch=master)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/prooph/improoph)

## Overview

The standard projections are some kind of event-indexing, so you can retrieve events from
all streams at once (`AllStreamProjectionRunner`), by category (`CategoryStreamProjectionRunner`)
or by message name (`MessageNameStreamProjectionRunner`). See docs for more information.

## Requirements

- PHP >= 7.4
- Prooph EventStore v7

## Documentation

Documentation is [in the doc tree](docs/), and can be compiled using [bookdown](http://bookdown.io).

```console
$ php ./vendor/bin/bookdown docs/bookdown.json
$ php -S 0.0.0.0:8080 -t docs/html/
```

Then browse to [http://localhost:8080/](http://localhost:8080/)

## Support

- Ask questions on Stack Overflow tagged with [#prooph](https://stackoverflow.com/questions/tagged/prooph).
- File issues at [https://github.com/prooph/event-store/issues](https://github.com/prooph/event-store/issues).
- Say hello in the [prooph gitter](https://gitter.im/prooph/improoph) chat.

## Contribute

Please feel free to fork and extend existing or add new plugins and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

## License

Released under the [New BSD License](LICENSE).
