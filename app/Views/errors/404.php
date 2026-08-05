<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <main class="main container">
        <section class="card card-narrow error-page">
            <h1>404</h1>
            <p>The page you requested could not be found.</p>
            <a href="<?= url() ?>" class="btn btn-primary">Back to Dashboard</a>
        </section>
    </main>
</body>
</html>
