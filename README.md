# Projet web4all

## Livrable

Le bloc termine sans surprise par une soutenance. Durant cette dernière, vous allez vous positionner comme le prestataire (Web4All) qui vient montrer à son client CESI (le jury) le résultat de sa commande.

La soutenance peut être composée d'une petite présentation de 5 minutes et surtout d'une démonstration technique. Le temps étant compté, le jury pourra vous guider par ses questions pour vérifier telle ou telle spécificité (fonctionnelle comme technique). La séquence se terminera hors contexte par des questions/réponses individuelles permettant d'évaluer votre implication personnelle dans le projet.

## Cahier des charges du projet

La réalisation d'une application web pour les stages se trouve être un projet plein d'ambitions. Le site va permettre d'informatiser l'aide à la recherche de stages en regroupant toutes les offres de stage. Il permettra entre autres d'enregistrer les données des entreprises ayant déjà pris un stagiaire, ou qui en recherchent un.

Ceci facilitera l'orientation des nouveaux étudiants dans leurs recherches de stages.

Les offres de stage seront notamment enregistrées par compétences, ce qui permettra à l'étudiant de trouver un stage en rapport avec son profil. L'application doit fournir différentes interfaces à destination des différents profils d'utilisateurs.

Les profils d'utilisateurs sont l'administrateur, le pilote de promotion et l'étudiant. Parmi les fonctionnalités attendues figurent la gestion des rôles, la gestion des entreprises, la gestion des offres de stage et la gestion des candidatures. Selon le profil d'utilisateur, ce dernier pourra accéder à certains services et pas à d'autres. Seuls les administrateurs ont accès à l'ensemble des fonctionnalités proposées par la plateforme (ou presque).

Ce cahier des charges laisse place à des interprétations, différentes options possibles et des champs de liberté. Vous devez analyser, faire ressortir les zones d'ombre, les options et autres incertitudes de manière à réfléchir à la meilleure ligne de conduite pour votre groupe et ainsi le proposer à votre client.

Outre les fonctionnalités techniques, votre site devra s'adapter au mieux en fonction de l'équipement de l'utilisateur (responsive) et respecter les bonnes pratiques de codage côté back-end comme front-end.

Le site web doit être conçu pour répondre aux critères d’optimisation SEO de base (structure HTML, mots-clés, performance, sécurité) et chaque section importante du site doit inclure des balises meta adéquates.

Par ailleurs il va sans dire que vous veillerez à la conformité légale de votre site, notamment à ce que les mentions légales obligatoires soient présentes.

À noter qu'utiliser un serveur de base de données commun au groupe (dans la mesure du possible) simplifiera grandement le travail de l'équipe.

# Etat d'avancement du projet

## 🚀 Glossaire

⬜ À faire - 🟡 En cours - ✅ Terminé - ❌ Bloqué

## 📊 Tableau de suivi des spécifications fonctionnelles

