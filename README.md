RNKElfinderBundle
================

This Bundle provides a Symfony2 [elFinder](https://github.com/Studio-42/elFinder) integration.

elFinder is an open-source file manager for web, written in JavaScript using jQuery UI.
This Bundle was inspired by [FMElfinderBundle](https://github.com/helios-ag/FMElfinderBundle).
In contrast to FMElfinderBundle, RNKElfinderBundle provides full integration with Symfony\Component\HttpFoundation component
and is assetic less. All occurrence of exit() function and $_GET, $_POST, $_FILES arrays has been removed.
This approach fixes 500 http errors which occurs when using native elFinderConnector in symfony


# Installation

RNKElfinderBundle is shipped with elFinder source files (due to lack of composer integration in elFinder).
We haven't any additional changes to elFinder code. If embedded version of elFinder became outdated please inform us. We will update it as soon as possible.

###1 Add the following lines in your composer.json:

```json
{
    "require": {
        "research-nk/elfinder-bundle": "dev-master"
    }
}
```

###2 Run the composer to download the bundle:

```bash
$ php composer.phar update research-nk/elfinder-bundle
```

###3 Add this bundle to your application's kernel:

```php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new RNK\ElFinderBundle\RNKElfinderBundle(),
        // ...
    );
}
```
###4 Routing configuration:

Add the following routes to your application:
```yaml
# app/config/routing.yml
RNKElFinderBundle:
    resource: "@RNKElFinderBundle/Resources/config/routing.yml"
```
or configure your routes manually:
```yaml
rnk_el_finder_backend:
    pattern:  /rnk_el_finder_backend
    defaults: { _controller: RNKElFinderBundle:ElFinder:backend }

rnk_el_finder_show:
    pattern: /rnk_el_finder_show
    defaults: { _controller: RNKElFinderBundle:ElFinder:show }
```

###5 Secure RNKElFinderBundle:

```yaml
# app/config/security.yml
security:
    access_control:
        - { path: ^/rnk_el_finder_backend, role: ROLE_ADMIN }
        - { path: ^/rnk_el_finder_show, role: ROLE_ADMIN }

```


###6 Install assets

```sh
$ php app/console assets:install
```

# Basic configuration

## Add configuration options to your config.yml

```yaml
rnk_el_finder:
  locale: '%locale%'
  connector:
    debug: false
    roots:
      uploads:
        driver: LocalFileSystem
        show_hidden_files: false
        path: 'uploads'
        upload_allow: []
        upload_deny: []
        upload_max_size: 10M
```
More about connector configuration can be found here - [elFinder -Client configuration options](https://github.com/Studio-42/elFinder/wiki/Client-configuration-options)


## locale
Set elFinder locale.

**Data type:** string  
**Default value:** `'%locale%'` - symfony default locale


## connector.debug
Send debug to client.  

**Data type:** boolean  
**Default value:** `false`


## connector.roots
Array of arrays with per root settings. This is the only required option.
Multiple Roots are supported. Keys in this array are root names.

**Data type:** array  
**Default value:** `array()`

## connector.roots.[ROOT_NAME].show_hidden_files
Determinate if connector should hide files starting with dot

**Data type:** boolean  
**Default value:** `false`

## connector.roots.[ROOT_NAME].upload_allow
Mimetypes allowed to upload.

**Data type:** array  
**Default value:** `array()`

## connector.roots.[ROOT_NAME].upload_deny
Mimetypes not allowed to upload. Same values accepted as in uploadAllow

**Data type:** array  
**Default value:** `array()`

## connector.roots.[ROOT_NAME].upload_max_size
Maximum upload file size. This size is per files. Can be set as number or string with unit 10M, 500K, 1G.

**Data type:** integer|string  
**Default value:** `10M`

# TODO
 - Add tests
 - Add integration with CKEditor
 - Add integration with TinyMCE
 - Add support of all elFinder configuration options
 - Parse elfinder headers array and send them using Response object

