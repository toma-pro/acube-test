# ACube API

**Time spent on the test : between 2h30 and 3h** 

## Good to know 

* This project is build on top of Dunglas' Symfony Docker
  * I didn't clean everything, 
  * though, I try to remove useless parts for this test (DB stuff, ...),
* I did not manage security related things like
  * Put the API behind a Symfony Firewall (with a JWT for example)
  * I did not put a Rate Limiter, which could be a great feature to add.
* I set up a quick conversion system based on `ConverterInterface`
* I installed GD extension (required by PHPSpreadsheet)

### General workflow

* You post a file through an API HTTP request
* If everything is validated by the server, the file is queued for conversion
* A Message handler is dedicated to handle the conversion request and challenge a file conversion removing the original file
* When the state is *FINISHED*, you can download the file within a dedicated API route.

## Setup

As this project is based on FrankenPHP/Caddy image, only thing to do is configuring available port to expose the Docker image (if conflicting with 80/443).

This project use a `SQLite` (convenient here to prevent extra-containers) database and `Doctrine Migrations`. 
In order to finish the setup, please run inside the `PHP container` : 

```php
$ docker compose exec -it php bash
> bin/console d:m:m
> bin/console messenger:setup-transports # auto setup is disabled on .env
```

## Running 

The project have the following routes :

```php
/api/convert [POST]
query: format=(json|xml)
formdata: file=(file)

/api/job/:id [GET]

/api/job/:id/download [GET]
```

Call the /api/convert passing the correct parameters. Then, run the worker thanks to the following command : 

```php 
$ docker compose exec -it php bash
> bin/console messenger:consume
```

Then to download the result : 

```php
GET /api/job/:yourid/download
```

## Some things that could have been improved

With more time : 

* This conversion API expose a route for polling, a webhook notification could have been great
* Implementing a fail-over strategy on the Worker and manage the failsafe
* Handling complex characters to prevent fail on conversion. 
* Implementing the `Lock Component` with a `Key` instead of preventing duplicate with db check
