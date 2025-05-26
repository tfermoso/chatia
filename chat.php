<?php
session_start();

// Cargar clave desde archivo .env
$env = parse_ini_file(__DIR__ . '/.env');
$api_key = $env['OPENROUTER_API_KEY'] ?? '';

$mensaje = $_POST['mensaje'] ?? '';

// Inicializar historial en sesión
if (!isset($_SESSION['historial'])) {
    $_SESSION['historial'] = [];
}

// Agregar nuevo mensaje del usuario al historial
if ($mensaje) {
    $_SESSION['historial'][] = ['role' => 'user', 'content' => $mensaje];
}

// Enviar solo el último mensaje al modelo
$data = [
    'model' => 'deepseek/deepseek-chat-v3-0324:free',
    'messages' => [['role' => 'user', 'content' => $mensaje]]
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => [
            'Content-type: application/json',
            'Authorization: Bearer ' . $api_key
        ],
        'content' => json_encode($data)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents('https://openrouter.ai/api/v1/chat/completions', false, $context);

if ($response !== false) {
    $result = json_decode($response, true);
    $respuesta = $result['choices'][0]['message']['content'] ?? 'Respuesta no válida';
    $_SESSION['historial'][] = ['role' => 'assistant', 'content' => $respuesta];
} else {
    $respuesta = 'Error al conectar con la API.';
}

echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Historial Chat</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='bg-light'><div class='container mt-5'>";
echo "<div class='card shadow'><div class='card-body'>";
echo "<h2 class='card-title'>🧠 Conversación</h2><div class='mt-4'>";

foreach ($_SESSION['historial'] as $msg) {
    $quien = $msg['role'] === 'user' ? '👤 Tú' : '🤖 IA';
    $color = $msg['role'] === 'user' ? 'text-primary' : 'text-success';
    echo "<p><strong class='$color'>$quien:</strong> " . nl2br(htmlspecialchars($msg['content'])) . "</p>";
}

echo "</div><a href='index.html' class='btn btn-secondary mt-3'>Enviar otro mensaje</a> ";
echo "<a href='reset.php' class='btn btn-danger mt-3'>🗑 Borrar historial</a>";
echo "</div></div></div></body></html>";
?>
