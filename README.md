TangoMan Kaamelott Videoboard Backend
=====================================

Awesome **TangoMan Kaamelott Videoboard Backend** is a dockerized api-platform restful backend for Kebab-case Kaamelott Videoboard.

📝 Notes
--------

Kaamelott Videoboard can be found here: [https://github.com/flazzarotto/kaamelott-videoboard](https://github.com/flazzarotto/kaamelott-videoboard)

🚀 Installation
---------------

### Step 1: Simply enter following command in your terminal

```bash
$ make up
```

💻 Dependencies
---------------

**TangoMan Kaamelott Videoboard Backend** requires the following dependencies:

- Docker
- Docker-compose
- Make

### 🐋 Docker

#### 🐧 Install Docker (Linux)

On linux machine enter following command

```bash
$ sudo apt-get install --assume-yes docker.io
```

#### 🔧 Configure Docker (Linux)

Add current user to docker group

```bash
$ sudo usermod -a -G docker ${USER}
```

> You will need to log out and log back in current user to use docker

> If your group membership is not properly re-evaluated, enter following command

```bash
$ newgrp docker
```

#### 🏁 Install Docker (Windows)

Download docker community edition installer from docker hub:

- [https://hub.docker.com/editions/community/docker-ce-desktop-windows](https://hub.docker.com/editions/community/docker-ce-desktop-windows)

#### 🍎 Install Docker (OSX)

Download docker community edition installer from docker hub:

- [https://hub.docker.com/editions/community/docker-ce-desktop-mac](https://hub.docker.com/editions/community/docker-ce-desktop-mac)

---

### 🐳 Docker Compose

#### 🐧 Install Docker Compose (Linux)

On linux machine you will need curl to install docker-compose with the following commands

```bash
$ sudo curl -L "https://github.com/docker/compose/releases/download/1.27.4/docker-compose-`uname -s`-`uname -m`" -o /usr/bin/docker-compose
$ sudo chmod uga+x /usr/bin/docker-compose
$ sync
```

---

### 🛠 Make

#### 🐧 Install Make (Linux)

On linux machine enter following command

```bash
$ sudo apt-get install --assume-yes make
```

#### 🏁 Install Make (Windows)

On windows machine you will need to install [cygwin](http://www.cygwin.com/) or [GnuWin make](http://gnuwin32.sourceforge.net/packages/make.htm) first to execute make script.

#### 🍎 Install Make (OSX)

Make should be available by default on OSX system, but you can upgrade make version with following command

```bash
$ brew install make
```

---

🔥 Usage
--------

Run `make` to print help

```bash
$ make [command] env=[env]
```

Available commands are: `help network remove-network install uninstall composer database cache nuke own`

🤖 Commands
-----------

#### help
```
$ make help
```
Print this help

### Docker-Compose Network
#### network
```
$ make network
```
Create tango network

#### remove-network
```
$ make remove-network
```
Remove tango network

### Symfony App Docker
#### install
```
$ make install
```
Install Symfony application in docker

#### uninstall
```
$ make uninstall
```
Uninstall app completely

#### composer
```
$ make composer
```
Composer install Symfony project

#### database
```
$ make database
```
Create database and schema

### Symfony Cache
#### cache
```
$ make cache
```
Clean cache

#### nuke
```
$ make nuke
```
Force delete cache

#### own
```
$ make own
```
Own project files

📤 Generate Schema from yaml
----------------------------

```bash
php -d memory-limit=-1 vendor/bin/schema generate-types ./src/ ./config/schema.yaml
```

📥 Import / export folders
--------------------------

```bash
./assets/imports
./assets/exports
```

✅ Continuous Integration
-------------------------

[![Build Status](https://travis-ci.org/TangoMan75/kaamelott-videoboard-backend.svg?branch=master)](https://travis-ci.org/TangoMan75/kaamelott-videoboard-backend) 
If you find any bug please report here : [Issues](https://github.com/TangoMan75/kaamelott-videoboard-backend/issues/new)

🤝 Contributing
---------------

If you find missing features, feel free to get in touch and contibute.

📜 License
----------

Copyrights (c) 2020 &quot;Matthias Morin&quot; &lt;mat@tangoman.io&gt;

[![License](https://img.shields.io/badge/Licence-MIT-green.svg)](LICENSE)
Distributed under the MIT license.

If you like **TangoMan Kaamelott Videoboard Backend** please star, follow or tweet:

[![GitHub stars](https://img.shields.io/github/stars/TangoMan75/kaamelott-videoboard-backend?style=social)](https://github.com/TangoMan75/kaamelott-videoboard-backend/stargazers)
[![GitHub followers](https://img.shields.io/github/followers/TangoMan75?style=social)](https://github.com/TangoMan75)
[![Twitter](https://img.shields.io/twitter/url?style=social&url=https%3A%2F%2Fgithub.com%2FTangoMan75%2Fkaamelott-videoboard-backend)](https://twitter.com/intent/tweet?text=Wow:&url=https%3A%2F%2Fgithub.com%2FTangoMan75%2Fkaamelott-videoboard-backend)

... And check my other cool projects.

[![LinkedIn](https://img.shields.io/static/v1?style=social&logo=linkedin&label=LinkedIn&message=morinmatthias)](https://www.linkedin.com/in/morinmatthias)
