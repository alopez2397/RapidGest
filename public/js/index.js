function abrirNuevoPedido() {
    const modal = new bootstrap.Modal(
        document.getElementById("modalCliente")
    );
    modal.show();
}

function crearPedido() {

    const telefono = document.getElementById("telefono").value.trim();
    const nombre = document.getElementById("nombre").value.trim();
    const direccion = document.getElementById("direccion").value.trim();

    if (telefono === '') {
        alert("Introduce un teléfono");
        return;
    }

    let data = new URLSearchParams({
        telefono,
        nombre,
        direccion
    });

    fetch("ajax/crearPedido.php", {
        method: "POST",
        body: data
    })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                window.location.href = "tpv.php";
            } else {
                alert(d.error || "Error al crear pedido");
            }
        })
        .catch(err => console.error(err));
}

document.addEventListener("DOMContentLoaded", () => {

    const telefono = document.getElementById("telefono");
    const nombre = document.getElementById("nombre");
    const direccion = document.getElementById("direccion");
     
    if (!telefono) return; // seguridad

    telefono.addEventListener("blur", () => {

        let tel = telefono.value.trim();

        if (tel.length < 6) return;

        fetch("ajax/buscarCliente.php?telefono=" + encodeURIComponent(tel))
            .then(r => r.json())
            .then(c => {

                if (c) {
                    nombre.value = c.nombre || "";
                    direccion.value = c.direccion || "";
                } else {
                    nombre.value = "";
                    direccion.value = "";
                }
            })
            .catch(err => console.error(err));
    });
});


