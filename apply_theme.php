<?php
    function applyGlobalTheme($dir) {
        $files = [
            $dir . '/components/layouts/dashboard.blade.php',
            $dir . '/components/layouts/admin.blade.php',
            $dir . '/components/layouts/doctor.blade.php',
            $dir . '/components/layouts/pharmacist.blade.php',
            $dir . '/components/auth-layout.blade.php',
            $dir . '/staff/auth/login.blade.php', // sometimes auth has its own layout or style
        ];

        $cssInject = "
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif !important;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe, #f8fafc) !important;
            background-attachment: fixed !important;
        }
        
        .shape {
            position: fixed; /* fixed so they stay when scrolling */
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.3;
            animation: float 20s infinite ease-in-out alternate;
            pointer-events: none;
        }

        .shape-1 { width: 500px; height: 500px; background: #0ea5e9; top: -150px; left: -100px; }
        .shape-2 { width: 600px; height: 600px; background: #38bdf8; bottom: -200px; right: -100px; animation-delay: -5s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.1); }
        }
        ";

        $htmlInject = "
    <!-- Background Decor (Very Subtle) -->
    <div class=\"shape shape-1\"></div>
    <div class=\"shape shape-2\"></div>
";

        foreach ($files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $changed = false;

                // Strip old body backgrounds if defined in style blocks
                $content = preg_replace('/body\s*\{\s*background-color:[^;}]*[;}]/is', '', $content);
                $content = preg_replace('/body\s*\{\s*background:[^;}]*[;}]/is', '', $content);

                if (!str_contains($content, 'shape-1')) {
                    if (str_contains($content, '</style>')) {
                        $content = str_replace('</style>', $cssInject . "\n    </style>", $content);
                        $changed = true;
                    }
                    if (str_contains($content, '<body>')) {
                        $content = str_replace('<body>', "<body>\n" . $htmlInject, $content);
                        $changed = true;
                    } else if (preg_match('/<body[^>]*>/i', $content, $matches)) {
                        $content = str_replace($matches[0], $matches[0] . "\n" . $htmlInject, $content);
                        $changed = true;
                    }
                }

                if ($changed) {
                    file_put_contents($file, $content);
                    echo "Applied theme to: " . $file . "\n";
                }
            }
        }
    }

    applyGlobalTheme(__DIR__ . '/resources/views');
