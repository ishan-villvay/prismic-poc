<?php

use function DI\create;
use App\Model\ArticleRepository;
use App\Persistence\InMemoryArticleRepository;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    // Bind an interface to an implementation
    ArticleRepository::class => create(InMemoryArticleRepository::class),

    // Configure Twig
    Environment::class => function () {
        $loader = new FilesystemLoader(__DIR__ . '/../src/Views');
        return new Environment($loader);
    },
];
