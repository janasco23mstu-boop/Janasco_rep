# 4aanasco — Render + Aiven deployment

This project is configured as a PHP 8.2 / Apache Docker web service for Render and a MySQL database hosted on Aiven.

## 1. Create the Aiven MySQL service

1. Create a MySQL service in Aiven and wait until it is running.
2. In Aiven's connection information, note the host, port, database name, username, and password.
3. Download the service's CA certificate from Aiven. In Render, add it as a **Secret File** named `ca.pem`. The app expects it at `/etc/secrets/ca.pem` by default. If you use another path, set `DB_SSL_CA` to that path.
4. Select the database you intend to use in Aiven. The SQL file intentionally does not create a database or run `USE`, because the Aiven database should be selected/created in Aiven first.

## 2. Import the database schema and sample data

Use the Aiven-provided MySQL connection details and import `aiven_mysql.sql` into the target database. For example, from a terminal with the MySQL client installed:

```bash
mysql --host YOUR_AIVEN_HOST --port YOUR_AIVEN_PORT --user YOUR_AIVEN_USER --password --ssl-mode=VERIFY_CA --ssl-ca ca.pem YOUR_AIVEN_DATABASE < aiven_mysql.sql
```

Enter the Aiven password when prompted. Replace the placeholders with values from Aiven. Do not commit `ca.pem`, passwords, or `.env` files to GitHub. If your local MySQL client does not support `--ssl-mode`, use Aiven's connection instructions for your client or import the SQL using a compatible MySQL GUI.

The SQL includes the existing sample records from the supplied project. Review those records and change/remove any sample accounts before using the site publicly.

## 3. Push the project to GitHub

Commit the Dockerfile, updated `db.php`, and `aiven_mysql.sql` along with the PHP application files. Do not commit credentials or the Aiven CA certificate.

## 4. Create the Render web service

1. In Render, choose **New + → Web Service** and connect the GitHub repository.
2. Choose **Docker** as the runtime and deploy the branch containing this Dockerfile.
3. In the Render service's **Environment** settings, add:
   - `DB_HOST` = Aiven host
   - `DB_PORT` = Aiven port
   - `DB_NAME` = database name selected in Aiven
   - `DB_USER` = Aiven username
   - `DB_PASSWORD` = Aiven password
   - `DB_SSL_CA` = `/etc/secrets/ca.pem` (optional because this is the default)
4. Add the downloaded CA certificate in Render under **Secret Files**, filename `ca.pem`.
5. Deploy. The Dockerfile configures Apache to listen on port `10000`.

The application also accepts the equivalent environment variable names `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, and `MYSQL_SSL_CA`.

## Important notes

- This app uses MySQLi and is configured to require TLS with a readable CA certificate for non-local database hosts.
- Render's standard managed database is PostgreSQL, so this setup uses Aiven MySQL to keep the app's existing MySQL queries.
- The service's local filesystem is not durable storage for newly uploaded images. Existing images included in the repository will be present after build, but new user/instructor image uploads may disappear after redeploy/restart. For durable uploads, configure persistent storage or move images to an object-storage service.
- The supplied SQL contains sample user records and password hashes. Verify the intended credentials and replace sample/default accounts before making the site public.
- This package has not been tested against your actual Aiven credentials/service. Verify the deployment logs and Aiven connection settings if connection fails.
