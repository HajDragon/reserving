# Docker setup for Reserving

Quick start (development):

1. Copy the example env for containers:

```bash
cp .env.docker .env.docker.local
```

2. Build and start the containers:

```bash
docker compose up --build
```

3. Install composer dependencies (if not in image):

```bash
docker compose run --rm app composer install
```

4. Run migrations:

```bash
docker compose run --rm app php artisan migrate
```

5. Visit the app at http://localhost:8080

Production image notes:

- The `docker/php/Dockerfile` is a simple base used for development and can be extended into a multi-stage production build as needed.

Security:

- Never commit real secrets. Use CI secrets or external secret managers for production builds.

MailHog for local email:

- `docker-compose.yml` includes a `mailhog` service for SMTP on port `1025`.
- The MailHog web UI is exposed on `http://localhost:18025` because `8025` is already used in this workspace.
- The Docker env file sets `MAIL_HOST=mailhog` and `MAIL_PORT=1025` for local development.
- Start everything with:

```bash
docker compose --env-file .env.docker up -d
```

Enable Xdebug (optional)

To enable Xdebug for local development, you can use the provided overlay which builds the `app` image with Xdebug and mounts a local `xdebug.ini`:

```bash
docker compose -f docker-compose.yml -f docker-compose.xdebug.yml up --build -d
```

This overlay sets the build-arg `INSTALL_XDEBUG=1` and mounts `docker/php/xdebug.ini` into the PHP container. The `docker/php/Dockerfile` supports this build arg and will install/enable Xdebug when set.

IDE tips:
- Point your IDE to `host.docker.internal` as the Xdebug client host (Windows/macOS). For Linux use the host gateway or container IP as appropriate.
- Default Xdebug port used here is `9003`.

