<?php
    $files = [
        __DIR__ . '/resources/views/staff/auth/login.blade.php',
        __DIR__ . '/resources/views/staff/auth/register.blade.php',
        __DIR__ . '/resources/views/components/auth-layout.blade.php',
    ];

    foreach ($files as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);

            // Fix broken body flexbox properties scattered in <style>
            $bad1 = '<style> display: flex; align-items: center; justify-content: center; min-height: 100vh; }';
            $good1 = "<style>\n        .flex-center-wrapper { display: flex; align-items: center; justify-content: center; min-height: 100vh; flex-direction: column; }";

            // Wait, if I change it to `.flex-center-wrapper`, I need to wrap the body contents.
            // Oh right, since body is already handled in the new theme injection, let's just add `d-flex align-items-center justify-content-center min-vh-100` to the <body> tag itself!

            // First, remove the corrupted CSS line
            $content = str_replace('<style> display: flex; align-items: center; justify-content: center; min-height: 100vh; }', '<style>', $content);
            $content = str_replace('<style> display: flex; align-items: center; justify-content: center; min-height: 100vh;flex-direction: column; }', '<style>', $content);
            $content = preg_replace('/<style>\s*display: [^}]+}/i', '<style>', $content);

            // Add flex layout to body tag if missing
            if (str_contains($content, 'class="shape shape-1"')) {
                // If it's a login/auth page, body should have d-flex alignment
                if (str_contains($file, 'login.blade.php') || str_contains($file, 'register.blade.php') || str_contains($file, 'auth-layout.blade.php')) {
                    if (str_contains($content, '<body class="d-flex')) {
                        // Already has it
                    } else {
                        $content = str_replace('<body>', '<body class="d-flex align-items-center justify-content-center min-vh-100 flex-column">', $content);
                        $content = preg_replace('/<body[^>]*>/i', '<body class="d-flex align-items-center justify-content-center min-vh-100 flex-column">', $content);
                        
                        // Let's not double replace if preg_replace matched
                    }
                }
            }

            file_put_contents($file, $content);
            echo "Fixed auth layout: $file\n";
        }
    }
