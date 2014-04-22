# mkvhost

Command line tool to create virtual host config files.

## Notice

This is still in very early stages and extremely rough around the edges. Use at own risk.

## Installation

Download the file with curl:

```bash
curl -O https://raw.github.com/geevcookie/mkvhost/master/mkvhost.sh
```

Move the file to your bin directory:

```bash
sudo mv mkvhost.sh /usr/bin/mkvhost
```

Ensure that the file is executable:

```bash
sudo chmod +x /usr/bin/mkvhost
```

## Usage

```bash
mkvhost <options> example.dev
```

## Options

### --help, -h

Shows the help screen.

### --directory, -d (Default: "/Library/WebServer/Documents")

Sets a custom parent directory for the document root.

```bash
mkvhost -d /var/www example.dev
```

You can also change `VHOST_DOCUMENT_ROOT` in the script file to set a default.

### --parent-directory (Default: "/private/etc/apache2/virtualhosts")

Sets the directory that will hold the virtual host config file.

```bash
mkvhost --parent-directory /etc/apache2/virtualhosts
```

The script will check if this directory is available and prompt to create it if it does not exist.

You can also change the default by editing `VHOST_PARENT_DIRECTORY` in the script file.

### --port, -p (Default: "80")

Sets the port that the virtual host will listen on.

```bash
mkvhost -p 8080 example.dev
```

Again, you can change the default by editing `VHOST_PORT` in the script file.

### --web-dir, -w (Default: "")

Sets the public directory.

```bash
mkvhost -w public example.dev
```

What a surprise... You can change the default of this as well. Just edit `VHOST_PUBLIC_DIR` in the script file.

### --directory-index, -i (Default: "index.php index.html")

Changes the directory index for the virtual host.

```bash
mkvhost -i app.dev example.dev
```

To change the default edit the `VHOST_DIRECTORY_INDEX` in the script file.

### --document-root, -r (Default: The domain name entered)

This one is a bit confusing, but I do require it in some situations so I added it. If the folder containing the project files does not match the domain name (e.g. example.dev/) you can change it with this option.

```bash
mkvhost -r example example.dev
```

### --skip-apache, -s

Add this option to skip the automatic apache restart that occurs after the script completes.

```bash
mkvhost -s example.dev
```

## Vhost Example

```
<VirtualHost *:80>
    DocumentRoot "/var/www/example.dev"
    ServerName example.dev

    <Directory "/var/www/example.dev">
        Options All
        AllowOverride All
        Order allow,deny
        Allow from all
        DirectoryIndex index.php index.html
    </Directory>
</VirtualHost>
```

## Symfony Example

This part can be ignored, but to help understand all the options, below is a command I use when setting up a local environment for Symfony 2:

```bash
mkvhost -d /var/www -w web -i app_dev.php -r project project.dev
```

In this example my project files are located in `/var/www/project`. The public directory for Symfony 2 is `web` and as it is my development environment I change the directory index to `app_dev.php`.

The .htaccess file will still kick in so you might still get the default `app.php` file. To fix this change the `AllowOverride` option in the vhost file to `AllowOverride none`.