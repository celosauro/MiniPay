## Instalação

1. Instale a última versão do `docker` e `docker-compose`.

2. Clone este repositório.

3. Suba o ambiente executando o comando:

    `make up`

4. Acesse [`http://localhost:8080/healthcheck`](http://127.0.0.1:8080/healthcheck) e deverá retornar uma resposta como:

    ``` 
    {
        "msg":"ok",
        "datetime":"2020-06-21T01:49:25Z",
        "timestamp":"1592704165"
    }
    ``` 


# Conceitos e tecnologias

Desenvolvido com conceitos como S.O.L.I.D., TDD (Test Driven Development) e DDD (Domain Driven Design), o projeto foi modularizado de forma que facilitem e incorporem esses conceitos. Além 

Foi utilizado a seguinte stack:

- PHP 7.4
- MySQL
- Symfony Framework
- Doctrine & Doctrine Migrations
- PHPStan/Psalm/PHPUnit


### Symfony Framework

O Symfony é um framework maduro e com alta reputação, conhecido pela comunidade PHP através dos seus componentes reutilizáveis e que compõem projetos como Magento/Drupal. 
Possui uma organização e recursos que facilitam a escrita de código limpo através de padrões de projeto, injeção de dependência e testes integrados.

#### O Symfony Messenger
O componente [`Symfony Messenger`](https://symfony.com/doc/current/messenger.html) provê uma série de mecanismos para a manipulação de mensagens/eventos de forma assíncrona e implementação do pattern Command através do Bus. Ele foi utilizado para manipular o recurso de envio de notificação para o usuário ao receber uma transação.

Para consumir as mensagens assíncronas através do messenger, é necessário executar, dentro do container php, o comando:
```bash
composer worker:queue:consumer
```

Haverá o processamento:
```bash
/app # composer worker:queue:consumer
> console messenger:consume async --limit=20 -vvv --no-interaction

                                                                                                                        
 [OK] Consuming messages from transports "async".                                                                       
                                                                                                                        

 // The worker will automatically exit once it has processed 20 messages or received a stop signal via the              
 // messenger:stop-workers command.                                                                                     

 // Quit the worker with CONTROL-C.                                                                                     

20:14:28 INFO      [messenger] Received message MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification
[
  "message" => MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification^ {
    +userId: "580b70fe-6955-453e-bde7-41280a465427"
    +amount: 1.0
  },
  "class" => "MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification"
]
20:14:29 INFO      [messenger] Message MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification handled by MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotificationHandler::__invoke
[
  "message" => MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification^ {
    +userId: "580b70fe-6955-453e-bde7-41280a465427"
    +amount: 1.0
  },
  "class" => "MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification",
  "handler" => "MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotificationHandler::__invoke"
]
20:14:29 INFO      [messenger] MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification was handled successfully (acknowledging to transport).
[
  "message" => MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification^ {
    +userId: "580b70fe-6955-453e-bde7-41280a465427"
    +amount: 1.0
  },
  "class" => "MiniPay\Core\Transaction\Application\Async\SendTransactionReceivedNotification"
]

```

### Testes e qualidade de código
As ferramentas [PHPStan](https://phpstan.org)/[Psalm](https://psalm.dev)/[PHPUnit](https://phpunit.de) foram introduzidas para garantir o teste de aplicação assim como a análise estática do código para capturar possíveis bugs com tipagem e smell codes;

Ao executar o comando `composer test` uma sequência de comandos avalia o projeto:
```bash
/app # composer test
> phpcs
> phpstan analyse --memory-limit=-1
Note: Using configuration file /app/phpstan.neon.
 96/96 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%


                                                                                                                        
 [OK] No errors                                                                                                         
                                                                                                                        

> psalm
Scanning files...
Analyzing files...

░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 60 / 95 (63%)
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░

------------------------------
No errors found!
------------------------------
29 other issues found.
You can display them with --show-info=true
------------------------------
Psalm can automatically fix 1 of these issues.
Run Psalm again with 
--alter --issues=MissingClosureReturnType --dry-run
to see what it can fix.
------------------------------

Checks took 5.32 seconds and used 297.696MB of memory
Psalm was able to infer types for 98.9369% of the codebase
> phpunit
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

Warning:       Your XML configuration validates against a deprecated schema.
Suggestion:    Migrate your XML configuration using "--migrate-configuration"!

...............................................................  63 / 131 ( 48%)
............................................................... 126 / 131 ( 96%)
.....                                                           131 / 131 (100%)

Time: 00:07.433, Memory: 40.00 MB

OK (131 tests, 256 assertions)

```


### Proposta de melhoria na arquitetura

- O uso de mensageria pode ser útil para processamentos assíncronos, diminuindo o tempo de resposta das operações e podendo ter garantias de reprocessamento de informação através de fallbacks. 
- Junto da mensageria, pode ser possível também implementar o Pattern [`Event Sourcing`](https://martinfowler.com/eaaDev/EventSourcing.html) onde as alterações são baseadas em eventos e mantendo um histórico dessas alterações 
