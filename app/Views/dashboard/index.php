<?php

declare(strict_types=1);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="description"
    content="Dashboard financeiro do Finanças+"
>

<title>Dashboard - Finanças+</title>

<link
    rel="stylesheet"
    href="/css/style.css"
>

</head>

<body>

<header class="header">

<nav class="nav container">

    <a
        class="brand"
        href="/"
    >

        <span class="mark">
            F+
        </span>

        Finanças<span>+</span>

    </a>

    <div class="links">

        <a href="/dashboard.php">
            Dashboard
        </a>

        <form class="logout-form" method="post" action="/logout.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $_SESSION['csrf_token'],
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?>"
            >

            <button class="logout-button" type="submit">
                Sair
            </button>

        </form>

    </div>

</nav>

</header>

<main>

<section class="section">

    <div class="container">

        <div class="heading">

            <small class="eyebrow">
                ÁREA DO USUÁRIO
            </small>

            <h1>
                Olá, <?= htmlspecialchars(
                    $usuario['nome'],
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?>!
            </h1>

            <p>
                Bem-vindo ao seu painel financeiro.
            </p>

        </div>

        <div class="cards">

            <article class="card">

                <div class="icon">
                    ??
                </div>

                <small>
                    PATRIMÔNIO
                </small>

                <h3>
                    R$ 0,00
                </h3>

                <p>
                    Seu patrimônio será exibido aqui.
                </p>

            </article>

            <article class="card">

                <div class="icon">
                    ??
                </div>

                <small>
                    RECEITAS
                </small>

                <h3>
                    R$ 0,00
                </h3>

                <p>
                    Acompanhe suas receitas mensais.
                </p>

            </article>

            <article class="card">

                <div class="icon">
                    ??
                </div>

                <small>
                    INVESTIMENTOS
                </small>

                <h3>
                    R$ 0,00
                </h3>

                <p>
                    Acompanhe a evolução dos seus investimentos.
                </p>

            </article>

        </div>

    </div>

</section>

</main>

<footer>

<div class="container footer">

    <div>

        <a
            class="brand"
            href="/"
        >

            <span class="mark">
                F+
            </span>

            Finanças<span>+</span>

        </a>

        <p>
            Conhecimento para decisões financeiras melhores.
        </p>

    </div>

    <small>
        © 2026 Finanças+.
    </small>

</div>

</footer>

</body>

</html>
