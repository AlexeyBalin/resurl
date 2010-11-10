resURL 0.1.1 is based on original code by lilURL http://lilurl.sourceforge.net

resURL is a simple PHP/Redis app that works basically like tinyurl.com,
allowing you to create shortcuts on your own server.

-----------------------------------------------------------------------

To install:

1. Edit the configuration file includes/conf.php to suit your needs.

2. Set up mod_rewrite, if necessary

      (( a .htaccess file with the lines:
   
         RewriteEngine On
         RewriteRule (.*) index.php
   
        should suffice ))

