RESTAURANT OPEN / CLOSE AND DAILY HISTORY

USING AN EXISTING DATABASE

1. Open http://localhost/phpmyadmin
2. Select the db_restoran database.
3. Choose Import.
4. Import update-operasional.sql only if the operations feature
   has not already been installed.
5. To translate the default categories and sample menu items,
   import translate-default-data-to-english.sql once.

HOW TO USE THE OPERATIONS FEATURE

1. Sign in:

   http://localhost/restoran-app/admin/login.php

2. Open Restaurant Operations.

3. Select Open Restaurant.
   The customer kiosk can now accept orders.

4. Before closing, make sure all active orders are completed
   or cancelled.

5. Select Close Restaurant.
   The kiosk will display a Restaurant Closed screen.

6. Open Daily History to view:

   - Business date
   - Opening and closing times
   - Administrator who opened and closed the restaurant
   - Opening and closing notes
   - Number of sessions
   - Total orders
   - Paid orders
   - Cancelled orders
   - Paid revenue

IMPORTANT

- One business day may contain more than one opening session.
- The restaurant cannot be closed while an order is Waiting,
  Processing, or Ready.
- Orders created before the operations feature was installed may not
  have a session_id and therefore may not appear in operational history.
