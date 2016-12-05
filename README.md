# rocket

## To get started
This project is in two parts the front-end angular application and a backend symfony app. See steps get the project up and running.
### Install dependencies
```sh
$ cd frontends/rocket-app
$ npm install
$ bower install
$ gulp build
$ cd ../api
$ composer install
```
### Launch Docker container
Ensure you are in the project root
```sh
$ docker-compose up --build
```
All mysql database tables and permissions should be created a part of the docker-compose command
