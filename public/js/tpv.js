let TOTAL_PEDIDO = 0;

function cargarArticulos(id) {
    fetch("ajax/articulos.php?id=" + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById("articulos").innerHTML = html;
        })
        .catch(err => console.error(err));
}

function addArticulo(idarticulo, articulo, precio) {

    fetch("ajax/addLinea.php", {
        method: "POST",
        body: new URLSearchParams({
            pedido_id: PEDIDO_ID,
            articulo: articulo,
            pvp: precio
        })
    })
.then(r => r.json())
.then(data => {
    if (!data.ok) {
        alert(data.error);
        return;
    }

    document.getElementById("lineas").innerHTML = data.lineas;
    document.getElementById("total").innerText = data.total;
});
}

function cobrar() {
    document.getElementById("cobroTotal").innerText =
        document.getElementById("total").innerText;

    document.getElementById("importeRecibido").value = "0";
    document.getElementById("cambio").innerText = "0.00";

    new bootstrap.Modal(document.getElementById("modalCobro")).show();
}

function servir() {
    fetch("ajax/servir.php")
        .then(r => r.json())
        .then(() => volverInicio());
}

function cancelar() {
    if (!confirm("¿Cancelar pedido?")) return;

    fetch("ajax/cancelarPedido.php")
        .then(r => r.json())
        .then(data => {
            if (data.ok) volverInicio();
            else alert("Error al cancelar");
        });
}

function imprimirPedido() {
    window.open(
        "print/ticket_pdf.php?pedido=" + PEDIDO_ID,
        "_blank"
    );
	volverInicio();
}

function sumarLinea(id) {
    fetch("ajax/modLinea.php", {
        method: "POST",
        body: new URLSearchParams({ id, accion: "sumar" })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById("lineas").innerHTML = data.lineas;
        document.getElementById("total").innerText = data.total;
	lineasPedido = data.lineas;
    });
}

function restarLinea(id) {
    fetch("ajax/modLinea.php", {
        method: "POST",
        body: new URLSearchParams({ id, accion: "restar" })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById("lineas").innerHTML = data.lineas;
        document.getElementById("total").innerText = data.total;
	lineasPedido = data.lineas;
    });
}

function tecla(v) {
    let input = document.getElementById("importeRecibido");
    if (input.value === "0") input.value = "";
    input.value += v;
    calcularCambio();
}

function borrar() {
    document.getElementById("importeRecibido").value = "0";
    document.getElementById("cambio").innerText = "0.00";
}

function calcularCambio() {
    let recibido = parseFloat(document.getElementById("importeRecibido").value) || 0;
    let total = parseFloat(document.getElementById("total").innerText);
    let cambio = recibido - total;

    document.getElementById("cambio").innerText =
        cambio >= 0 ? cambio.toFixed(2) : "0.00";
}

function finalizarPedidoLocal() {

    if (!confirm("¿Finalizar pedido y dejarlo pendiente?")) return;

    fetch("ajax/finalizarpedidolocal.php")
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                window.location.href = "index.php";
            } else {
                alert("Error al finalizar pedido");
            }
        });
}

function finalizarPedidoDomicilio() {

    if (!confirm("¿Finalizar pedido y dejarlo pendiente?")) return;

    fetch("ajax/finalizarpedidodomicilio.php")
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                window.location.href = "index.php";
            } else {
                alert("Error al finalizar pedido");
            }
        });
}

function confirmarCobro() {

    let envio = getEnvioSeleccionado();
    let TOTAL_PEDIDO = 0;

   let recibido = document.getElementById("importeRecibido").value.trim();

    /* Normalizar */
    recibido = recibido.replace(',', '.');

    /* Validaciones frontend */
    if (
        recibido === "" ||
        recibido === "." ||
        isNaN(recibido) ||
        parseFloat(recibido) <= 0
    ) {
        alert("Introduce un importe válido");
        return;
    }


    fetch("ajax/cobrar.php", {
        method: "POST",
        body: new URLSearchParams({
            pedido_id: PEDIDO_ID, 
	    recibido: recibido 
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) {
            alert(data.error || "Error al cobrar");
            return;
        }

        // Generar PDF
        fetch("print/ticket_pdf.php?pedido=" + PEDIDO_ID)
        .then(() => {

            if (envio === "whatsapp") {
                enviarWhatsApp(PEDIDO_ID, CLIENTE.telefono);
            }

            if (envio === "email") {
                enviarEmail(PEDIDO_ID);
            }
            volverInicio();
        });
    });
}


function volverInicio() {
    window.location.href = "index.php?reload=1";
}

function getEnvioSeleccionado() {
    return document.querySelector(
        'input[name="envioTicket"]:checked'
    ).value;
}

function enviarEmail(pedidoId) {
    fetch("ajax/enviarEmail.php", {
        method: "POST",
        body: new URLSearchParams({ pedido_id: pedidoId })
    });
}

function enviarWhatsApp(pedidoId, telefono) {

    telefono = telefono.replace(/\D/g, ""); // solo números

    let urlPDF = window.location.origin +
        "/Rapidgest/tickets/ticket_" + pedidoId + ".pdf";

    let mensaje = `
Gracias por su pedido 🙌
Pedido Nº ${pedidoId}

Puede descargar su ticket aquí:
${urlPDF}
`;

    let wa = "https://wa.me/" + telefono +
        "?text=" + encodeURIComponent(mensaje);

    window.open(wa, "_blank");
}

document.addEventListener("DOMContentLoaded", () => {
    fetch("ajax/cargarPedido.php")
        .then(r => r.json())
        .then(data => {
            document.getElementById("lineas").innerHTML = data.lineas;
            document.getElementById("total").innerText = data.total;
            lineasPedido = data.lineas; 
        });
});


