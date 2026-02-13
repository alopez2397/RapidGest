/**
 * JavaScript para TPV (Terminal Punto de Venta)
 * Gestión de pedidos, artículos y cobro
 * Optimizado para móvil
 */

let totalPedido = 0;
let numArticulos = 0;

/**
 * Toggle panel de familias en móvil
 */
function toggleFamilias() {
    const panel = document.getElementById('familiasPanel');
    const overlay = document.getElementById('overlay');
    const articulosPanel = document.getElementById('articulosPanel');

    if (window.innerWidth <= 768) {
        panel.classList.toggle('show');
        overlay.classList.toggle('show');

        if (panel.classList.contains('show')) {
            articulosPanel.classList.remove('show');
        }
    }
}

/**
 * Toggle panel de pedido en móvil
 */
function togglePedido() {
    const panel = document.getElementById('pedidoPanel');
    const overlay = document.getElementById('overlay');
    const articulosPanel = document.getElementById('articulosPanel');
    const familiasPanel = document.getElementById('familiasPanel');

    if (window.innerWidth <= 768) {
        const isShowing = panel.classList.contains('show');

        if (isShowing) {
            // Cerrar pedido, mostrar artículos
            panel.classList.remove('show');
            overlay.classList.remove('show');
            articulosPanel.classList.add('show');
        } else {
            // Abrir pedido, cerrar todo lo demás
            panel.classList.add('show');
            overlay.classList.add('show');
            articulosPanel.classList.remove('show');
            familiasPanel.classList.remove('show');
        }
    }
}

/**
 * Cerrar todos los paneles y volver a artículos
 */
function cerrarPaneles() {
    const familiasPanel = document.getElementById('familiasPanel');
    const pedidoPanel = document.getElementById('pedidoPanel');
    const overlay = document.getElementById('overlay');
    const articulosPanel = document.getElementById('articulosPanel');

    if (familiasPanel) familiasPanel.classList.remove('show');
    if (pedidoPanel) pedidoPanel.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
    if (articulosPanel) articulosPanel.classList.add('show');
}

/**
 * Actualizar badge de número de artículos
 */
function actualizarBadge() {
    const badge = document.getElementById('badgePedido');
    if (badge) {
        const numAnterior = parseInt(badge.textContent) || 0;
        badge.textContent = numArticulos;

        if (numArticulos > 0) {
            badge.style.display = 'flex';

            // Animación cuando aumenta
            if (numArticulos > numAnterior) {
                badge.style.animation = 'none';
                setTimeout(() => {
                    badge.style.animation = 'bounce 0.5s';
                }, 10);
            }
        } else {
            badge.style.display = 'none';
        }
    }
}

/**
 * Carga los artículos de una familia
 */
function cargarArticulos(idFamilia) {
    // Cerrar panel de familias en móvil
    if (window.innerWidth <= 768) {
        cerrarPaneles();
    }

    fetch("ajax/articulos.php?familia=" + idFamilia)
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar artículos');
            return response.text();
        })
        .then(html => {
            document.getElementById("articulosPanel").innerHTML = html;
        })
        .catch(err => {
            console.error("Error:", err);
            document.getElementById("articulosPanel").innerHTML =
                '<div class="alert alert-danger">❌ Error al cargar artículos</div>';
        });
}

/**
 * Añade un artículo al pedido
 */
function addArticulo(idarticulo, articulo, precio) {
    fetch("ajax/addLinea.php", {
        method: "POST",
        body: new URLSearchParams({
            pedido_id: PEDIDO_ID,
            articulo: articulo,
            pvp: precio
        })
    })
        .then(r => {
            if (!r.ok) throw new Error('Error al añadir artículo');
            return r.json();
        })
        .then(data => {
            if (!data.ok) {
                alert("❌ " + (data.error || "Error al añadir artículo"));
                return;
            }

            document.getElementById("lineas").innerHTML = data.lineas;
            document.getElementById("total").innerText = data.total;
            totalPedido = parseFloat(data.total);

            // Contar artículos para el badge
            numArticulos = contarArticulos(data.lineas);
            actualizarBadge();

            // Vibración táctil en móvil
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("❌ Error de conexión");
        });
}

/**
 * Cuenta el número de artículos en el HTML de líneas
 */
function contarArticulos(lineasHtml) {
    const temp = document.createElement('div');
    temp.innerHTML = lineasHtml;
    return temp.querySelectorAll('.linea-pedido, .border').length;
}

/**
 * Suma cantidad de una línea
 */
function sumarLinea(id) {
    modificarLinea(id, "sumar");
}

/**
 * Resta cantidad de una línea
 */
function restarLinea(id) {
    modificarLinea(id, "restar");
}

/**
 * Modifica cantidad de una línea
 */
