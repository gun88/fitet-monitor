<?php

class Fitet_Monitor_Helper {

    // todo ...diventa utils????

    public static function enqueue_style($handle, $src, $deps, $version, $media) {
        $minified = plugin_dir_path($src) . basename($src, '.css') . '.min.css';
        if (file_exists($minified)) {
            $url = plugin_dir_url($src) . basename($minified);
            wp_enqueue_style($handle, $url, $deps, $version, $media);
        } else if (file_exists($src)) {
            $url = plugin_dir_url($src) . basename($src);
            wp_enqueue_style($handle, $url, $deps, $version, $media);
        }

    }

    public static function enqueue_script($handle, $src, $deps, $version, $in_footer) {
        $minified = plugin_dir_path($src) . basename($src, '.js') . '.min.js';
        if (file_exists($minified)) {
            $url = plugin_dir_url($src) . basename($minified);
            wp_enqueue_script($handle, $url, $deps, $version, $in_footer);
        } else if (file_exists($src)) {
            $url = plugin_dir_url($src) . basename($src);
            wp_enqueue_script($handle, $url, $deps, $version, $in_footer);
        }
    }


    public static function github_download_dir_recursive(
        string  $owner,
        string  $repo,
        string  $path,
        string  $dest,
        string  $branch = 'main',
        ?string $token = null,
        ?string $rootPath = null
    ): void {
        if (!function_exists('wp_remote_get')) {
            throw new RuntimeException('WordPress HTTP API non disponibile.');
        }

        // Radice di riferimento per tutti i percorsi relativi (rimane costante durante la ricorsione)
        if ($rootPath === null) {
            $rootPath = trim($path, '/');
        } else {
            $rootPath = trim($rootPath, '/');
        }

        // Assicura che la cartella di destinazione esista
        if (!file_exists($dest)) {
            if (!wp_mkdir_p($dest)) {
                throw new RuntimeException('Impossibile creare la cartella di destinazione: ' . $dest);
            }
        }

        $apiBase = "https://api.github.com/repos/{$owner}/{$repo}/contents/";
        $url = $apiBase . ltrim($path, '/');
        $args = [
            'headers' => [
                'User-Agent' => 'WordPress; github-recursive-downloader',
                'Accept' => 'application/vnd.github+json',
            ],
            'timeout' => 30,
        ];
        if ($token) {
            $args['headers']['Authorization'] = 'Bearer ' . $token;
        }

        // Aggiunge il branch
        $url = add_query_arg(['ref' => $branch], $url);

        $res = wp_remote_get($url, $args);
        if (is_wp_error($res)) {
            throw new RuntimeException('Errore HTTP: ' . $res->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($res);
        if ($code !== 200) {
            throw new RuntimeException('GitHub API ha risposto con codice ' . $code . ' su ' . $url);
        }

        $body = wp_remote_retrieve_body($res);
        $items = json_decode($body, true);

        if (!is_array($items)) {
            throw new RuntimeException('Risposta API non valida per ' . $url);
        }

        foreach ($items as $item) {
            if (!isset($item['type'], $item['path'])) {
                continue;
            }

            // Calcola il percorso locale mantenendo la struttura relativa alla RADICE iniziale
            // Esempio: rootPath = 'data', item['path'] = 'data/clubs/logo.png' => 'clubs/logo.png'
            $itemPathNormalized = ltrim($item['path'], '/');
            $relativePath = preg_replace(
                '#^' . preg_quote($rootPath, '#') . '/#',
                '',
                $itemPathNormalized
            );

            $localPath = rtrim($dest, '/\\') . DIRECTORY_SEPARATOR . $relativePath;

            if ($item['type'] === 'dir') {
                // Crea la cartella e scende ricorsivamente
                if (!file_exists($localPath)) {
                    wp_mkdir_p($localPath);
                }
                // ATTENZIONE: passiamo SEMPRE lo stesso $rootPath
                self::github_download_dir_recursive($owner, $repo, $item['path'], $dest, $branch, $token, $rootPath);

            } elseif ($item['type'] === 'file') {
                $rawUrl = !empty($item['download_url'])
                    ? $item['download_url']
                    : "https://raw.githubusercontent.com/{$owner}/{$repo}/{$branch}/" . $item['path'];

                // Scarica il file
                $fileRes = wp_remote_get($rawUrl, [
                    'headers' => ['User-Agent' => 'WordPress; github-recursive-downloader'],
                    'timeout' => 60,
                ]);
                if (is_wp_error($fileRes)) {
                    error_log('Download fallito per ' . $rawUrl . ': ' . $fileRes->get_error_message());
                    continue;
                }
                $status = wp_remote_retrieve_response_code($fileRes);
                if ($status !== 200) {
                    error_log('HTTP ' . $status . ' scaricando ' . $rawUrl);
                    continue;
                }

                $data = wp_remote_retrieve_body($fileRes);

                // Assicura che la sottocartella esista
                $dirOfFile = dirname($localPath);
                if (!file_exists($dirOfFile)) {
                    wp_mkdir_p($dirOfFile);
                }

                // Scrive su disco (binario)
                $bytes = file_put_contents($localPath, $data);
                if ($bytes === false) {
                    error_log('Impossibile scrivere il file: ' . $localPath);
                }
            }
        }
    }

}
