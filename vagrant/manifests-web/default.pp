$panel_host_name = "dedipanel.dev"
$site_host_name  = "dedisite.dev"

Exec { path => [ '/bin/', '/sbin/', '/usr/bin/', '/usr/sbin/' ] }
File { owner => 0, group => 0, mode => 0644 }

user { 'dedipanel':
  ensure   => present,
  password => sha1('dedipanel'),
}

file { "/var/lock/apache2":
  ensure => directory,
  owner => vagrant
}

exec { "ApacheUserChange" :
  command => "sed -i 's/export APACHE_RUN_USER=.*/export APACHE_RUN_USER=vagrant/ ; s/export APACHE_RUN_GROUP=.*/export APACHE_RUN_GROUP=vagrant/' /etc/apache2/envvars",
  require => [ Package["apache"], File["/var/lock/apache2"] ],
  notify  => Service['apache'],
}

class { 'apt':
  always_apt_update => true,
}

Class['::apt::update'] -> Package <|
    title != 'python-software-properties'
and title != 'software-properties-common'
|>

package { [
    'build-essential',
    'vim',
    'curl',
    'git-core',
    'default-jre',
    'firefox',
    'xvfb'
  ]:
  ensure  => 'installed',
}

class { 'apache': }

exec { 'disable default vhost':
  command => '/usr/sbin/a2dissite 000-default && /usr/bin/service apache2 reload',
  require => Class['apache'],
}

apache::module { 'rewrite': }

apache::vhost { "${panel_host_name}":
  server_name   => "${panel_host_name}",
  serveraliases => [
    "www.${panel_host_name}"
  ],
  docroot       => "/var/www/dedipanel/web/",
  directory     => "/var/www/dedipanel/web/",
  priority      => 1,
  directory_allow_override => "All",
}

apache::vhost { "${site_host_name}":
  server_name => "${site_host_name}", 
  serveraliases => [
    "www.${site_host_name}", 
  ],
  docroot   => '/var/www/dedipanel-site/web/',
  directory => '/var/www/dedipanel-site/web/',
  priority      => 1,
  directory_allow_override => "All",
}

class { 'php':
  service             => 'apache',
  service_autorestart => false,
  module_prefix       => '',
}

php::module { 'php5-mysql': }
php::module { 'php5-cli': }
php::module { 'php5-curl': }
php::module { 'php5-intl': }
php::module { 'php5-mcrypt': }
php::module { 'php5-gd': }
php::module { 'php-apc': }
php::module { 'php5-xdebug': }

php::conf { 'xdebug':
  source => 'puppet:////vagrant/manifests-web/files/xdebug.ini',
  ensure => 'present',
  path   => '/etc/php5/mods-available/xdebug.ini',
}

class { 'php::devel':
  require => Class['php'],
}

class { 'composer':
  command_name => 'composer',
  target_dir   => '/usr/local/bin',
  auto_update => true,
  require => Package['php5', 'curl'],
}

exec { "phpunit":
  command => '/usr/local/bin/composer global require "phpunit/phpunit=4.2.*"',
  environment => "COMPOSER_HOME=/home/vagrant",
  require => Class['composer'],
}

file { "vagrant composer":
  path    => "/home/vagrant/.composer",
  ensure  => directory,
  owner   => 'vagrant',
  group   => 'vagrant',
  mode    => 0644,
  recurse => inf,
  require => Exec['phpunit'],
}

php::ini { 'php_ini_configuration':
  value   => [
    'date.timezone = "UTC"',
    'display_errors = "On"',
    'error_reporting = -1',
    'short_open_tag = 0'
  ],
  notify  => Service['apache'],
  require => Class['php'], 
}

class { 'mysql::server':
  override_options => {
    'root_password' => '',
    'mysqld' => {
      'bind_address' => '0.0.0.0'
    },
  },
}

exec { "mysql-root-access":
    command => "/usr/bin/mysql -u root -e \"GRANT ALL ON *.* to root@10.0.0.1 IDENTIFIED BY ''; FLUSH PRIVILEGES;\"",
    require => Class['mysql::server'],
}

mysql_database{ 'dedipanel':
  ensure  => present,
  charset => 'utf8',
  require => Class['mysql::server'],
}

mysql_database{ 'dedipanel_test':
  ensure  => present,
  charset => 'utf8',
  require => Class['mysql::server'],
}

mysql_database { 'dedipanel-site':
  ensure => present, 
  charset => 'utf8', 
  require => Class['mysql::server'],
}

# Installing selenium server and setting an autostart script
include wget
wget::fetch { "selenium-server":
  source      => 'http://selenium-release.storage.googleapis.com/2.42/selenium-server-standalone-2.42.2.jar',
  destination => '/usr/local/bin/selenium-server',
  verbose     => false,
  require     => [ Package['default-jre'], Package['firefox'] ],
}

file { 'selenium-server':
  path    => '/usr/local/bin/selenium-server',
  mode    => '755',
  ensure  => present,
  require => Wget::Fetch['selenium-server'],
}

$autostart_sh = "#!/bin/bash
DISPLAY=:1 screen xvfb-run java -jar /usr/local/bin/selenium-server"

file { 'autostart-selenium-server':
  path    => '/etc/init.d/selenium-server',
  mode    => '755',
  ensure  => present,
  content => $autostart_sh,
  require => File['selenium-server'],
}

exec { 'rc.d selenium-server':
  command => 'update-rc.d selenium-server defaults',
  require => File['autostart-selenium-server'],
}
