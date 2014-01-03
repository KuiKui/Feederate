# Installation

### Clone du projet :

```shell
$ cd /path/to/your/workspace
$ git clone git@github.com:KuiKui/Feederate.git
$ cd Feederate
```

### Lancement de Vagrant :

Installer vagrant en suivant les instruction de mon [projet chez C2iS](https://github.com/c2is/VagrantBoxes/tree/master/your-lamp-server#your-custom-lamp-server) puis charger l'environnement :

```shell
$ vagrant up
```

S'il y a un problème lors du lancement de Vagrant, il faut [relancer VirtualBox](https://coderwall.com/p/ydma0q).

### Accès au serveur de dev :

L'accès au serveur http se fait à l'adresse `http://localhost:8888`

L'accès au serveur de dev se fait par ssh :

```shell
$ vagrant ssh
```

Les sources du projet sont dans le répertoire `/vagrant`

```
$ cd /vagrant
```

### Installation des vendors :

```shell
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

Les paramètres par défaut de la configuration du projet sont correctement pré-remplis, il suffit donc de laisser les valeurs par défaut (en appuyant sur Enter).

### Création du modèle:

```shell
$ php app/console doctrine:database:create
$ php app/console doctrine:schema:create
$ php app/console doctrine:fixtures:load
```
