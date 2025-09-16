// Mostrar notificaciones
function showNotification(message, type) {
  const notification = document.createElement("div")
  notification.className = `notification ${type}`
  notification.textContent = message

  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 2rem;
        border-radius: 10px;
        color: white;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        background-color: ${type === "success" ? "#28a745" : "#dc3545"};
    `

  document.body.appendChild(notification)

  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease"
    setTimeout(() => {
      document.body.removeChild(notification)
    }, 300)
  }, 3000)
}

// Animaciones CSS
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`
document.head.appendChild(style)

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {

  // Confirmar antes de eliminar elementos
  const deleteButtons = document.querySelectorAll(".btn-danger")
  deleteButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      if (!confirm("¿Estás seguro de que quieres eliminar este elemento?")) {
        e.preventDefault()
      }
    })
  })
})

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('message-modal');
    if (modal) {
        const closeBtn = modal.querySelector('.close-button');

        document.querySelectorAll('.view-message-btn').forEach(button => {
            button.addEventListener('click', function() {
                const mensajeId = this.dataset.id;
                const fila = this.closest('tr');

                // Marcar como leído (si no lo está ya)
                if (fila.classList.contains('mensaje-nuevo')) {
                    fetch(`mensajes.php?toggle_leido=1&id=${mensajeId}`, {
                        method: 'GET' // Usamos GET para simplicidad aquí, pero POST sería ideal
                    }).then(response => {
                        if (response.ok) {
                            fila.classList.remove('mensaje-nuevo');
                            fila.classList.add('mensaje-leido');
                            const badge = fila.querySelector('.badge');
                            if (badge) {
                                badge.classList.remove('badge-nuevo');
                                badge.classList.add('badge-leido');
                                badge.textContent = 'Leído';
                            }
                        }
                    });
                }

                document.getElementById('modal-asunto').textContent = this.dataset.asunto;
                document.getElementById('modal-nombre').textContent = this.dataset.nombre;
                document.getElementById('modal-email').textContent = this.dataset.email;
                document.getElementById('modal-fecha').textContent = this.dataset.fecha;
                document.getElementById('modal-mensaje').innerHTML = this.dataset.mensaje;
                modal.style.display = 'block';
            });
        });

        if (closeBtn) {
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    }
});