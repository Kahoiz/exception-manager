

## Exception manager

The Exception manager is a lightweight processing tool for handling exceptions in Laravel applications. The tool is designed to be used in conjunction with a package called [exception-logger](https://github.com/Kahoiz/exception-logger.git) and is intended to be used in production. 

- Processes exceptions logged by the exception-logger package by.
  - Analysing exceptions and uses a complex algorithm to determine if theres been a spike.
  - Sending notifications to the configured channels.
- The tool is
  - Message broker agnostic as the tool and package uses Laravel's queue system.
  - Database agnostic if need be.


## Installation

The application works on its own, however it doesnt do anything if the message broker is empty.
- add the **[exception-logger](https://github.com/Kahoiz/exception-logger)** package to a project of your choosing and append the middleware within to your entire application.
- change the messaging credentials to your own in the .env.
  All unhandled thrown exceptions will now be sent to the broker.
  
  # Now for the exception manager
  
- Copy .env.example to .env
- Change database and messaging credentials to your own in the .env.
- add **SLACK_BOT_USER_OAUTH_TOKEN** and **SLACK_BOT_USER_DEFAULT_CHANNEL** keys with your own values in .env if you use slack.
- If you're using laradock, configure the application in nginx and docker-compose.yaml.
- If not, run **php artisan serve** in the cmd of the project.
- run **php artisan migrate**.
- Then run **php artisan schedule:work** to start the worker. It runs every 5 minutes and clears the queue for analysis.
- Every 5 minutes, an analysis will then run on your data and create ema_history data in the table with the same name. A spikeRules dataset will be created for each application using the package.
- If the spikerules dataset arer beached, further analysis will happen and a notification will be sent to the slack channel chosen in the above step.




### Premium Partners

- **[Benjamin Christiansen](https://github.com/Kahoiz)**
- **[Mathias Rasmussen](https://github.com/GuRLiG)**


