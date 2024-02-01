# Activity Management Application API

Enables the management of group rotations during multi-stand activities.

- The application utilizes Docker to containerize PHP and PHPMyAdmin, thus necessitating Docker Desktop or Docker Engine (CLI).

## Setting up the application locally

__1__ : The MySQL data will be stored in the `mysql` folder so you need to rename the `mysql-sample` folder to `mysql`.
  
__2__ : Configure your database access path and user in the  `.env` file located in the `app/` directory. For good practice, save this file as  `.env.local`

Example:
DATABASE_URL="username:password@127.0.0.1:port/database_name"

```php
DATABASE_URL="mysql://symfony:symfony@127.0.0.1:3306/rotation_app?serverVersion=8.0.32&charset=utf8mb4"
```

_IMPORTANT: `127.0.0.1` does not function within containers if your Symfony app is also containerized. Instead, you need to use the database container name from the `docker-compose.yaml` file:_ :

e.g : (database // replace 127.0.0.1)

```yaml
  database:
    container_name: database
```

__3__ : At the project root, build and launch PHP and PHPMyAdmin containers using the following command with Docker :

```shell
docker-compose up --build
```

__4__ : Navigate to the directory (cd app/) and start the Symfony server (add the -d flag to run in the background):

```shell
symfony serve
```

### Access the application in the browser

__PHPMyAdmin__ : 127.0.0.1:8899
__Application__ : 127.0.0.1:8000
