<?php
require_once("config/conexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liga Panteras - Fútbol 7</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Swiper CSS (para carousel de banners) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- CSS Público -->
    <link rel="stylesheet" href="css/index_publico.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-futbol"></i>
                <span class="brand-text">LIGA PANTERAS</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#inicio">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#divisiones">Divisiones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#partidos">Partidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#resultados">Resultados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#goleadores">Goleadores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-login" href="pages/login.php">
                            <i class="fas fa-sign-in-alt"></i> Acceso
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section con Banners -->
    <section id="inicio" class="hero-section">
        <div class="hero-overlay"></div>
        
        <!-- Swiper Banner Carousel -->
        <div class="swiper bannerSwiper">
            <div class="swiper-wrapper" id="bannersContainer">
                <!-- Se llena dinámicamente con los banners activos -->
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>

        <div class="hero-content">
            <div class="container text-center">
                <h1 class="hero-title">
                    <i class="fas fa-trophy"></i>
                    LIGA PANTERAS
                </h1>
                <p class="hero-subtitle">La liga de fútbol 7 más competitiva</p>
                <div class="hero-cta">
                    <a href="#divisiones" class="btn btn-primary-custom">
                        <i class="fas fa-eye"></i> Ver Divisiones
                    </a>
                    <a href="#partidos" class="btn btn-outline-custom">
                        <i class="fas fa-calendar"></i> Partidos de la Semana
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Divisiones -->
    <section id="divisiones" class="divisiones-section py-5">
        <div class="container">
            <div class="section-title">
                <h2><i class="fas fa-trophy"></i> Nuestras Divisiones</h2>
                <p>4 categorías competitivas para todos los niveles</p>
            </div>

            <div class="row g-4" id="divisionesCards">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </section>

    <!-- Partidos de la Semana -->
    <section id="partidos" class="partidos-section py-5">
        <div class="container">
            <div class="section-title">
                <h2><i class="fas fa-calendar-week"></i> Partidos de la Semana</h2>
                <p>Consulta los próximos enfrentamientos</p>
            </div>

            <!-- Filtros por División -->
            <div class="filtros-division mb-4">
                <button class="btn-filtro active" data-liga="todas">
                    <i class="fas fa-th-large"></i> Todas
                </button>
                <button class="btn-filtro" data-liga="1">
                    Champions League
                </button>
                <button class="btn-filtro" data-liga="2">
                    Europa League
                </button>
                <button class="btn-filtro" data-liga="3">
                    Conference League
                </button>
                <button class="btn-filtro" data-liga="4">
                    MLS
                </button>
            </div>

            <div id="partidosContainer">
                <!-- Se llena dinámicamente -->
            </div>
        </div>
    </section>

    <!-- Últimos Resultados -->
    <section id="resultados" class="resultados-section py-5">
        <div class="container">
            <div class="section-title">
                <h2><i class="fas fa-check-circle"></i> Últimos Resultados</h2>
                <p>Revisa los marcadores más recientes</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="resultados-grid" id="resultadosContainer">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
                <div class="col-lg-4">
                    <!-- Widget: Próximas Jornadas -->
                    <div class="widget-card">
                        <div class="widget-header">
                            <i class="fas fa-calendar-alt"></i>
                            <h3>Próxima Jornada</h3>
                        </div>
                        <div class="widget-body" id="proximaJornada">
                            <!-- Se llena dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tablas de Posiciones -->
    <section id="posiciones" class="posiciones-section py-5">
        <div class="container">
            <div class="section-title">
                <h2><i class="fas fa-list-ol"></i> Tablas de Posiciones</h2>
                <p>Clasificación actual por división</p>
            </div>

            <!-- Tabs de Divisiones -->
            <ul class="nav nav-tabs mb-4" id="tablasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-champions" data-bs-toggle="tab" 
                            data-bs-target="#tabla-champions" type="button">
                        <i class="fas fa-crown"></i> Champions League
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-europa" data-bs-toggle="tab" 
                            data-bs-target="#tabla-europa" type="button">
                        <i class="fas fa-star"></i> Europa League
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-conference" data-bs-toggle="tab" 
                            data-bs-target="#tabla-conference" type="button">
                        <i class="fas fa-medal"></i> Conference League
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-mls" data-bs-toggle="tab" 
                            data-bs-target="#tabla-mls" type="button">
                        <i class="fas fa-trophy"></i> MLS
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="tablasContent">
                <div class="tab-pane fade show active" id="tabla-champions">
                    <div class="table-responsive">
                        <table class="tabla-posiciones">
                            <thead>
                                <tr>
                                    <th>Pos</th>
                                    <th>Equipo</th>
                                    <th class="text-center">PJ</th>
                                    <th class="text-center">G</th>
                                    <th class="text-center">E</th>
                                    <th class="text-center">P</th>
                                    <th class="text-center">GF</th>
                                    <th class="text-center">GC</th>
                                    <th class="text-center">DIF</th>
                                    <th class="text-center">PTS</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-champions">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabla-europa">
                    <div class="table-responsive">
                        <table class="tabla-posiciones">
                            <thead>
                                <tr>
                                    <th>Pos</th>
                                    <th>Equipo</th>
                                    <th class="text-center">PJ</th>
                                    <th class="text-center">G</th>
                                    <th class="text-center">E</th>
                                    <th class="text-center">P</th>
                                    <th class="text-center">GF</th>
                                    <th class="text-center">GC</th>
                                    <th class="text-center">DIF</th>
                                    <th class="text-center">PTS</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-europa">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabla-conference">
                    <div class="table-responsive">
                        <table class="tabla-posiciones">
                            <thead>
                                <tr>
                                    <th>Pos</th>
                                    <th>Equipo</th>
                                    <th class="text-center">PJ</th>
                                    <th class="text-center">G</th>
                                    <th class="text-center">E</th>
                                    <th class="text-center">P</th>
                                    <th class="text-center">GF</th>
                                    <th class="text-center">GC</th>
                                    <th class="text-center">DIF</th>
                                    <th class="text-center">PTS</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-conference">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabla-mls">
                    <div class="table-responsive">
                        <table class="tabla-posiciones">
                            <thead>
                                <tr>
                                    <th>Pos</th>
                                    <th>Equipo</th>
                                    <th class="text-center">PJ</th>
                                    <th class="text-center">G</th>
                                    <th class="text-center">E</th>
                                    <th class="text-center">P</th>
                                    <th class="text-center">GF</th>
                                    <th class="text-center">GC</th>
                                    <th class="text-center">DIF</th>
                                    <th class="text-center">PTS</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-mls">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabla de Goleadores -->
    <section id="goleadores" class="goleadores-section py-5">
        <div class="container">
            <div class="section-title">
                <h2><i class="fas fa-futbol"></i> Tabla de Goleo</h2>
                <p>Los máximos anotadores de la temporada</p>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="goleadores-card">
                        <div class="table-responsive">
                            <table class="tabla-goleadores">
                                <thead>
                                    <tr>
                                        <th>Pos</th>
                                        <th>Jugador</th>
                                        <th>Equipo</th>
                                        <th>División</th>
                                        <th class="text-center">Goles</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-goleadores">
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h3><i class="fas fa-futbol"></i> Liga Panteras</h3>
                    <p>La liga de fútbol 7 más competitiva de la región. Únete a nosotros y vive la pasión del fútbol.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h4>Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="#divisiones">Divisiones</a></li>
                        <li><a href="#partidos">Partidos</a></li>
                        <li><a href="#resultados">Resultados</a></li>
                        <li><a href="#posiciones">Posiciones</a></li>
                        <li><a href="#goleadores">Goleadores</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h4>Contacto</h4>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i> Ubicación de las canchas</li>
                        <li><i class="fas fa-phone"></i> (81) 1234-5678</li>
                        <li><i class="fas fa-envelope"></i> info@ligapanteras.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Liga Panteras. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Index Público JS -->
    <script src="js/index_publico.js"></script>

</body>
</html>