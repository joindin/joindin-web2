class joindin::mongodb {

    # Setup 10Gen's repo
    file { "10GenRepo" :
        path   => "/etc/yum.repos.d/10gen.repo",
        source => "puppet:///modules/joindin/10gen.repo",
        owner  => "root",
        group  => "root",
    }

    package { ['mongo-10gen', 'mongo-10gen-server' ]:
        ensure => present,
        require => File["10GenRepo"],
    }

    service { 'mongod':
        require => Package['mongo-10gen-server'],
        ensure => running,
    }
}



