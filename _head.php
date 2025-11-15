<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=  $_title ?? 'Untitiled' ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="/index.php">
                    <img src="/images/logo.png" alt="Freelancer Platform Logo" class="logo-img">
                </a>
            </div>
            <nav class="header-nav">
                <a href="/login.php" class="btn btn-login">Login</a>
                <a href="/signup.php" class="btn btn-signup">Sign Up</a>
            </nav>
        </div>
    </header>