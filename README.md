# Test

This is a minimized version of the Voting API Widgets module designed to
illustrate (at least part of) this issue:
https://www.drupal.org/project/votingapi_widgets/issues/3265224

This module demonstrates that deleting a field that uses a permission_callback
does not delete the permission.

Steps to reproduce:
1. Put this module in the modules directory and enable it on admin/modules

2. Go to admin/structure/types/manage/article/fields and add a new field
of the type 'Field to be deleted'. Name it 'Stillthere' and accept the
next two screens.

3. Go to admin/people/permissions and give authenticated users the permission
'Vote on type node from bundle article in field field_stillthere'.

4. Run this in the database and you should see the data:
select * from config where name='user.role.authenticated' and data like '%stillthere%' \G

5. Go to admin/structure/types/manage/article/fields and delete the field.

6. Run the db command again and you'll still see the data. Running cron and
clearing cache won't change it.






