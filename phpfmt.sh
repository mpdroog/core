#!/bin/bash
# Auto-format PHP
set +e
if [ ! -f "php-cs-fixer" ]; then
        wget https://cs.symfony.com/download/php-cs-fixer-v2.phar -O php-cs-fixer
        chmod a+x php-cs-fixer
fi
set -e

php php-cs-fixer fix --config=.php_cs.dist .
