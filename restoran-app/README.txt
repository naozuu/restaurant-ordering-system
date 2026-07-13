RESTAURANT ORDERING APPLICATION

INSTALLATION

1. Copy the "restoran-app" folder to:

   C:\xampp\htdocs\restoran-app

2. Start Apache and MySQL from the XAMPP Control Panel.

3. Open phpMyAdmin:

   http://localhost/phpmyadmin

4. For a new installation, import:

   database.sql

5. For an existing Indonesian installation, run the required
   operational migration first if it has not been installed:

   update-operasional.sql

6. To translate the default categories and original sample menu data,
   import:

   translate-default-data-to-english.sql

   Run this file only once. Custom menu names are not translated
   automatically and can be edited from the admin panel.

7. Create the first administrator account:

   http://localhost/restoran-app/admin/buat-admin.php

8. After creating the administrator account, delete:

   admin/buat-admin.php

9. Administrator login:

   http://localhost/restoran-app/admin/login.php

10. Customer kiosk:

    http://localhost/restoran-app/kiosk/

NOTES

- The interface is in English.
- Internal database column names and status enum values remain unchanged
  to preserve compatibility with the existing database.
- The interface converts those internal status values into English labels.
- Menu images are stored in assets/images.
