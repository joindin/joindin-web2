class joindin::web {
    include apache
    include php

    # Configure apache virtual host
    apache::vhost { $params::host :
        docroot  => '/vagrant/web',
        template => 'joindin/vhost.conf.erb',
        port     => $params::port,
        require  => Package["apache"],
    }
    
    # Install PHP modules
    php::module { "pecl-xdebug" :
        require => File["EpelRepo"],            # xdebug is in the epel repo
    }

    # Set development values to our php.ini
    augeas { 'set-php-ini-values':
        context => '/files/etc/php.ini',
        changes => [
            'set PHP/error_reporting "E_ALL | E_STRICT"',
            'set PHP/display_errors On',
            'set PHP/display_startup_errors On',
            'set PHP/html_errors On',
            'set Date/date.timezone Europe/London',
        ],
        require => Package['php'],
        notify  => Service['apache'],
    }

}
