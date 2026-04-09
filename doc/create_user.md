# Create Admin User (aropixel:admin:create-user)

The `AropixelAdminBundle` provides a command to quickly create a new administrative user for your application. This user is automatically assigned the `ROLE_SUPER_ADMIN` role.

## Usage

To create a new admin user, run the following command:

```bash
php bin/console aropixel:admin:create-user
```

The command can be run in **interactive mode** or by providing **options**.

### Interactive Mode

If you run the command without options, it will ask you for:
1.  **Email**: The email address of the new administrator.
2.  **First Name**: The administrator's first name.
3.  **Last Name**: The administrator's last name.

### Using Options

You can also provide the information directly via command-line options:

```bash
php bin/console aropixel:admin:create-user --login=admin@example.com --first_name=John --last_name=Doe --password=secret
```

Available options:
-   `--login`: The user's email address.
-   `--first_name`: The user's first name.
-   `--last_name`: The user's last name.
-   `--password`: (Optional) The user's password.

## Behavior

> [!WARNING]
> By default, if the login is `admin`, the password defaults to `admin`.
> **Never use the default `admin/admin` credentials in a production environment.**

### Non-Interactive Mode
When running the command in non-interactive mode (e.g., in a CI/CD pipeline or setup script with `--no-interaction`), you **must** provide the required options (`--login`, `--first_name`, `--last_name`, `--password`).

**Special case for Development:**
In the `dev` environment (where `APP_ENV=dev`), you can run the command without any options in non-interactive mode to create a default `admin/admin` account:
```bash
# Works only in dev environment
php bin/console aropixel:admin:create-user --no-interaction
```
In any other environment (e.g., `prod`), the command will fail if you try to use the default `admin/admin` credentials in non-interactive mode, as a security measure.

### Activation Email
-   If the login provided is **not** "admin", the command will attempt to send an activation email to the user so they can set their own password and activate their account.
-   If the login is "admin", the account is automatically enabled and initialized.

### Password
-   If you don't provide a password via the `--password` option and the login is "admin", the default password will be "admin".
-   If you provide a login other than "admin" and don't provide a password, the user will need to set it via the activation email.

### Validation
The command validates the email format and ensures that required fields (First Name, Last Name) are not empty. It also checks if a user with the same email already exists in the database.
