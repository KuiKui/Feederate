# Description

Rss Reader

# Installation

### Installation de VirtualBox et Vagrant

Voir mon [projet chez C2iS](https://github.com/c2is/VagrantBoxes/tree/master/your-lamp-server#your-custom-lamp-server).

### Clone du projet :

```shell
$ cd /path/to/your/workspace
$ git clone git@github.com:kuikui/feed.git
$ cd feed
```

### Lancement de Vagrant
```shell
$ vagrant up
$ vagrant ssh
$ cd /vagrant
```

S'il y a un problème lors du lancement de Vagrant, il faut [relancer VirtualBox](https://coderwall.com/p/ydma0q).

Le serveur http est visible sur `http://localhost:8888`

### Installation des vendors :

```shell
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

### Création du modèle:

```shell
$ php app/console doctrine:database:create
$ php app/console doctrine:schema:create
```
