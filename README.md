# Lawyer booking system
_A technical task for a job interview_

## Project Request
Develop a web-based application for citizens and lawyers.
The application is an online registry for citizens who want to make appointments with lawyers


## Description
   The project is realised with Symfony 5, PHP 7.4 and Docker

![project screenshot][screenshot_01]

**Docker**
![project screenshot][screenshot_02]

## Installation
1. Clone the project from the repo
    ```
    git clone git@github.com:fantomas/lawyer-booking-system.git
    ```
2. enter in the project `cd lawyer-booking-system`
3. setup a local vhost `127.0.0.1   lawyers.local`
4. run docker containers `docker-compose up -d`
5. get inside php container `docker-compose exec php bash`
5. create the DB `php bin/console doctrine:database:create`
5. load migrations `php bin/console doctrine:migrations:migrate`

## Notes
1. you can see a demo on [youtube](https://youtu.be/IvkMq7J7SsI)


[screenshot_01]: https://raw.githubusercontent.com/fantomas/lawyer-booking-system/master/symfony/public/img
/UI.png "Screenshot"
[screenshot_02]: https://raw.githubusercontent.com/fantomas/lawyer-booking-system/master/symfony/public/img
                 /docker_containers.png "Screenshot"
