<!-- includes/footer.php -->
<style>
  .footer-container {
    margin-top: 3rem;
    padding: 0;
    width: 100%;
  }
  
  .footer-main {
    background-color: var(--buzios-azul);
    color: #fff;
    padding: 1rem 0;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.2);
  }
  
  .footer-logo {
    height: 40px;
    margin-right: 10px;
  }
  
  .footer-content {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .footer-text {
    font-size: 1rem;
    margin: 0;
    color: #fff;
  }
  
  @media (max-width: 576px) {
    .footer-container {
      margin-top: 2rem;
    }
    
    .footer-logo {
      height: 35px;
    }
    
    .footer-text {
      font-size: 0.9rem;
    }
    
    .footer-content {
      flex-direction: column;
    }
    
    .footer-logo {
      margin-right: 0;
      margin-bottom: 0.5rem;
    }
  }
</style>

<div class="container-fluid footer-container">
  <div class="footer-main">
    <div class="container">
      <div class="footer-content">
        <img src="/assets/img/buzios_logo.png" alt="Búzios Logo" class="footer-logo">
        <span class="footer-text">Sistema de Transporte Universitário</span>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

