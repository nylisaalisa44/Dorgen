<p align="center">
    <h1 align="center">Yii 2 Dorgen</h1>
    <br>
</p>

## About the Project
Yii 2 Dorgen is a powerful and flexible web application template built on the Yii2 framework. It is designed for modern development workflows and provides a solid foundation for web application projects.

---

## Technologies Used
This project leverages the following technologies:

- **PHP 8.1**: The core programming language.
- **Yii 2 Framework**: A high-performance PHP framework.
- **Docker**: For containerized development and deployment.
- **Composer**: Dependency management.
- **Nginx**: Web server for serving the application.
- **MariaDB**: Supported databases.
- **Bootstrap**: For frontend panel design and responsive UI.
- **Git**: Version control system.
- **RabbitMQ**: For message queues and asynchronous tasks.
- **Redis**: For caching and session storage.

---

## Directory Structure

      assets/             contains assets definition
      components/         contains componenets system
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources

---

## Requirements

The minimum requirement for this project template is that your Web server supports PHP 8.1.

---

## Installation

### Install with Docker

Start the container

    docker compose up -d

Rebuild all containers

    docker compose up -d --build

Run the yii Migration

    docker compose run --rm php php yii migrate

You can then access the application through the following URL:

    http://localhost

Import test DB .sql from docker folder

### For development and operations teams

Update your vendor packages

    docker compose run --rm php composer update --prefer-dist

**NOTES:**
- Minimum required Docker engine version `20.10` for development (see [Performance tuning for volume mounts](https://docs.docker.com/docker-for-mac/osxfs-caching/))

**Panel**

    http://localhost/panel/default/login
    admin
    admin

**Change password to panel**
    
    app/models/User.php:17