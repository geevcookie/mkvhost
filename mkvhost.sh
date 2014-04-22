#!/usr/bin/env bash

# Colors.
NORMAL=$(tput sgr0)
BOLD=$(tput bold)
RED=$(tput setaf 1)
GREEN=$(tput setaf 2)
YELLOW=$(tput setaf 3)
BLUE=$(tput setaf 4)
MAGENTA=$(tput setaf 5)
CYAN=$(tput setaf 6)

# Virtual host defaults.
VHOST_PARENT_DIRECTORY=/private/etc/apache2/virtualhosts
VHOST_DIRECTORY_INDEX="index.php index.html"
VHOST_DOCUMENT_ROOT=/Library/WebServer/Documents
VHOST_PUBLIC_DIR=
VHOST_PORT=80

# Path to apache.
APACHE_PATH=/usr/sbin/apachectl

# Info output.
info ()
{
    printf "%b" "[${GREEN}...${NORMAL}] $1\n"
}

# Prompt user for input.
prompt ()
{
    printf "%b" "[${YELLOW} ? ${NORMAL}] $1 "
}

# Error output.
error ()
{
    printf "%b" "[${RED}ERROR${NORMAL}] ${0}: ${1:-'Unkown Error'}\n" >&2
}

# Fail output.
fail ()
{
    error "$1"

    case $2 in
        ''|*[!0-9]*)
            EXIT_CODE=1
            ;;
        *)
            EXIT_CODE=$2
        ;;
    esac

    exit $EXIT_CODE
}

# Ask yes or no.
ask ()
{
    while true; do
 
        if [ "${2:-}" = "Y" ]; then
            PROMPT_TAIL="Y/n"
            DEFAULT=Y
        elif [ "${2:-}" = "N" ]; then
            PROMPT_TAIL="y/N"
            DEFAULT=N
        else
            PROMPT_TAIL="y/n"
            DEFAULT=
        fi
 
        # Ask the question
        prompt "$1 [$PROMPT_TAIL]"
        read REPLY
 
        # Default?
        if [ -z "$REPLY" ]; then
            REPLY=$DEFAULT
        fi
 
        # Check if the reply is valid
        case "$REPLY" in
            Y*|y*) return 0 ;;
            N*|n*) return 1 ;;
        esac

    done
}

# Show help
show_help ()
{
    cat <<HELP
    Usage:
        $0 <options> [DOMAIN NAME]

    OPTIONS:
        --help, -h:            Show this help and exit
        --directory, -d:       Set custom parent directory for document root
        --parent-directory:    Set the directory containing the virtual host config files
        --port, -p:            Change the port the virtual host will listen on
        --skip-apache, -s:     Skip apache restart
        --web-dir, -w:         Sets the public directory (e.g. public)
        --directory-index, -i: Sets the directory index (Default: index.php index.html)
        --document-root, -r:   Sets the root directory (Only used if root directory does not match domain name)

    Example:
        $0 example.dev
        $0 -d /var/www example.dev

HELP

    # Fail with general error if status code passed
    case $1 in
        ''|*[!0-9]*)
            EXIT_CODE=1
            ;;
        *)
            EXIT_CODE=$1
        ;;
    esac

    exit $EXIT_CODE
}

# Check if directory exists.
directory_exists()
{
    if [ ! -d "$1" ]; then
        echo "false";
    else
        echo "true";
    fi
}

# Main logic.
finally()
{
    info "Creating virtual host for ${GREEN}$DOMAIN_NAME${NORMAL}"

    # Check if virtual host directory exists.
    info "Checking virtual host directory: $VHOST_PARENT_DIRECTORY"
    if [ "$(directory_exists $VHOST_PARENT_DIRECTORY)" = "false" ]; then
        if ask "Virtual host directory does not exist. Create it?" Y; then
            info "Creating directory $VHOST_PARENT_DIRECTORY"
            mkdir $VHOST_PARENT_DIRECTORY
        else
            error "Cancelled by user"
            exit 0
        fi
    fi

    # Fix document root.
    if [ -z $VHOST_ROOT_DIRECTORY ]; then
        VHOST_ROOT_DIRECTORY=$DOMAIN_NAME
    fi

    # create the virtual host config file.
    cat << __EOF >$VHOST_PARENT_DIRECTORY/$DOMAIN_NAME.conf
<VirtualHost *:$VHOST_PORT>
    DocumentRoot "$VHOST_DOCUMENT_ROOT/$VHOST_ROOT_DIRECTORY/$VHOST_PUBLIC_DIR"
    ServerName $DOMAIN_NAME

    <Directory "$VHOST_DOCUMENT_ROOT/$VHOST_ROOT_DIRECTORY/$VHOST_PUBLIC_DIR">
        Options All
        AllowOverride All
        Order allow,deny
        Allow from all
        DirectoryIndex $VHOST_DIRECTORY_INDEX
    </Directory>
</VirtualHost>
__EOF

    info "Created: ${GREEN}$VHOST_PARENT_DIRECTORY/$DOMAIN_NAME.conf${NORMAL}"

    # Restart Apache
    if [ -z $SKIP_APACHE ]; then
        info "Restarting Apache..."
        $APACHE_PATH restart 1>/dev/null 2>/dev/null
        info "Done"
    fi
}

# Process the arguments.
check_args()
{
    while [ ! -z "$1" ]; do
        local arg="$1"
        case "$1" in
            -h|--help) 
                local help=0
                shift
                ;;
            -d|--directory)
                VHOST_DOCUMENT_ROOT=$2
                shift
                ;;
            --parent-directory)
                VHOST_PARENT_DIRECTORY=$2
                shift
                ;;
            -p|--port)
                VHOST_PORT=$2
                shift
                ;;
            -w|--web-dir)
                VHOST_PUBLIC_DIR=$2
                shift
                ;;
            -i|--directory-index)
                VHOST_DIRECTORY_INDEX=$2
                shift
                ;;
            -r|--document-root)
                VHOST_ROOT_DIRECTORY=$2
                shift
                ;;
            -s|--skip-apache)
                SKIP_APACHE=true
                shift
                ;;
            -*|--*)
                fail "Invalid argument." 1
                exit 0
                shift
                ;;
            *)
                DOMAIN_NAME=$1
                shift
                ;;
        esac
    done

    if [ ! -z $help ] || [ -z $arg ]; then
        show_help $help
    fi

    finally

    exit 0
}

# Main process.
main()
{
    # Check if running as root.
    if [ `whoami` != 'root' ]; then
        sudo "$0" $* || exit 1
        exit 0
    fi

    if [ "$SUDO_USER" = "root" ]; then
        fail "You must start this under your regular user account (not root) using sudo." 1
        exit 1
    fi

    check_args "$@"
}

# Start the main process.
main "$@"