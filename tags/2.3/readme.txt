Modification to the Apache config
=================================

    local .htaccess support
    -----------------------

    - /etc/httpd/httpd.conf
        # Take wipha .htaccess into account
        <Directory "/Users/*/Sites/wipha">
            AllowOverride All
        </Directory>

    ==== not used anymore ====
    - /etc/httpd/users/<user>.conf
        AllowOverride All


    PHP support
    -----------

    - /etc/httpd/httpd.conf  (uncomment these lines)
        #LoadModule php4_module        libexec/httpd/libphp4.so
        #AddModule mod_php4.c
