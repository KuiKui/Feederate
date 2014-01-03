# Description

Présentation de la répartition par personne morale et par commune ainsi que l'évolution, en montant et en pourcentage, des aides perçues au titre de la [Politique Agricole Commune](http://fr.wikipedia.org/wiki/Politique_agricole_commune) sur le territoire français en 2010, 2011 et 2012. Les données utilisées proviennent de la [plateforme ouverte des données publiques françaises](http://www.data.gouv.fr/fr/dataset/publication-des-montants-des-aides-percus-par-les-personnes-morales-au-titre-de-la-politiqu-00000000).

# Installation

### Installation de VirtualBox et Vagrant

Voir mon [projet chez C2iS](https://github.com/c2is/VagrantBoxes/tree/master/your-lamp-server#your-custom-lamp-server).

### Clone du projet :

```shell
$ cd /path/to/your/workspace
$ git clone git@github.com:kuikui/pac.git
$ cd pac
```

### Lancement de Vagrant
```shell
$ vagrant up
$ vagrant ssh
$ cd /vagrant
```

S'il y a un problème lors du lancement de Vagrant, il faut [relancer VirtualBox](https://coderwall.com/p/ydma0q).

Le serveur http est visible sur `http://localhost/8888`

### Création de la base de données MySQL :

```shell
CREATE DATABASE pac DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL ON pac.* TO pac@localhost IDENTIFIED BY 'pac++';
FLUSH PRIVILEGES;
```

### Droits des répertoires cache et logs :

```shell
$ sudo chmod +a "_www allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
$ sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```

Ou :

```shell
$ sudo setfacl -R -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs
$ sudo setfacl -dR -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs
```

### Installation des vendors :

```shell
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

### Génération du modèle et du dump sql :

```shell
$ ./vendor/bin/propel-gen app/config/Propel main
```

Il faut exécuter 2 fois cette commande :disappointed_relieved:

### Imporation des données

Import du dump vide pour initialiser le projet :

```shell
$ mysql -upac -ppac++ pac < app/config/Propel/sql/Pac.Model.schema.sql
```

ou, import du dump complet contenant toutes les données :

```shell
$ gunzip < app/resources/Pac.sql.gz | mysql -upac -ppac++ pac
```

### Configuration du runtime de Propel :

```shell
$ cp app/config/Propel/runtime-conf.xml.dist app/config/Propel/runtime-conf.xml
```
Il faut ensuite éditer pour configurer avec les paramètres de connexion MySQL

### Imporation des données :

```shell
$ ./console parse 2010
$ ./console parse 2011
$ ./console parse 2012
```
