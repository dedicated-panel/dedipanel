#!/bin/bash

set -e -x

mkdir -p /etc/puppet/modules

puppet module install example42/puppi --version 2.1.9 --force
puppet module install example42/apache --version 2.1.7 --force
puppet module install puppetlabs/stdlib --version 4.2.2 --force
puppet module install puppetlabs/apt --version 1.4.2 --force
puppet module install example42/php --version 2.0.18 --force
puppet module install puppetlabs/mysql --version 2.2.3 --force
puppet module install willdurand/composer --version 0.0.7 --force
puppet module install maestrodev/wget --version 1.4.3 --force
