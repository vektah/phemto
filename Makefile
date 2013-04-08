default: help

help:
	@echo Available targets:
	@grep -E '^[a-z_]+:' Makefile | cut -d: -f1 | grep -v -E '^default' | sed 's/^/ * make /'
	@echo 'See Makefile for more details'

docs:
	@./vendor/bin/phpdoc --directory src --target api
	@echo "Docs are available in ./api"
