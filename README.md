# Activity Management Application API

Enables the management of group rotations during multi-stand activities.

- The application utilizes Docker to containerize PHP and PHPMyAdmin, thus necessitating Docker Desktop or Docker Engine (CLI).

## Setting up the application locally

__1__ - Rename the `mysql-sample` folder to `mysql`. Database is stored in this folder for data persistence.
  
__2__ - Configure your database access path and user in the  `.env` file located in the `app/` directory. For good practice, save this file as  `.env.local` (it will be ignored).

```php
DATABASE_URL="mysql://symfony:symfony@127.0.0.1:3306/rotation_app?serverVersion=8.0.32&charset=utf8mb4"
```

_IMPORTANT: `127.0.0.1` does not function within containers if your Symfony app is also containerized. Instead, you need to use the database container name from the `docker-compose.yaml` file:_

e.g : ('127.0.0.1' must be replaced by 'database'  )

```yaml
# docker-compose.yaml
  database:
    container_name: database
```

```php
# .env
DATABASE_URL="username:password@database:port/database_name"
```

__3__ - At the project root, build and launch PHP and PHPMyAdmin containers using the following command with Docker

```shell
docker-compose up --build
```

__4__ - Install the dependencies in the app/ directory

```shell
cd app && composer install
```

__5__ - Start the Symfony server (add the -d flag to run in the background)

```shell
symfony serve
```

### Access the application in the browser

__PHPMyAdmin__ : 127.0.0.1:8899
__Application__ : 127.0.0.1:8000
