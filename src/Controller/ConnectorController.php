<?php

namespace App\Controller;

use App\Entity\Connector;
use App\Entity\User;
use App\Service\Connector\AbstractProvider;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ConnectorController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        #[Autowire(service: 'service_container')]
        private readonly ContainerInterface $locator,
    ) {
    }

    #[Route(
        path: '/api/connectors',
        name: 'connector_get_all_mine',
        defaults: [
            '_api_resource_class' => Connector::class,
            '_api_operation_name' => 'get_all_mine',
        ],
        methods: ['GET']
    )]
    public function getConnector(): Collection
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user->getConnectors();
    }

    /**
     * @throws \Exception
     * @throws ExceptionInterface
     */
    #[Route(
        path: '/api/connectors',
        name: 'connector_create',
        defaults: [
            '_api_resource_class' => Connector::class,
            '_api_operation_name' => 'create',
        ],
        methods: ['POST']
    )]
    public function createConnector(Connector $connector): Connector
    {
        /** @var User $user */
        $user = $this->getUser();
        $connector->setUser($user);

        $provider = $connector->getProvider();

        $this->logger->info('User {username} wants to register a connector from provider {provider}.', [
            'username' => $user->getUserIdentifier(),
            'provider' => $provider->value,
        ]);

        if (null === $provider) {
            throw new BadRequestHttpException('Provider not found');
        }

        /** @var AbstractProvider $providerClient */
        $providerClient = $this->locator->get($provider->getConnectorProvider());
        $connector->setAuthData($providerClient->authenticate($connector->getAuthData()));

        $this->logger->info('User {username} authentication data with the {provider} provider has been validated.', [
            'username' => $user->getUserIdentifier(),
            'provider' => $provider->value,
        ]);

        $this->logger->info('The new API connector requested by {username} has been successfully registered.', [
            'username' => $user->getUserIdentifier(),
        ]);

        $connector->setCreatedAt(new \DateTimeImmutable('now'));
        $this->em->persist($connector);
        $this->em->flush();

        return $connector;
    }
}
