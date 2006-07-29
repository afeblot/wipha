Modification to the Apache config
=================================

    local .htaccess support
    -----------------------

    - /etc/httpd/httpd.conf
        # This controls which options the .htaccess files in directories can
        # override. ....
        AllowOverride All
        
    - /etc/httpd/users/<user>.conf
        AllowOverride All


    PHP support
    -----------

    - /etc/httpd/httpd.conf  (uncomment these lines)
        #LoadModule php4_module        libexec/httpd/libphp4.so
        #AddModule mod_php4.c

Create a link into the iphoto dir to the iPhoto Library
=======================================================

    cd ~/Sites/iphoto
    ln -s "../../Pictures/iPhoto Library" ipl

Authorize apache to access the iphoto data
=================================================

    chmod o+x ~/Pictures

    find "~/Pictures/iPhoto Library" -type f | xargs -I @ chmod g+r "@"
    find "~/Pictures/iPhoto Library" -type d | xargs -I @ chmod g+x "@"
    sudo chgrp -R www "~/Pictures/iPhoto Library"

Create a data directory with apache write access
================================================

    cd ~/Sites/iphoto
    mkdir data
    chgrp www data
    chgown <user> data
    chmod g+w data


Personal notes
================================================

to set the default umask to 027 (decimal equivalent 23) so that other group members can read files created by a user
    defaults write -g NSUmask -int 23

first backup your netinfo database:
sudo cp -Rp /private/var/db/netinfo/local.nidb /private/var/db/netinfo local.nibak
......this should all be on one line with a space between netinfo and local.nibak

then to add a user to a group
sudo niutil -appendprop / /groups/groupname users username

to delete a user from a group
sudo niutil -destroyval / /groups/wheel users username

Date period tests
================================================

2 Jan 2004 - 5 Fev 2005            dat1<dat2
2          - 5 Fev 2005 +Fev +2005 dat1<dat2
2 Jan      - 5 Fev 2005      +2005 dat1<dat2
  Jan      - 5 Fev 2005      +2005 int1<dat2
  Jan 2004 - 5 Fev 2005            int1<dat2
      2004 - 5 Fev 2005            int1<2005


2 Jan 2004 -   Fev 2005            dat1<int2
2          -   Fev 2005 NON
2 Jan      -   Fev 2005      +2005 dat1<int2
  Jan      -   Fev 2005      +2005 int1<int2
  Jan 2004 -   Fev 2005            int1<int2
      2004 -   Fev 2005            int1<int2


2 Jan 2004 -       2005
2          -       2005 NON
2 Jan      -       2005 NON     
  Jan      -       2005 NON
  Jan 2004 -       2005
      2004 -       2005
      
x xxx xxxx - 2          NON
x xxx xxxx - 2 Fev      NON
-----------------------------------------------
2 Jan 2004
2          - . ... .... NON
2 Jan      - . ... .... NON
  Jan      - . ... .... NON
  Jan 2004
      2004
