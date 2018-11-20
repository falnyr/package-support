cs:
	vendor/bin/php-cs-fixer fix -v
phar:
	php -d phar.readonly=0 ./vendor/bin/box build -v