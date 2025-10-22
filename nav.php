<?php
    // On récupère le nom du fichier actuel pour savoir quelle page est active
    $currentPage = basename($_SERVER['REQUEST_URI']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>HiroAuto - Espace Client</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <link href="css/dashboard.css" rel="stylesheet">


    
</head>

<body class="<?= isset($bodyClass) ? htmlspecialchars($bodyClass) : '' ?>">

<?php if (isset($_SESSION['id_u'])) : ?>
    <div class="sidebar">
        <h3 class="sidebar-brand">
            <a href="accueil">🚗 HiroAuto</a>
        </h3>
        
        <?php
            $role_titre = '';
            if ($_SESSION['lvl'] == 1) $role_titre = "Espace Élève";
            if ($_SESSION['lvl'] == 2) $role_titre = "Espace Moniteur";
            if ($_SESSION['lvl'] == 3) $role_titre = "Administration";
        ?>
        <div class="sidebar-user-role"><?= htmlspecialchars($role_titre) ?></div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage == 'accueil') ? 'active' : '' ?>" href="accueil"><i class="bi bi-house-door-fill me-2"></i> Accueil</a>
            </li>

            <?php if ($_SESSION['lvl'] == 1) : // Élève ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'agenda') ? 'active' : '' ?>" href="agenda"><i class="bi bi-calendar-check-fill me-2"></i> Agenda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'galerie') ? 'active' : '' ?>" href="galerie"><i class="bi bi-images me-2"></i> Galerie</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'reception') ? 'active' : '' ?>" href="reception"><i class="bi bi-envelope-fill me-2"></i> Messages</a>
                </li>
            <?php elseif ($_SESSION['lvl'] == 2) : // Moniteur ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'agenda-admin') ? 'active' : '' ?>" href="agenda-admin"><i class="bi bi-calendar-week-fill me-2"></i> Agenda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'reception') ? 'active' : '' ?>" href="reception"><i class="bi bi-envelope-fill me-2"></i> Messages</a>
                </li>
            <?php elseif ($_SESSION['lvl'] == 3) : // Admin ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'admin') ? 'active' : '' ?>" href="admin"><i class="bi bi-people-fill me-2"></i> Liste</a>
                </li>
            <?php endif; ?>
        </ul>

        <ul class="nav flex-column mt-auto">
             <li class="nav-item">
                <a class="nav-link logout-link" href="logout"><i class="bi bi-box-arrow-left me-2"></i> Déconnexion</a>
            </li>
        </ul>
    </div>

    <main class="main-content">
        <?= $content; ?>
    </main>

<?php else : ?>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="accueil">🚗 HiroAuto</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#visitorNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="visitorNavbar">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="accueil">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link" href="galerie">Galerie</a></li>
                        <li class="nav-item"><a class="btn btn-outline-warning ms-lg-2" href="login">Connexion</a></li>
                        <li class="nav-item"><a class="btn btn-warning ms-lg-2 mt-2 mt-lg-0" href="inscription">S'inscrire</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-5">
        <?= $content; ?>
    </main>

    <footer class="py-4 bg-dark mt-auto">
        <div class="container text-center">
            <p class="mb-0 text-white-50">&copy; <?= date('Y'); ?> HiroAuto. Tous droits réservés.</p>
        </div>
    </footer>

<?php endif; ?>


</body>
</html>