function modificarLinea(id, accion) {
    fetch("ajax/modlinea.php", {
        method: "POST",
        body: new URLSearchParams({
            id: id,
            accion: accion
        })
    })
        .then(r => {
            if (!r.ok) throw new Error('Error al modificar línea');
            return r.json();
        })
        .then(data => {
            if (!data.ok) {
                alert("❌ " + (data.error || "Error al modificar línea"));
                return;
            }

            document.getElementById("lineas").innerHTML = data.lineas;
            document.getElementById("total").innerText = data.total;
            totalPedido = parseFloat(data.total);

            // Actualizar contador
            numArticulos = contarArticulos(data.lineas);
            actualizarBadge();

            // Vibración táctil
            if (navigator.vibrate) {
                navigator.vibrate(30);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("❌ Error de conexión");
        });
}

/**
 * Abre el modal de cobro
 */
function cobrar() {
    const total = document.getElementById("total").innerText;

    if (parseFloat(total) <= 0) {
        alert("⚠️ El pedido está vacío");
        return;
    }

    document.getElementById("cobroTotal").innerText = total;
    document.getElementById("importeRecibido").value = "0";
    document.getElementById("cambio").innerText = "0.00";

    // Resetear radio buttons
    document.getElementById("envioNada").checked = true;

    new bootstrap.Modal(document.getElementById("modalCobro")).show();
}

/**
 * Maneja el teclado numérico
 */
function tecla(valor) {
    let input = document.getElementById("importeRecibido");

    if (input.value === "0") {
        input.value = "";
    }

    // Evitar múltiples puntos decimales
    if (valor === '.' && input.value.includes('.')) {
        return;
    }

    input.value += valor;
    calcularCambio();
}

/**
 * Borra el importe recibido
 */
function borrar() {
    document.getElementById("importeRecibido").value = "0";
    document.getElementById("cambio").innerText = "0.00";
}

/**
 * Calcula el cambio
 */
function calcularCambio() {
    let recibido = parseFloat(document.getElementById("importeRecibido").value) || 0;
    let total = parseFloat(document.getElementById("cobroTotal").innerText);
    let cambio = recibido - total;

    document.getElementById("cambio").innerText =
        cambio >= 0 ? cambio.toFixed(2) : "0.00";
}

/**
 * Confirma el cobro
 */
function confirmarCobro() {
    let recibido = document.getElementById("importeRecibido").value.trim();

    // Normalizar decimales
    recibido = recibido.replace(',', '.');

    // Validaciones
    if (recibido === "" || recibido === "." || isNaN(recibido) || parseFloat(recibido) <= 0) {
        alert("⚠️ Introduce un importe válido");
        return;
    }

    const total = parseFloat(document.getElementById("cobroTotal").innerText);
    if (parseFloat(recibido) < total) {
        alert("⚠️ El importe recibido es insuficiente");
        return;
    }

    // Deshabilitar botón
    const btnCobrar = event.target;
    btnCobrar.disabled = true;
    btnCobrar.textContent = 'Procesando...';

    fetch("ajax/cobrar.php", {
        method: "POST",
        body: new URLSearchParams({
            pedido_id: PEDIDO_ID,
            recibido: recibido
        })
    })
        .then(r => {
            if (!r.ok) throw new Error('Error en el servidor');
            return r.json();
        })
        .then(data => {
            if (!data.ok) {
                alert("❌ " + (data.error || "Error al cobrar"));
                btnCobrar.disabled = false;
                btnCobrar.textContent = '✅ CONFIRMAR COBRO';
                return;
            }

            // Generar PDF
            return fetch("print/ticket_pdf.php?pedido=" + PEDIDO_ID);
        })
        .then(() => {
            // Gestionar envío del ticket
            const envio = getEnvioSeleccionado();

            if (envio === "whatsapp") {
                enviarWhatsApp(PEDIDO_ID, CLIENTE.telefono);
            } else if (envio === "email") {
                enviarEmail(PEDIDO_ID);
            }

            // Cerrar modal y volver
            setTimeout(() => {
                volverInicio();
            }, 500);
        })
        .catch(err => {
            console.error("Error:", err);
            alert("❌ Error al procesar el cobro");
            btnCobrar.disabled = false;
            btnCobrar.textContent = '✅ CONFIRMAR COBRO';
        });
}

/**
 * Marca el pedido como servido
 */
function servir() {
    if (!confirm("¿Marcar pedido como servido?")) return;

    fetch("ajax/servir.php", {
        method: "POST",
        body: new URLSearchParams({ pedido_id: PEDIDO_ID })
    })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                volverInicio();
            } else {
                alert("❌ Error al marcar como servido");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("❌ Error de conexión");
        });
}

/**
 * Cancela el pedido
 */
function cancelar() {
    if (!confirm("⚠️ ¿Estás seguro de cancelar este pedido?\n\nEsta acción eliminará el pedido y todas sus líneas.")) {
        return;
    }

    fetch("ajax/cancelarpedido.php", {
        method: "POST",
        body: new URLSearchParams({ pedido_id: PEDIDO_ID })
    })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert("✅ Pedido cancelado");
                volverInicio();
            } else {
                alert("❌ Error al cancelar pedido");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("❌ Error de conexión");
        });
}

