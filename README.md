# ByterCold Deploy Module for Magento 2

## Description

If you just realized that you forgot to push a static file or a little change into a JS or HTML after you deployed the latest changes on your Magento production instance keeping the website down for minutes, then this module might help you with this kind of issues by helping you to deploy a single file and reset the static version without downtime. 

## How to install

### 1. Install via composer (recommended)

It is recommended to install the module via composer because it is easy to install, update or maintenance.

#### 1.1 Install

```
composer config repositories.bytercold/m2-deploy git https://github.com/Cata2806/bytercold-m2-deploy.git 
composer require bytercold/module-deploy:dev-master
php bin/magento module:enable ByterCold_Deploy
php bin/magento setup:upgrade --keep-generated
php bin/magento setup:di:compile
```

#### 1.2 Update

```
composer update bytercold/module-deploy:dev-master
php bin/magento module:enable ByterCold_Deploy
php bin/magento setup:upgrade --keep-generated
php bin/magento setup:di:compile
```

### 2. Install manually

If you do not want to install via composer, you can install it manually. 

- Download [the latest version here](https://github.com/Cata2806/bytercold-m2-deploy/archive/master.zip). 
- Extract `master.zip` file to `app/code/ByterCold/Deploy`.
- Run the following commands:

```
php bin/magento module:enable ByterCold_Deploy
php bin/magento setup:upgrade --keep-generated
php bin/magento setup:di:compile
```

### 3. Usage

The module adds a new command line called `bytercold:instant-static-deploy:file` that have 2 parameters:
- --staticFile - which is the Magento relative path of an asset, for example "Magento_Theme/js/theme.js"
- --reloadVersion - this parameter is used to refresh the static version of the deployed files (Can be 0 or 1)

#### Example:
```
$ php bin/magento bytercold:instant-static-deploy:file --staticFile "Magento_Theme/js/theme.js" --reloadVersion "1"
Initializing packages for deployment
Packages loaded successfully for all areas
Processing theme: Magento/blank
Asset processed: /var/www/html/magento2/vendor/magento/theme-frontend-blank/Magento_Theme/web/js/theme.js
Processing theme: Magento/luma
Asset processed: /var/www/html/magento2/vendor/magento/theme-frontend-blank/Magento_Theme/web/js/theme.js
Processing theme: Magento/backend
Static version changed successfully
File deployed successfully
```

### 4. FAQs

Q: Will this module work with the minified version of JS files?

A: Yes. 

Q: Will this module work with the merged JS files?

A: Not tested yet.

### 5. Future planning

- Implement a command line that regenerates the compiled version of PHP Class file. (This is necessary when the constructor of a class is changed)
- Implement a command line that refresh the translation files for the JS area. 
