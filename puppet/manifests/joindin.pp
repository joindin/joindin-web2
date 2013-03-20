
# Set default path for Exec calls
Exec {
    path => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ]
}

# Include the parameters file then execute the joinin module
node default {
    include params
    include joindin
}

host { 'api.dev.joind.in':
    ip => '192.168.57.5',
}
