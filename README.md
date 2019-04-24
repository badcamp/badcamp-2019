# badcamp-2019

[![CircleCI](https://circleci.com/gh/badcamp/badcamp-2019.svg?style=shield)](https://circleci.com/gh/badcamp/badcamp-2019)
[![Dashboard badcamp-2019](https://img.shields.io/badge/dashboard-badcamp_2019-yellow.svg)](https://dashboard.pantheon.io/sites/77e169f6-098e-4e68-b9b1-67d0b7cc2f02#dev/code)
[![Dev Site badcamp-2019](https://img.shields.io/badge/site-badcamp_2019-blue.svg)](http://dev-badcamp-2019.pantheonsite.io/)

## Designs

- [Zeplin](https://app.zeplin.io/project/5c9a927bca007d05b8951ac2/dashboard)
- [Sketch](https://sketch.cloud/s/q9j5d)

## Bootstrapping Your Dev Environment

You have your choice of local environments, but since we have build-in support for Docksel, Docksal is the quickest way to get started.  Getting ddev, Lando or similar is an exercise for the Reader :-) We welcome documentation for other environments if anyone wants to contribute these. 

Steps:

- [Install Docksal for your platform] (https://docksal.io/installation/).

- Use git to pull the website repo (https://github.com/badcamp/badcamp-2019)

- In your Pantheon account (you'll want to create one if you don't have one already) you'll need to create a token for a secure link between your install and the Pantheon dev and test environments.  Once you do this, you should talk to Sean or some other designated adult, who will "bless" your Pantheon account into the BADCamp dev group.

- Add settings to your badcamp-2019 install. These go in your `.docksal` directory at the base of the repo. The file is called `docksal-local.env` and looks like this on my install:  
```
SECRET_TERMINUS_TOKEN="YOUR-PANTHEON-TOKEN-HERE"
NGINX_DRUPAL_FILE_PROXY_URL=https://dev-badcamp-2019.pantheonsite.io/


- Start up your Docksal server: `fin system start`

- cd' into your `badcamp-2019` directory, start up your virtual environment, and start installing drupal and other dependencies.
```
fin start
fin site-init

- Docksal now downloads docker images for your install, and runs composer to download the right version of Drupal and initialize it. It will also pull down a current (dev?) database from Pantheon and load it for you.

- You now have a Drupal environment, but most likely it isn't quite ready. For one thing, the CSS will likely be messed up. In addition, the database's cache is stale enough to keep the site from coming up. Both issues are easy to fix.

- To fix the CSS issue, you need to set up gulp to compile your CSS. You'll need to make sure gulp is installed in the containers, and you'll need to run gulp periodically to recompile your CSS, as so:
```
fin exec npm install -g gulp-cli
cd web/themes/custom/bay_area_camp
fin exec npm install
fin gulp

- To resolve the cache problem, rebuild your cache in the environment:
 `fin drush cr`


- At this point, your environment should be working.  By default, Docksal makes your local install available as `http://badcamp-2019.docksal/`. Go thee ahead and start contributing!

- We work by a standard Github-style workflow, so fork the badcamp/badcamp-2019 site into your Github account, link it to your local install using `git remove add LOCALNAME YOUR-GIT-SSH-LINK-TO-REPO`. Create an issue on badcamp/badcamp-2019, make your changes locally, and create a Pull Release (PR) with your change. Details how to do this are on Github, although any of us will help you get started.




