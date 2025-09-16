<link rel="stylesheet" href="assets/css/footer.css">



<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-col">
      <h4>Tienda</h4>
      <ul>
        <li><a href="productos.php">Catálogo</a></li>
        <li><a href="#">Promociones</a></li>
        <li><a href="#">Novedades</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Ayuda</h4>
      <ul>
        <li><a href="contacto.php">Contacto</a></li>
        <li><a href="#">Envíos</a></li>
        <li><a href="#">Devoluciones</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Contacto</h4>
      <address>
        Satélite Norte, Warnes<br>
        <a href="tel:+59170000000">+591 70000000</a><br>
        <a href="mailto:info@minichic.com">info@minichic.com</a>
      </address>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      © 2025 MINI CHIC. Todos los derechos reservados.
    </div>
  </div>
</footer>


<style>



body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    margin: 0;
}

.site-footer {
    margin-top: auto;
    background-color: #222;   
    color: #f5f5f5;         
    padding: 3rem 0 1rem 0;   
    font-family: Arial, sans-serif;
    font-size: 0.95rem;     
    
}

.site-footer a {
    color: #ccc;           
    text-decoration: none;
    transition: color 0.3s;
}

.site-footer a:hover {
    color: #17a2b8; /* Color principal de Mini Chic */
}

.footer-grid {
   display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.footer-col {
    flex: 1 1 200px;
}

.footer-bottom {
    text-align: center;
    margin-top: 2rem;
    color: #777;
    font-size: 0.8rem;
}
.footer-col h4 {
    font-size: 1.2rem;
    margin-bottom: 1rem;
    color: #fff;
}
.footer-bottom {
    text-align: center;
    border-top: 1px solid #444;
    padding: 1rem 0;
    margin-top: 2rem;
    font-size: 0.8rem;
    color: #aaa;
}


</style>