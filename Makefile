# Run `make` (no arguments) to get a short description of what is available
# within this `Makefile`.

DOCKER_IMAGE_NAME := laminas/validator
PHP_EXTENSIONS := mbstring json xdebug intl ctype fileinfo filter


qa: mago-fmt-check mago-lint mago-sa test docs-lint docs-check-links
.PHONY: qa

include vendor/laminas/internal-tooling/Makefile

install-tools: ## Install standalone tools
	cd tools/crc && composer install
.PHONY: install-tools

bump-tools: ## Bump deps for all standalone tools
	cd tools/crc && composer update && composer bump -D && composer update
.PHONY: bump-tools

composer-require-checker: ## Check composer.json for un-declared dependencies
	tools/crc/vendor/bin/composer-require-checker check \
		--config-file=tools/crc/config.json \
		composer.json
.PHONY: composer-require-checker