| ID    | Catégorie       | Fonctionnalité                        | Description courte                                | Données principales                                                              | Statut |
| ----- | --------------- | ------------------------------------- | ------------------------------------------------- | -------------------------------------------------------------------------------- | ------ |
| SFx1  | Gestion d'accès | Authentification & gestion des accès  | Connexion / Déconnexion + gestion des rôles       | email, mot de passe, rôle                                                        | ✅      |
| SFx2  | Entreprises     | Rechercher & afficher entreprise      | Recherche multicritères + avis + offres liées     | nom, description, contact, nb candidatures, moyenne évaluations                  | ✅      |
| SFx3  | Entreprises     | Créer entreprise                      | Création fiche entreprise                         | nom, description, contact                                                        | ✅      |
| SFx4  | Entreprises     | Modifier entreprise                   | Modification fiche entreprise                     | nom, description, contact                                                        | ✅      |
| SFx5  | Entreprises     | Évaluer entreprise                    | Notation entreprise                               | évaluation                                                                       | ✅      |
| SFx6  | Entreprises     | Supprimer entreprise                  | Suppression entreprise                            | -                                                                                | ✅      |
| SFx7  | Offres          | Rechercher & afficher offre           | Recherche multicritères offre                     | entreprise, titre, description, compétences, rémunération, date, nb candidatures | ✅      |
| SFx8  | Offres          | Créer offre                           | Création offre                                    | compétences, titre, description, entreprise, rémunération, date                  | ✅      |
| SFx9  | Offres          | Modifier offre                        | Modification offre                                | compétences, titre, description, entreprise, rémunération, date                  | ✅      |
| SFx10 | Offres          | Supprimer offre                       | Suppression offre                                 | -                                                                                | ✅      |
| SFx11 | Offres          | Statistiques offres                   | Carrousel indicateurs clés                        | répartition durée, top wishlist, total offres, moyenne candidatures              | ✅      |
| SFx12 | Pilotes         | Rechercher & afficher pilote          | Recherche compte pilote                           | nom, prénom                                                                      | ✅      |
| SFx13 | Pilotes         | Créer pilote                          | Création compte pilote                            | nom, prénom                                                                      | ✅      |
| SFx14 | Pilotes         | Modifier pilote                       | Modification compte pilote                        | nom, prénom                                                                      | ✅      |
| SFx15 | Pilotes         | Supprimer pilote                      | Suppression compte pilote                         | -                                                                                | ✅      |
| SFx16 | Étudiants       | Rechercher & afficher étudiant        | Recherche + état recherche stage                  | nom, prénom, email                                                               | ✅      |
| SFx17 | Étudiants       | Créer étudiant                        | Création compte étudiant                          | nom, prénom, email                                                               | ✅      |
| SFx18 | Étudiants       | Modifier étudiant                     | Modification compte étudiant                      | nom, prénom, email                                                               | ✅      |
| SFx19 | Étudiants       | Supprimer étudiant                    | Suppression compte étudiant                       | -                                                                                | ✅      |
| SFx20 | Candidatures    | Postuler à une offre                  | Envoi CV + LM                                     | offre, CV, LM                                                                    | ✅      |
| SFx21 | Candidatures    | Voir candidatures étudiant            | Liste des offres postulées                        | offre, CV, LM                                                                    | ✅      |
| SFx22 | Candidatures    | Voir candidatures des élèves (pilote) | Liste des candidatures des étudiants du pilote    | offre, CV, LM                                                                    | ⬜      |
| SFx23 | Wish-list       | Afficher wish-list                    | Voir offres ajoutées                              | -                                                                                | ✅      |
| SFx24 | Wish-list       | Ajouter à wish-list                   | Ajouter offre                                     | offre                                                                            | ✅      |
| SFx25 | Wish-list       | Retirer de wish-list                  | Supprimer offre de la liste                       | offre                                                                            | ✅      |
| SFx27 | Transversal     | Pagination                            | Pagination sur listes                             | -                                                                                | ✅      |
| SFx28 | Transversal     | Mentions légales                      | Conformité légale                                 | -                                                                                | ✅      |
| BONUS | Bonus           | PWA (Accès mobile)                    | Installation comme app mobile                     | -                                                                                | ✅      |



## 🛠️ Tableau de suivi des spécifications techniques

| ID    | Exigence               | Description                                                      | Statut |
| ----- | ---------------------- | ---------------------------------------------------------------- | ------ |
| STx1  | Architecture MVC       | MVC obligatoire                                                  | ✅      |
| STx2  | Qualité code           | HTML5 sémantique, validation W3C, CSS structuré, POO PHP, PSR-12 | ✅      |
| STx3  | Validation formulaires | Validation Front (HTML/JS) + Back (PHP)                          | ✅      |
| STx4  | Pas de CMS             | Aucun CMS autorisé                                               | ✅      |
| STx5  | Frameworks             | Pas de React/Vue/Laravel/Symfony                                 | ✅      |
| STx6  | Stack technique        | Apache, HTML/CSS/JS, PHP, SGBD SQL                               | ✅      |
| STx7  | Template engine        | Moteur de template backend obligatoire                           | ✅      |
| STx8  | Clés étrangères        | Utilisation FK en base                                           | ✅      |
| STx9  | Vhost statique         | Vhost pour ressources statiques                                  | ✅      |
| STx10 | Responsive             | Responsive + menu burger                                         | ✅      |
| STx11 | Sécurité               | Cookies sécurisés, hash mdp, anti SQLi/XSS/CSRF, HTTPS           | ✅      |
| STx12 | SEO                    | Meta, Hn, alt, <3s chargement, sitemap, robots.txt               | ✅      |
| STx13 | Routage                | Système de routing backend                                       | ✅      |
| STx14 | Tests unitaires        | Tests PHPUnit sur au moins 1 contrôleur                          | ✅      |

## 🔥 Stack technique

- ✅ : Apache2
- ✅ : Postgresql
    - : ✅ Base de données
- ✅ : PHP
- ✅ : Twig
- ✅ : PHPUnit


# VM Ubuntu 24.04 LTS virtualisée dans Oracle VirtualBox

Etapes préliminaires :

## Création de la VM Ubuntu 24.04 LTS

### Télécharger l'image ISO de Ubuntu

L'image ISO permettant l'installation se situe à cette adresse :

