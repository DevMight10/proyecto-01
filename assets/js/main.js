// Función para agregar productos al carrito via AJAX
function addToCart(productId) {
  fetch("ajax/add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "producto_id=" + productId,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Actualizar contador del carrito
        updateCartCounter()
        showNotification("Producto agregado al carrito", "success")
      } else {
        showNotification(data.message || "Error al agregar producto", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error al agregar producto", "error")
    })
}

// Actualizar contador del carrito
function updateCartCounter() {
  fetch("ajax/get_cart_count.php")
    .then((response) => response.json())
    .then((data) => {
      const cartLinks = document.querySelectorAll('a[href="carrito.php"]')
      cartLinks.forEach((link) => {
        link.innerHTML = '<i class="fas fa-shopping-cart"></i> Carrito (' + data.count + ")"
      })
    })
}

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
  // Actualizar contador del carrito al cargar la página
  updateCartCounter()

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
