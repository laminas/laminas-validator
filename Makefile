# Run `make` (no arguments) to get a short description of what is available
# within this `Makefile`.

MKDOCS_IMAGE_ID := $(shell docker images -q laminas/mkdocs | xargs)

help: ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
.PHONY: help

documentation-theme: ## fetch the documentation theme repo
	git clone git@github.com:laminas/documentation-theme.git

build-mkdocs-image: documentation-theme ## Build the mkdocs image with necessary dependencies for building the docs
	$(if ${MKDOCS_IMAGE_ID}, $(info Image already built), cd documentation-theme/builder && docker build -t laminas/mkdocs .)
.PHONY: build-mkdocs-image

docs: build-mkdocs-image ## build the docs using a Docker container
	docker run -it -w /app -v ${PWD}:/app --rm laminas/mkdocs ./documentation-theme/build.sh -u https://www.example.com
	$(info ${PWD}/docs/html/index.html)
.PHONY: docs

install: install-tools ## Install PHP dependencies
	composer install
.PHONY: install

update: ## Update PHP dependencies
	composer update
.PHONY: update

bump: ## Bump PHP dev dependencies and update
	composer update && composer bump -D && composer update
.PHONY: bump

clean: ## Clear out caches and documentation assets
	rm -rf documentation-theme
	rm -rf docs/html
	docker image rm laminas/mkdocs
	rm -rf .phpunit.cache
	rm -f .phpcs-cache
	vendor/bin/psalm --clear-cache

static-analysis: ## Run static analysis checks
	vendor/bin/psalm --no-cache
.PHONY: static-analysis

coding-standards: ## Run coding standards checks
	vendor/bin/phpcs
.PHONY: coding-standards

coding-standards-fix: ## Fix coding standard violations
	vendor/bin/phpcbf
.PHONY: coding-standards-fix

test: ## Run unit tests
	vendor/bin/phpunit
.PHONY: test

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

qa: coding-standards static-analysis test composer-require-checker ## Run all QA Checks
.PHONY: qa
