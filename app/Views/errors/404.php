<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
</head>
<body class="site-body">
    <main class="main container">
        <section class="card card-narrow error-page">
            <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40"><h1>Page not found</h1>
            <p>The page you requested could not be found.</p>
            <a href="<?= url() ?>" class="btn btn-primary">Back to Home</a>
        </section>
    </main>
</body>
</html>
