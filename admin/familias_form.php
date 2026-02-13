<?php
require_once "../config/database.php";
require_once "../config/auth.php";

Auth::require();

$id = intval($_GET['id'] ?? 0);
$familia = "";

if ($id > 0) {
    $stmt = Database::execute(
        "SELECT * FROM familias WHERE idfamilias = ?",
        "i",
        [$id]
    );
    
    if ($stmt && $stmt->num_rows > 0) {
        $f = $stmt->get_result()->fetch_object();
        $familia = $f->familia;
    }
    
    if ($stmt) $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? "Editar" : "Nueva" ?> Familia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-3">

<div class="container">

<div class="row justify-content-center">
    <div class="col-md-6">
    
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <?= $id ? "✏️ Editar" : "➕ Nueva" ?> familia
                </h5>
            </div>
            
            <div class="card-body">
                <form method="post" action="familias_save.php">
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <div class="mb-3">
                        <label class="form-label">Nombre de la familia *</label>
                        <input type="text" 
                               name="familia"
                               class="form-control"
                               required
                               autofocus
                               maxlength="100"
                               value="<?= htmlspecialchars($familia) ?>"
                               placeholder="Ej: Pizzas, Bebidas, Postres...">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            ✅ Guardar
                        </button>
                        <a href="familias.php" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

</div>

</body>
</html>
