<?php

declare(strict_types=1);

namespace MiniPay\Tests\Framework;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

use function assert;
use function sprintf;

class DoctrineTestCase extends WebTestCase
{
    protected static KernelBrowser $client;
    protected Application $application;

    protected ObjectManager $entityManager;

    protected function setUp(): void
    {
        self::$client = static::createClient();

        $kernel = self::$client->getKernel();
        $kernel->boot();

        $doctrine = $kernel->getContainer()
            ->get('doctrine');
        assert($doctrine instanceof Registry);

        $em = $doctrine->getManager();
        assert($em instanceof ObjectManager);
        $this->entityManager = $em;

        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);

        $this->executarComando('doctrine:database:drop --force');
        $this->executarComando('doctrine:database:create');
        $this->executarComando('doctrine:schema:create');

        parent::setUp();
    }

    protected function executarComando(string $command): void
    {
        $command = sprintf('%s --quiet', $command);

        $this->application->run(new StringInput($command));
    }

    protected function salvar(object $entidade): void
    {
        $this->entityManager->persist($entidade);
        $this->entityManager->flush();
    }
}
