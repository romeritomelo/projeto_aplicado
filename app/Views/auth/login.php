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
    content="Acesso ao sistema Finanças+"
>

<title>Acesso - Finanças+</title>

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

</nav>

</header>

<main>

<section class="section">

    <div
        class="container"
        style="max-width: 480px;"
    >

        <div class="heading">

            <small class="eyebrow">
                ACESSO AO SISTEMA
            </small>

            <!--<h1>
                Entrar
            </h1>-->

            <p>
                Acesse sua área financeira.
            </p>

        </div>

        <?php if ($erro !== ''): ?>

            <div
                role="alert" class="login-error"
            >

                <?= htmlspecialchars(
                    $erro,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

         <div class="login-card">
            <form
                method="post"
                action="/login.php"
                autocomplete="on"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) ?>"
                >

                <label>
                    E-mail

                    <div class="input">

                        <input
                            type="email"
                            name="email"
                            maxlength="150"
                            required
                            autocomplete="username"
                            value="<?= htmlspecialchars(
                                $email,
                                ENT_QUOTES | ENT_SUBSTITUTE,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                </label>

                <br>

                <label>
                    Senha

                    <div class="input">

                        <input
                            type="password"
                            name="senha"
                            maxlength="128"
                            required
                            autocomplete="current-password"
                        >

                    </div>

                </label>

                <button
                    type="submit"
                    class="btn primary full"
                >
                    Entrar
                </button>

            </form>

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
