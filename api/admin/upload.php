<?php
// =====================================================
// API: SUBIR IMÁGENES (productos / banners)
// =====================================================

require_once '../../includes/config.php';
require_once '../../includes/auth.php';

verificarAdmin();

header('Content-Type: application/json');

// -----------------------------------------------------
// Configuración
// -----------------------------------------------------
$MAX_SIZE = 3 * 1024 * 1024; // 3 MB
$TIPOS_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$CARPETAS = [
    'producto' => 'productos',
    'banner'   => 'banners',
];

// -----------------------------------------------------
// Validaciones
// -----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    $errores = [
        UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño permitido por el servidor',
        UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño permitido',
        UPLOAD_ERR_PARTIAL    => 'La subida se interrumpió',
        UPLOAD_ERR_NO_FILE    => 'No se envió ningún archivo',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo',
        UPLOAD_ERR_EXTENSION  => 'Extensión bloqueada',
    ];
    $codigo = $_FILES['archivo']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'error' => $errores[$codigo] ?? 'Error desconocido']);
    exit;
}

$archivo = $_FILES['archivo'];
$tipo_solicitado = $_POST['tipo'] ?? 'producto';

if (!isset($CARPETAS[$tipo_solicitado])) {
    echo json_encode(['success' => false, 'error' => 'Tipo de imagen inválido']);
    exit;
}

if ($archivo['size'] > $MAX_SIZE) {
    echo json_encode(['success' => false, 'error' => 'La imagen supera el máximo de 3 MB']);
    exit;
}

// -----------------------------------------------------
// Validar MIME real (no confiar en $_FILES['type'])
// -----------------------------------------------------
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_real = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_real, $TIPOS_PERMITIDOS)) {
    echo json_encode(['success' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF']);
    exit;
}

// -----------------------------------------------------
// Generar nombre único
// -----------------------------------------------------
$extensiones = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
$ext = $extensiones[$mime_real];

$prefijo = ($tipo_solicitado === 'banner') ? 'banner' : 'prod';
$nombre_archivo = $prefijo . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

// -----------------------------------------------------
// Ruta de destino
// -----------------------------------------------------
$raiz = dirname(dirname(__DIR__)); // sube 2 niveles desde api/admin/
$carpeta_destino = $raiz . '/img/' . $CARPETAS[$tipo_solicitado] . '/';

if (!is_dir($carpeta_destino)) {
    if (!mkdir($carpeta_destino, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'No se pudo crear la carpeta de destino']);
        exit;
    }
}

$ruta_completa = $carpeta_destino . $nombre_archivo;

// -----------------------------------------------------
// Mover el archivo
// -----------------------------------------------------
if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
    echo json_encode(['success' => false, 'error' => 'Error al guardar la imagen en el servidor']);
    exit;
}

// -----------------------------------------------------
// Devolver la URL relativa
// -----------------------------------------------------
$url_relativa = 'img/' . $CARPETAS[$tipo_solicitado] . '/' . $nombre_archivo;

echo json_encode([
    'success'         => true,
    'url'             => $url_relativa,
    'nombre_archivo'  => $nombre_archivo,
    'peso'            => filesize($ruta_completa),
    'nombre_original' => $archivo['name'],
]);