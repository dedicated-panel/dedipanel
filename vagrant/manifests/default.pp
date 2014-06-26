$panel_host_name = "dedipanel.dev"
$site_host_name  = "dedisite.dev"

Exec { path => [ '/bin/', '/sbin/', '/usr/bin/', '/usr/sbin/' ] }
File { owner => 0, group => 0, mode => 0644 }

user { 'dedipanel':
  ensure   => present,
  password => sha1('dedipanel'),
}

file { "/dev/shm/dedipanel":
  ensure => directory,
  purge => true,
  force => true,
  owner => vagrant,
  group => vagrant,
}

file { "/dev/shm/dedipanel-site":
  ensure => directory, 
  purge => true, 
  force => true, 
  owner => vagrant, 
  group => vagrant
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

    apt::key { '4F4EA0AAE5267A6C': }

apt::ppa { 'ppa:ondrej/php5-oldstable':
  require => Apt::Key['4F4EA0AAE5267A6C']
}

package { [
    'build-essential',
    'vim',
    'curl',
    'git-core',
    'mc'
  ]:
  ensure  => 'installed',
}

class { 'apache': }

apache::module { 'rewrite': }

apache::vhost { "${panel_host_name}":
  server_name   => "${panel_host_name}",
  serveraliases => [
    "www.${panel_host_name}"
  ],
  docroot       => "/var/www/dedipanel/web/",
  port          => '80',
  env_variables => [
    'VAGRANT VAGRANT'
  ],
  priority      => '1',
}

apache::vhost { "${site_host_name}":
  server_name => "${site_host_name}", 
  serveraliases => [
    "www.${site_host_name}", 
  ],
  docroot => '/var/www/dedipanel-site/web/',
  port          => '80',
  env_variables => [
    'VAGRANT VAGRANT'
  ],
  priority => '1', 
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

class { 'php::devel':
  require => Class['php'],
}

class { 'php::pear':
  require => Class['php'],
}

php::pear::module { 'PHPUnit':
  repository  => 'pear.phpunit.de',
  use_package => 'no',
  require => Class['php::pear']
}

class { 'composer':
  command_name => 'composer',
  target_dir   => '/usr/local/bin',
  auto_update => true,
  require => Package['php5', 'curl'],
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
  override_options => { 'root_password' => '', },
}

mysql_database{ 'dedipanel':
  ensure  => present,
  charset => 'utf8',
  require => Class['mysql::server'],
}

mysql_database { 'dedipanel-site':
  ensure => present, 
  charset => 'utf8', 
  require => Class['mysql::server'], 
}