/**
 * Imprime el ticket
 */
function imprimirPedido() {
    window.open(
        "print/ticket_pdf.php?pedido=" + PEDIDO_ID,
        "_blank"
    );
}

/**
 * Finaliza pedido como local
 */
function finalizarPedidoLocal() {
    if (!confirm("¿Finalizar pedido para recogida en local?\n\nEl pedido quedará pendiente de cobro y servicio.")) {
        return;
    }

    fetch("ajax/finalizarpedidolocal.php", {
        method: "POST",
        body: new URLSearchParams({ pedido_id: PEDIDO_ID })
    })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert("✅ Pedido guardado para recogida en local");
                volverInicio();
            } else {
                alert("❌ Error al finalizar pedido");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("❌ Error de conexión");
        });
}

/**
 * Finaliza pedido como domicilio
 */
function finalizarPedidoDomicilio() {
    if (!confirm("¿Finalizar pedido para entrega a domicilio?\n\nEl pedido quedará pendiente de cobro y entrega.")) {
        return;
    }

    fetch("ajax/finalizarpedidodomicilio.php", {
        method: "POST",
        body: new URLSearchParams({ pedido_id: PEDIDO_ID })
    })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert("✅ Pedido guardado para entrega a domicilio");
                volverInicio();
            } else {
                alert("❌ Error al finalizar pedido");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("❌ Error de conexión");
        });
}

/**
 * Vuelve al inicio
 */
function volverInicio() {
    window.location.href = "index.php";
}

/**
 * Obtiene el método de envío seleccionado
 */
function getEnvioSeleccionado() {
    const selected = document.querySelector('input[name="envioTicket"]:checked');
    return selected ? selected.value : 'nada';
}

/**
 * Envía ticket por email
 */
function enviarEmail(pedidoId) {
    const email = prompt("Introduce el email del cliente:");

    if (!email) return;

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("❌ Email no válido");
        return;
    }

    fetch("ajax/enviarTicketEmail.php", {
        method: "POST",
        body: new URLSearchParams({
            pedido_id: pedidoId,
            email: email
        })
    })
        .then(r => {
            if (!r.ok) throw new Error("Error HTTP " + r.status);
            return r.json();
        })
        .then(res => {
            if (res.ok) {
                alert("✅ Ticket enviado por email correctamente");
            } else {
                alert("❌ Error: " + (res.error || "No se pudo enviar el email"));
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("❌ No se pudo enviar el email");
        });
}

function formatearTelefono(telefono, codigoPais) {
    // Eliminar todo lo que no sea dígito
    let limpio = telefono.replace(/\D/g, "");

    // Eliminar ceros iniciales para evitar conflictos
    limpio = limpio.replace(/^0+/, "");

    // Eliminar el código de país si ya está presente
    if (limpio.startsWith(codigoPais)) {
        return `+${limpio}`;
    }
    // Añadir el código de país
    return `+${codigoPais}${limpio}`;
}

/**
 * Envía ticket por WhatsApp
 */
function enviarWhatsApp(pedidoId, telefono) {
    // Limpiar teléfono (solo números)
   // telefono = telefono.replace(/\D/g, "");
    

    if (!telefono) {
        alert("⚠️ No hay teléfono registrado para este cliente");
        return;
    }
	let telefonoinicial = telefono;
	let codigoPais = "34"; // España

	let telefonoFinal = formatearTelefono(telefono, codigoPais);
	console.log(telefonoFinal); // +34612345678
    
    const urlPDF = window.location.origin + "/tickets/ticket_" + pedidoId + ".pdf";

    const mensaje = `¡Gracias por su pedido! 🍕

Pedido Nº ${pedidoId}

Puede descargar su ticket aquí:
${urlPDF}

Pizzeria La Fuente`;
    $codigopais = '+34';

    const wa = "https://wa.me/" + telefonoFinal + "?text=" + encodeURIComponent(mensaje);

    window.open(wa, "_blank");
}

/**
 * Carga el pedido actual al iniciar
 */
function cargarPedidoActual() {
    fetch("ajax/cargarpedido.php")
        .then(r => {
            if (!r.ok) throw new Error('Error al cargar pedido');
            return r.json();
        })
        .then(data => {
            document.getElementById("lineas").innerHTML = data.lineas || '';
            document.getElementById("total").innerText = data.total || '0.00';
            totalPedido = parseFloat(data.total) || 0;

            // Inicializar contador
            numArticulos = contarArticulos(data.lineas || '');
            actualizarBadge();
        })
        .catch(err => {
            console.error("Error:", err);
            document.getElementById("lineas").innerHTML =
                '<div class="alert alert-danger">❌ Error al cargar pedido</div>';
        });
}

// Inicializar al cargar la página
document.addEventListener("DOMContentLoaded", function () {
    cargarPedidoActual();

    // Ajustar paneles en cambio de orientación
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            cerrarPaneles();
            document.getElementById('articulosPanel').classList.add('show');
        }
    });
});