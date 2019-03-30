# PHP-Application
The sports league application is about accessing the content of various mysql table based on the roles and permission. For example. an admin can access all the resources and can edit/add/delete/view and perform any operation they want. The league manager has access that are limited to the league they belong to. Further, coach, team manager, and parent are bounded by their own team. They can perform operations only on their team.

Entry point for the project: index.html

Features:
1. Role based access
2. Used ajax for login, team, and schedule page.
3. Implemented recaptcha 2.0
4. Implemented generic PDO function that handles all the database operation.
5. Used session to save all the users information.
6. Wrote a generic sanitization function to sanitize input.
7. Wrote a generic validator function that validates input.
8. Performed both client and server side validation wherever required.
9. Logged the login/logout information, exception, request data, and other important information.
10. Properly commented the code.
