#!/bin/bash
set -e

# Variables
SYMFONY_REPO="https://github.com/symfony/symfony.git"
INSTALL_DIR="/var/www/mediatheque"

# Décochez la ligne correspondant à votre base de données et rentrez les informations de connexion
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
# DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=15&charset=utf8"

# Check wich package manager is installed
if [ -x "$(command -v apt)" ]; then
    PACKAGE_MANAGER="apt"
elif [ -x "$(command -v yum)" ]; then
    PACKAGE_MANAGER="yum"
elif [ -x "$(command -v pacman)" ]; then
    PACKAGE_MANAGER="pacman"
elif [ -x "$(command -v dnf)" ]; then
    PACKAGE_MANAGER="dnf"
elif [ -x "$(command -v pkg)" ]; then
    PACKAGE_MANAGER="pkg"
else
    echo "No package manager found"
    exit 1
fi

# Install dependencies
if ! command -v git > /dev/null; then
    echo "Installing git"
    $PACKAGE_MANAGER install -y git
fi

if ! command -v php > /dev/null; then
    echo "Installing php"
    $PACKAGE_MANAGER install -y php
fi

if ! command -v composer > /dev/null; then
    echo "Installing composer"
    $PACKAGE_MANAGER install -y composer
fi

# Cloner le dépôt Symfony depuis GitHub
git clone $SYMFONY_REPO $INSTALL_DIR

# Installer les dépendances avec Composer
cd $INSTALL_DIR

# Créer un fichier .env.local et y ajouter la variable d'environnement APP_ENV=prod
echo "APP_ENV=prod" > .env.local

# Réglez les autorisations si nécessaire
chmod -R 775 var

# Créer la base de données
php bin/console doctrine:database:create

# Lance update.sh
./update.sh