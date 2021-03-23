#!/usr/bin/make -f
.SILENT:
.PHONY: build up down ssh sql logs reset

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## Exibe as instruções de uso.
help:
	printf "${COLOR_COMMENT}Uso:${COLOR_RESET}\n"
	printf " make [comando]\n\n"
	printf "${COLOR_COMMENT}Comandos disponíveis:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

## Constroi a imagem.
build:
	@echo 🐳🐘 Construindo as imagens.
	docker-compose build

## Inicia a aplicação.
up: api/vendor
	make build
	make reset
	docker-compose up -d

## Reinicia a aplicação.
reset:
	@echo 💾 Criando e populando banco de dados local.
	docker-compose run php bin/wait-for-mysql.sh console doctrine:migrations:migrate --no-interaction

## Desliga a aplicação.
down:
	@echo 🔴 Desligando os serviços.
	docker-compose down

## Conecta-se ao container php.
ssh:
	docker-compose exec php ash

## Conecta-se ao cliente SQL do container mysql.
sql:
	docker-compose exec mysql mysql -uroot -proot

## Exibe os logs da aplicação.
logs:
	docker-compose logs -f -t

api/vendor: api/composer.json api/composer.lock
	@echo 📦 Instalando as dependências do Composer.
	docker-compose run --no-deps php ash -c "composer install"

## Atualiza o composer.lock
composer.lock:
	@echo 🔒 Atualizando composer lock
	docker-compose exec php composer update --lock

## Executa os testes da aplicação.
test:
	@echo ► Executando testes
	docker-compose exec php composer test

## Executa o linter.
lint:
	@echo Efetuando análise do código
	docker-compose exec php composer lint

## Tenta corrigir os problemas de lint automáticamente.
lint.fix:
	@echo Efetuando correção automática do código
	docker-compose exec php composer lint-fix

## Apaga arquivos gerados dinâmicamente pelo projeto (containers docker, vendor, etc)
clean:
	@echo 🗑️ Removendo arquivos gerados automaticamente pelo projeto.
	sudo rm -rf api/vendor
	docker-compose down --rmi local --remove-orphans --volumes

## Libera espaço em disco (apaga dados do docker em desuso)
freespace:
	@echo 🗑️ Apagando arquivos do Docker que não estão sendo utilizados
	docker system prune --all --volumes --force