[Download](https://ubuntu.com/download/desktop)

récupérer le fichier `ubuntu-24.04.4-desktop-amd64.iso`

### Créer une nouvelle VM VirtualBox

#### Prérequis

S'assurer que VirtualBox 7.2.6 ([Download](https://www.virtualbox.org/wiki/Downloads)) soit bien installé sur le PC ainsi que "Oracle VirtualBox Extension Pack" ("VirtualBox 7.2.6 Extension Pack" -> Accept and download)

Un fois VirtualBox installé, installer l'extension pack.

Dans VirtualBox, Fichier - Outils - Extensions

Puis "Install" (-> pointer sur le fichier `Oracle_VirtualBox_Extension_Pack-7.2.6.vbox-extpack` fraîchement téléchargé)

#### Paramétrage de la nouvelle VM

Dans VirtualBox, faire : Machine - Nouvelle

- VM Name : le_nom_de_la_VM
- VM Folder : par défaut
- ISO Image : <non sélectionné>
- OS : Linux
- OS Distribution : Other Linux
- OS Version : Other Linux (64 bits)
- Specify virtual hardware
    - Base Memory : 4086 Mo
    - Number of CPU : 2
- Specify virtual hard disk
    - Disk Size : 20 Go


Une fois la VM définie, rajouter quelques paramètres :
- Affichage : Video Memory => 128 Mo
- Stockage : Cliquer sur "Controleur : IDE" puis sur le CDRom avec la croix verte (Add optical Drive)
    - Cliquer sur le bouton Ajouter
    - Sélectionner l'image ISO d'Ubuntu 24.04 LTS précédemment téléchargée (`ubuntu-24.04.4-desktop-amd64.iso`)
    - Cliquer sur "Ouvrir"
    - une fois la fenêtre fermée, Sélectionner-la puis cliquer sur "Choose"

Laisser tout le reste par défaut.

Démarrer la VM fraîchement paramétrée se laisser-vous guider pour l'installation jusqu'au bout.

Une fois fini on se retrouve avec une VM avec Ubuntu Desktop 24.04 LTS, de base, installée avec un user ({votre_user}) qui bénéficie des droits `sudo`.

## Les installations à faire

Dès que la VM a démarré, lancer un terminal et taper les commandes suivantes pour installer tous les paquest nécessaires au projet :

```bash
sudo apt update && sudo apt dist-upgrade -y
sudo apt install bzip2 tar gcc make perl terminator php apache2 postgresql tree net-tools libapache2-mod-php8.3 libapache2-mod-php php-pgsql git
sudo a2enmod php8.3
```

------- Partie optionnelle : uniquement à faire dans le cas d'une image cirtuelle VirtualBox ---------
Il vaut mieux installer les extensions VirtualBox afin (notamment) de bénéficier du "full screen" dans la VM.

Pour cela, Cliquer sur "Périphériques" - "Insérer l'image CD des additions invitées"

Puis dans la VM Ubuntu, dans un terminal :

```bash
cd /media/{votre_user}/VBox_GAs_7.2.6/
sudo ./VBoxLinuxAdditions.run 
reboot
```

On peut à présent passer en full screen.
------- Fin Partie optionnelle ---------

## Les paramétrages à faire

### Apache 2 avec HTTPS

Prérequis, installer la librairie libnss

```bash
sudo apt install libnss3-tools
```

On pourrais créer un certificat auto-signé et l'installer, mais cela va poser plusieurs problèmes :

- Le navigateur va bloquer dans un premier temps en demandant qu'on lui valide le certificat auto-signé
- comme nous avons deux domaines (web4all.local et static.web4all.local), il faudra le faire pour les deux
- le service worker https://web4all.local/sw.js ne pourra pas s'installer avec cette limitation et on ne pourra pas installer l'application PWA

La solution : utiliser `mkcert`

Il s'agit d'un projet qui installer dans l'environnement de développement un système de certificats de niveau supérieur faisant croire aux navigateurs locaux que le certificat qui a été généré est bon.

Pour cela, il faut télécharger le programme écrit en GO et compilé pour l'architecture amd64 ([mkcert-v1.4.4-linux-amd64](https://github.com/FiloSottile/mkcert/releases/download/v1.4.4/mkcert-v1.4.4-linux-amd64))

Puis lancer les commandes suivantes (**Attention, on ne met pas `sudo` devant pour une fois !!**)

```bash
$ mkcert -install
Created a new local CA 💥
The local CA is now installed in the system trust store! ⚡️
The local CA is now installed in the Firefox and/or Chrome/Chromium trust store (requires browser restart)! 🦊

$ mkcert web4all.local static.web4all.local

Created a new certificate valid for the following names 📜
 - "web4all.local"
 - "static.web4all.local"

The certificate is at "./web4all.local+1.pem" and the key at "./web4all.local+1-key.pem" ✅

It will expire on 23 June 2028 🗓
```

Il ne reste plus qu'à copier/déplacer les deux fichiers dans leurs emplacements finaux afin qu'apache puisse les utiliser.

```bash
sudo mv web4all.local+1.pem /etc/ssl/certs/web4all.crt
sudo mv web4all.local+1-key.pem /etc/ssl/private/web4all.key
```

Il ne reste plus qu'à mettre dans le fichier `/etc/apache2/sites-available/web4all.conf` : 

```
<VirtualHost *:80>
    ServerName web4all.local
    Redirect permanent / https://web4all.local/
</VirtualHost>

<VirtualHost *:80>
    ServerName static.web4all.local
    Redirect permanent / https://static.web4all.local/
</VirtualHost>

<VirtualHost *:443>
    ServerName web4all.local

    DocumentRoot /var/www/html/web4all/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/web4all.crt
    SSLCertificateKeyFile /etc/ssl/private/web4all.key

    <Directory /var/www/html/web4all/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Sécurité
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"

    ErrorLog ${APACHE_LOG_DIR}/web4all_error.log
    CustomLog ${APACHE_LOG_DIR}/web4all_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName static.web4all.local

    DocumentRoot /var/www/html/web4all/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/web4all.crt
    SSLCertificateKeyFile /etc/ssl/private/web4all.key

    <Directory /var/www/html/web4all/public>
        AllowOverride None
        Require all granted
    </Directory>

    # Cache statique
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType text/css "access + 1 month"
        ExpiresByType application/javascript "access + 1 month"
        ExpiresByType image/png "access + 1 month"
        ExpiresByType image/jpeg "access + 1 month"
    </IfModule>

    # headers perf
    Header set Cache-Control "public, max-age=2592000"

    ErrorLog ${APACHE_LOG_DIR}/web4all_static_error.log
    CustomLog ${APACHE_LOG_DIR}/web4all_static_access.log combined
</VirtualHost>
```

Puis taper :

```bash
sudo a2ensite web4all.conf
sudo a2dissite 000-default.conf
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl reload apache2
```

Puis éditer le fichier `/etc/hosts`

```
127.0.0.1 localhost web4all.local static.web4all.local
127.0.1.1 {votre_user}-VirtualBox

# The following lines are desirable for IPv6 capable hosts
::1     ip6-localhost ip6-loopback
fe00::0 ip6-localnet
ff00::0 ip6-mcastprefix
ff02::1 ip6-allnodes
ff02::2 ip6-allrouters
```

### Le projet web4all

Se placer dans le répertoire /var/www/html et lancer ces commandes

```bash
cd /var/www/html
mkdir web4all
git clone 'https://github.com/Jucott/web4all.git'
cd web4all
sudo chown -R {votre_user}:{votre_user} *
sudo chmod -R 755 *
```


### Postgresql

A partie de {votre_user} lancer ces commandes afin de créer le user "web4all" dans la database ainsi que la database "web4all" du projet elle-même:

```bash
sudo su - postgres
psql
create user web4all with password 'web4all' createdb;
create database web4all;
alter database web4all owner to web4all;
\q
exit
```

Puis, en se plaçant dans le répetoire /var/www/html/web4all, installer la base de donnée ainsi :

```bash
cat web4all.sql | psql -h 127.0.0.1 -U web4all web4all
Password for user web4all: web4all
CREATE....
CREATE....
__[snip]__
ALTER....

```

Afin de ne pas avoir à entrer systématiquement le mot de passe à chaque commande SQL passée depuis la ligne de commande (et accessoirement aussi pour le script `./dump_database.sh`), il suffit de créer un fichier dans le home directory.

```bash
echo "127.0.0.1:5432:web4all:web4all:web4all" > ~/.pgpass
chmod 600 ~/.pgpass
```


### Le fichier `.env` contenant les secrets de l'application

Situé à la racine du projet `/var/www/html/web4all/.env` il contient les secrets notamment ceux permettant la connexion à la database.

Ce fichier ne doit JAMAIS figurer dans GitHub par mesure évidente de sécurité, c'est pourquoi il appartient aux développeurs de le créer eux-même.

Ce fichier doit être explicitement exclue de toute synchronisation git.

Pour cela, il appartient aux développeurs, en se plaçant dans le répertoire racine de l'application de taper la commande suivante :

```bash
cd /var/www/html/web4all
echo ".env" >> .gitignore
```

Selon l'exemple ci-dessus, en tenant compte des crédentials positionnés dans postgresq plus haut, le fichier `.env` doit à minima contenir :

```bash
# Fichier contenant les secrets de l'application
# surtout ne pas versionner !!!
APP_ENV=dev
DB_HOST=localhost
DB_NAME=web4all
DB_USER=web4all
DB_PASS=web4all
DB_PORT=5432
```

### 📘 Procédure Git – Projet Web4All

#### 👨‍💻 Côté développeur

##### Accéder au projet

Rendez-vous sur le dépôt GitHub du projet :

👉 GitHub : https://github.com/Jucott/web4all

Assurez-vous d’être connecté à votre compte.

##### Créer un fork du projet

- Cliquez sur le bouton “**Fork**” en haut à droite de la page
- Sélectionnez “**Create a new fork**”
- Donnez un nom explicite à votre dépôt (idéalement lié à votre développement)
- Cliquez sur “**Create fork**”

✅ Vous disposez maintenant d’une copie du projet sur votre propre compte.

##### Développer sur votre fork

Travaillez sur votre fork :

- Créez une branche (recommandé)
- Effectuez vos modifications
- Réalisez des commits propres et explicites
- Poussez vos changements sur votre fork

Exemple de bonnes pratiques :

- Commits courts et clairs (feat, fix, refactor, etc.)
- Une fonctionnalité = une branche

##### Proposer vos modifications (Pull Request)

Une fois votre développement terminé :

- Allez sur votre fork
- Ouvrez l’onglet “**Pull requests**”
- Cliquez sur “**New pull request**”
- Vérifiez :
    - base repository : projet principal (`web4all`)
    - base branch : `main`
    - compare : votre branche

GitHub affiche automatiquement les différences.

##### Créer la Pull Request

- Cliquez sur “**Create pull request**”
- Rédigez une description claire :
    - objectif de la modification
    - changements réalisés
    - éventuels impacts

👉 Cette étape est essentielle pour faciliter la relecture.

- Cliquez sur “**Create pull request**” pour valider

⏳ Votre demande est maintenant en attente de validation.


#### 🔐 Côté responsable du projet (branche `main`)

##### Accéder aux demandes

Dans le dépôt principal :

- Aller dans l’onglet “**Pull requests**”
- Sélectionner la demande à examiner

##### Analyser les modifications

Plusieurs onglets sont disponibles :

- **Conversation** : échanges et description
- **Commits** : historique des modifications
- **Checks** : tests automatisés (si présents)
- **Files changed** : 📌 vue détaillée des modifications

👉 L’onglet “**Files changed**” est le plus important pour la revue de code.

##### Valider et fusionner

Si les modifications sont correctes :

- Aller dans l’onglet “**Conversation**”
- Cliquer sur “**Merge pull request**”
- Adapter le message de commit si nécessaire
- Cliquer sur “**Confirm merge**”

✅ Les modifications sont maintenant intégrées dans la branche `main`.

#### ✅ Bonnes pratiques générales

❌ Ne jamais travailler directement sur `main`

❌ Ne jamais pousser directement sur le dépôt principal

✅ Toujours passer par une Pull Request

✅ Documenter clairement ses modifications

✅ Mettre à jour régulièrement son fork


## Conformité PSR-12

Installer VsCode + composer + php-cs-fixer

```bash
sudo snap install code --classic
sudo apt install composer
composer global require friendsofphp/php-cs-fixer
export PATH="$PATH:$HOME/.config/composer/vendor/bin"
echo 'export PATH="$PATH:$HOME/.config/composer/vendor/bin"' >> ~/.bashrc
```

Pour voir si cela marche :

```bash
cd /var/www/html/web4all
php-cs-fixer fix --dry-run --diff
```

Cela va afficher les différences entre ce qui est et ce qui est proposé.

En cas d'accord avec les modifications suggérées :

```bash
php-cs-fixer fix
```

Pour qu'à chaque sauvegarde dans Vscode, les modifications respectant la conformité PSR-12 soit respectées, il faut installer l'extension : "php cs fixer"

## PHPUnit, Twig et carousel

Depuis la racine du projet, installer ces deux modules ainsi :

```bash
cd /var/www/hmtl/web4all
composer require --dev phpunit/phpunit:^12
sudo apt install php-xml php-mbstring php-curl php-zip
sudo systemctl restart apache2
composer require twig/twig
composer require julienlinard/php-carousel
```

Pour tester le code de test sur la class EvaluationController

```bash
php -d display_errors=1 ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/EvaluationControllerTest.php
```
