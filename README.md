# CodeIgniter 4 Application Example

* CodeIgniter 4
* React PHP
* Redis React

# Servers

The following names have been chosen for the servers:
* Development: dev.api.coaster.local
* Production: prod.api.coaster.local

You have to add the following entries to your `/etc/hosts` file.

If you want to change the names, the following files have to be changed:
* `env.dev` and `env.prod` in the root directory
* `resources/http-client.env.json` if you use the HTTP client in PHPStorm
* `docker/nginx/nginx.conf`

# Allow IP for development

If you want to resctrict to selected IP addresses, you must enter them in `env.dev`:

``
custom.allowIps = '192.168.65.0/24 172.21.0.0/24'
``

# CLI command

You can run the CLI command with the following command:

```
make exec-dev
```
and
```
php spark monitor:stats
```
