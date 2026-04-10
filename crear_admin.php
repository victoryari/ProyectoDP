<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Credenciales por defecto para el primer acceso
$usuario = 'admin';
$password_plano = 'admin123';

// Generamos el hash seguro de la contraseña
$password_hash = password_hash($password_plano, PASSWORD_BCRYPT);

try {
    // 1. Asegurar que el rol de Administrador exista (estado = 1 activo)
    $query_rol = "INSERT IGNORE INTO roles (id_rol, nombre_rol, descripcion, estado) 
                  VALUES (1, 'Administrador', 'Acceso total al sistema administrativo', 1)";
    $db->exec($query_rol);

    // 2. Insertar el usuario. Si ya existe un usuario 'admin', actualizamos su contraseña.
    $query_user = "INSERT INTO usuarios (id_rol, nombre_usuario, password_hash, estado) 
                   VALUES (1, :usuario, :hash, 1)
                   ON DUPLICATE KEY UPDATE password_hash = :hash_update, estado = 1";
    
    $stmt = $db->prepare($query_user);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':hash', $password_hash);
    $stmt->bindParam(':hash_update', $password_hash); // Para el caso de que ya exista y se actualice
    
    if($stmt->execute()) {
        echo "<div style='font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; text-align: center; background-color: #f8f9fa;'>";
        echo "<h2 style='color: #198754;'>¡Usuario creado con éxito!</h2>";
        echo "<p>Ya puedes acceder al sistema con las siguientes credenciales:</p>";
        echo "<div style='background: #fff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p><strong>Usuario:</strong> " . htmlspecialchars($usuario) . "</p>";
        echo "<p><strong>Contraseña:</strong> " . htmlspecialchars($password_plano) . "</p>";
        echo "</div>";
        echo "<p style='color: #dc3545; font-size: 0.9em;'><strong>Importante:</strong> Por medidas de seguridad, recuerda eliminar este archivo (<code>crear_admin.php</code>) después de ingresar al sistema.</p>";
        echo "<a href='login.php' style='display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; margin-top: 15px;'>Ir al Login</a>";
        echo "</div>";
    }

} catch(PDOException $e) {
    echo "<div style='font-family: sans-serif; color: red; text-align: center; margin-top: 50px;'>";
    echo "<h3>Error al crear el usuario:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>