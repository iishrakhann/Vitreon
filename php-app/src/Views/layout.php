<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'VITREON', ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        lavender: 'var(--color-lavender)',
                        plum: 'var(--color-plum)',
                        ink: 'var(--color-plum)',
                        mist: 'var(--color-mist)',
                        glass: 'var(--color-glass)'
                    },
                    boxShadow: {
                        glow: '0 24px 64px rgba(26, 11, 28, 0.34)'
                    },
                    animation: {
                        morph: 'morphIn 700ms ease both',
                        floaty: 'floaty 4s ease-in-out infinite'
                    },
                    keyframes: {
                        morphIn: {
                            '0%': { opacity: '0', transform: 'translateY(24px) scale(0.98)' },
                            '100%': { opacity: '1', transform: 'translateY(0) scale(1)' }
                        },
                        floaty: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-6px)' }
                        }
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="<?= htmlspecialchars($url('styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="min-h-screen bg-[#fbf8ff] text-plum" data-base-path="<?= htmlspecialchars($appBasePath, ENT_QUOTES, 'UTF-8') ?>">
    <div class="page-shell">
        <div class="page-aurora page-aurora--one"></div>
        <div class="page-aurora page-aurora--two"></div>
        <div class="page-aurora page-aurora--three"></div>
        <?php require dirname(__DIR__) . '/Views/partials/header.php'; ?>
        <main class="page-main w-full px-4 pb-28 pt-8 sm:px-6 lg:px-10">
            <?php require $viewPath; ?>
        </main>
        <?php require dirname(__DIR__) . '/Views/partials/footer.php'; ?>
        <?php require dirname(__DIR__) . '/Views/partials/bottom-nav.php'; ?>
    </div>
    <script src="<?= htmlspecialchars($url('app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
