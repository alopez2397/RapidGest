/**
 * JavaScript para index.php
 * Manejo de nuevo pedido y búsqueda de clientes
 */

function abrirNuevoPedido() {
    // Limpiar campos
    document.getElementById("telefono").value = '';
    document.getElementById("nombre").value = '';
    document.getElementById("direccion").value = '';
    
    const modal = new bootstrap.Modal(
        document.getElementById("modalCliente")
    );
    modal.show();
}

function crearPedido() {
    const telefono = document.getElementById("telefono").value.trim();
    const nombre = document.getElementById("nombre").value.trim();
    const direccion = document.getElementById("direccion").value.trim();

    // Validaciones
    if (telefono === '') {
        alert("⚠️ Introduce un teléfono");
        document.getElementById("telefono").focus();
        return;
    }
    
    if (telefono.length < 9) {
        alert("⚠️ El teléfono debe tener al menos 9 dígitos");
        document.getElementById("telefono").focus();
        return;
    }
    
    if (nombre === '') {
        alert("⚠️ Introduce el nombre del cliente");
        document.getElementById("nombre").focus();
        return;
    }

    // Preparar datos

    const data = new URLSearchParams({
        telefono: telefono,
        nombre: nombre,
        direccion: direccion
    });

    // Deshabilitar botón para evitar doble click
    const btnCrear = event.target;
    btnCrear.disabled = true;
    btnCrear.textContent = 'Creando...';

    fetch("ajax/crearpedido.php", {
        method: "POST",
        body: data
    })
    .then(response => {
        if (!response.ok) {
           throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.ok) {
             window.location.href = "tpv.php";
        } else {
            alert("❌ " + (data.error || "Error al crear pedido"));
            btnCrear.disabled = false;
            btnCrear.textContent = '✅ Crear pedido';
        }
    })
    .catch(err => {
        console.error("Error:", err);
        alert("❌ Error de conexión. Inténtalo de nuevo. index.js Pedido: ");
        btnCrear.disabled = false;
        btnCrear.textContent = '✅ Crear pedido';
    });
}

/**
 * Busca un cliente por teléfono cuando se pierde el foco del campo
 */
function buscarClientePorTelefono() {
    const telefono = document.getElementById("telefono");
    const nombre = document.getElementById("nombre");
    const direccion = document.getElementById("direccion");
    
    if (!telefono) return;

    telefono.addEventListener("blur", function() {
        const tel = telefono.value.trim();

        if (tel.length < 6) return;

        fetch("ajax/buscarcliente.php?telefono=" + encodeURIComponent(tel))
            .then(r => {
                if (!r.ok) throw new Error('Error en búsqueda');
                return r.json();
            })
            .then(cliente => {
                if (cliente) {
                    nombre.value = cliente.nombre || "";
                    direccion.value = cliente.direccion || "";
                    console.log("✅ Cliente encontrado:", cliente.nombre);
                } else {
                    // Cliente nuevo - limpiar campos
                    nombre.value = "";
                    direccion.value = "";
                    nombre.focus();
                }
            })
            .catch(err => {
                console.error("Error al buscar cliente:", err);
            });
    });
}

// Inicializar al cargar la página
document.addEventListener("DOMContentLoaded", function() {
    buscarClientePorTelefono();
    
    // Permitir crear pedido con Enter en el modal
    const modalInputs = document.querySelectorAll('#modalCliente input');
    modalInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                crearPedido();
            }
        });
    });
});
