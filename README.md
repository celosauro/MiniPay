# Stack

- PHP 7.4
- Symfony Framework
- Doctrine & Doctrine Migrations
- MySQL
- Ferramentas de QA

## Instalação

1. Instale a última versão do `docker` e `docker-compose`.

2. Clone este repositório.

3. Suba o ambiente:

`make up`

4. Para ver se está tudo ok, acesse [http://127.0.0.1:8080/healthcheck](http://127.0.0.1:8080/healthcheck) e veja uma resposta como:

``` 
{
    "msg":"ok",
    "datetime":"2020-06-21T01:49:25Z",
    "timestamp":"1592704165"
}
``` 
