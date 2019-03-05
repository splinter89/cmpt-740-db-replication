<?php

declare(strict_types=1);

use App\Repository\AccommodationRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Service\RandomQueryGenerator;
use Atlas\Orm\Atlas;
use Atlas\Orm\AtlasBuilder;
use Atlas\Pdo\Connection;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as Twig;
use Zend\Diactoros\Response as DiactorosResponse;

use function DI\{create, get, string};

$containerBuilder = (new ContainerBuilder)
    ->useAutowiring(false)
    ->useAnnotations(false)
    //->enableCompilation(__DIR__.'/../tmp')
    //->writeProxiesToFile(true, __DIR__.'/../tmp/proxies')
    ->addDefinitions([
        \App\Controller\AccommodationController::class => create()->constructor(
            get(Response::class),
            get(Twig::class),
            get(AccommodationRepository::class)
        ),
        \App\Controller\HomeController::class => create()->constructor(
            get(Response::class),
            get(Twig::class)
        ),
        \App\Controller\RandomController::class => create()->constructor(
            get(Response::class),
            get(Twig::class),
            get(RandomQueryGenerator::class)
        ),
        \App\Controller\ReservationController::class => create()->constructor(
            get(Response::class),
            get(Twig::class),
            get(ReservationRepository::class)
        ),
        \App\Controller\UserController::class => create()->constructor(
            get(Response::class),
            get(Twig::class),
            get(UserRepository::class)
        ),

        AccommodationRepository::class => create()->constructor(get(Atlas::class)),
        ReservationRepository::class => create()->constructor(get(Atlas::class)),
        UserRepository::class => create()->constructor(get(Atlas::class)),

        Atlas::class => function (ContainerInterface $c) {
            return Atlas::new(
                $c->get('db.dsn.default'),
                $c->get('db.username'),
                $c->get('db.password')
            );
        },
        RandomQueryGenerator::class => create()->constructor(
            get(UserRepository::class),
            get(AccommodationRepository::class),
            get(ReservationRepository::class)
        ),
        Response::class => function () {
            return new DiactorosResponse;
        },
        Twig::class => function (ContainerInterface $c) {
            $loader = new \Twig\Loader\FilesystemLoader($c->get('path.templates'));
            $options = [
                //'cache' => $c->get('path.templates.cache'),
            ];
            return new \Twig\Environment($loader, $options);
        },

        'db.dsn.default' => string('mysql:host={db.host};dbname={db.name};charset=utf8'),
        'db.host' => 'localhost',
        'db.name' => '740_project',
        'db.password' => '...',
        'db.username' => '...',
        'path.templates' => __DIR__.'/../templates/',
        'path.templates.cache' => __DIR__.'/../tmp/templates_cache/',
    ]);

return $containerBuilder->build();
