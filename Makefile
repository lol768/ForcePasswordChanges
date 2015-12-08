SHELL := /usr/bin/zsh
all: addon-ForcePasswordChanges.xml
	rm upload/ -rf; \
	mkdir -p upload/library/ForcePasswordChanges/; \
	rsync -av --exclude='upload' --exclude='vendor' --exclude='target' --exclude='.idea' --exclude='.git' . upload/library/ForcePasswordChanges/; \
	mkdir -p target; \
	rm target/* -rf; \
	zip -r target/ForcePasswordChanges.zip upload addon-ForcePasswordChanges.xml LICENSE;
tests:
	vendor/bin/phpunit
