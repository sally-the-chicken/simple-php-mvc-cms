Simple PHP MVC CMS Sample
======

This is a simple CMS using [simple-php-mvc-base module](https://github.com/sally-the-chicken/simple-php-mvc-base).  
If you would like a barebone application and customize it yourself, and do not want to use any conventional frameworks, this might be for you.  

Setup
------

### Configs:

```
cd [your repos]/config
cp cli.php.sample cli.php
cp constants.php.sample constants.php
cp db.php.sample db.php
cp environment.php.sample environment.php
```
Please fill in custom values for your own application environment.  

#### USER_PASSWORD_SALT

You might want to generate your admin password hash using your own salt for your application.

```
php -r 'echo sha1(strrev("${PASSWORD}").strtoupper("${USERNAME}")."${USER_PASSWORD_SALT}");'
```

E.g.
```
php -r 'echo sha1(strrev("Test1234").strtoupper("admin")."6roHjBijWtztkUHH");'
```

In this case, update `sample/db_create.sql` with your values.

### Models:

Please use this [db model generator](https://github.com/sally-the-chicken/simple-php-mvc-base/tree/master/.utilities) to generate code chunks. 

### Session Directory: 

```
cd ${YOUR_REPOS_DIR}
mkdir session
sudo chown ${WEB_USER}:${WEB_USER} session
```

### Server rewrites

#### Nginx

/etc/nginx/conf.d/sample.conf

```
location / {
    try_files $uri $uri/ /index.php?q=$uri&$args;
}
```

#### Apache

.htaccess

(Make sure `mod_rewrite` is uncommented in http.conf)

```
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule / /path/to/webroot/or/repos/index.php?q=%{REQUEST_URI} [L,QSA]
```

### DB

```

mysql > CREATE DATABASE `simple_php_mvc_cms`;
mysql > USE `simple_php_mvc_cms`;
mysql > source ./sample/db_create.sql
mysql > source ./sample/db_insert.sql
mysql > source ./sample/db_alter.sql


```


