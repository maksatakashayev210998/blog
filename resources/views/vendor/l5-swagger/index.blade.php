<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>

    <!-- Swagger UI CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.1.3/swagger-ui.min.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="swagger-ui"></div>

<!-- Swagger UI JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.1.3/swagger-ui-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.1.3/swagger-ui-standalone-preset.min.js"></script>

<script>
    const ui = SwaggerUIBundle({
        url: "{{ asset('storage/swagger.json') }}",  // Убедитесь, что путь правильный
        //url : "{{ asset('localhost/storage/api-docs/swagger.json') }}",
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIBundle.presets.swagger
        ],
        layout: "BaseLayout"
    });
</script>
</body>
</html>
