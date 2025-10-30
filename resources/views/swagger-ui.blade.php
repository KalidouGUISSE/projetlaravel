<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
        /* Correction des erreurs CSS liées aux propriétés background */
        .swagger-ui .topbar {
            background: #1f2937 !important;
        }
        .swagger-ui .info .title {
            background: transparent !important;
        }
        .swagger-ui .scheme-container {
            background: transparent !important;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            SwaggerUIBundle({
                url: '/guisse/docs.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                tryItOutEnabled: true,
                requestInterceptor: function (req) {
                    // Supprimer les références aux fichiers .map pour éviter les erreurs 404
                    if (req.url && req.url.includes('.map')) {
                        return false; // Annuler la requête
                    }
                    return req;
                },
                responseInterceptor: function (res) {
                    // Ignorer les erreurs liées aux fichiers .map
                    if (res.url && res.url.includes('.map') && res.status === 404) {
                        return null; // Ignorer la réponse
                    }
                    return res;
                }
            });
        };
    </script>
</body>
</html>