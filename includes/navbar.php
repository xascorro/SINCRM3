    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-toogled toogled" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        <div class="sidebar-brand-icon rotate-n-15">
          <i class="fas fa-water"></i>
        </div>
        <div class="sidebar-brand-text mx-3">SINC<span style="color:#FF6C60">RM</span> <sup>3</sup></div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item active">
        <a class="nav-link" href="index.php">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
        </li>
        <!-- Divider -->

        <hr class="sidebar-divider">
        <!-- Heading -->
        <div class="sidebar-heading">
          Administración
        </div>
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUsuarios" aria-expanded="true" aria-controls="collapseUsuarios">
            <i class="fas fa-fw fa-cog"></i>
            <span>Usuarios</span>
          </a>
          <div id="collapseUsuarios" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Usuarios:</h6>
              <a class="collapse-item" href="usuarios.php">Usuarios</a>
              <a class="collapse-item" href="niveles.php">Niveles</a>
            </div>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDB" aria-expanded="true" aria-controls="collapseDB">
            <i class="fas fa-fw fa-database"></i>
            <span>Base de datos</span>
          </a>
          <div id="collapseDB" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Base de datos:</h6>
              <a class="collapse-item" href="setup.php">Configuración</a>
              <a class="collapse-item" href="niveles.php">Guardar</a>
              <a class="collapse-item" href="niveles.php">Cargar</a>
            </div>
          </div>
        </li>
        <!-- Divider -->
        <hr class="sidebar-divider">
        <!-- Heading -->
        <div class="sidebar-heading">
          Datos
        </div>
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDatos" aria-expanded="true" aria-controls="collapseDatos">
            <i class="fas fa-fw fa-cog"></i>
            <span>Datos</span>
          </a>
          <div id="collapseDatos" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Usuarios:</h6>
              <a class="collapse-item" href="competiciones.php">
                <i class="fas fa-fw fa-flag"></i>
                <span>Competiciones</span>
              </a> 
              <a class="collapse-item" href="federaciones.php">
                <i class="fas fa-fw fa-flag-checkered"></i>
                <span>Federaciones</span>
              </a> 
              <a class="collapse-item" href="clubes.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Clubs</span>
              </a> 
              <a class="collapse-item" href="nadadoras.php">
                <i class="fas fa-fw fa-female"></i>
                <span>Nadadoras</span>
              </a> 
              <a class="collapse-item" href="categorias.php">
                <i class="fas fa-signal"></i>
                <span>Categorías</span>
              </a>           
              <a class="collapse-item" href="jueces.php">
                <i class="fa fa-fw fa-gavel"></i>
                <span>Jueces</span>
              </a> 
              <a class="collapse-item" href="figuras.php">
                <i class="fab fa-xing"></i>
                <span>Figuras</span>
              </a> 
              <a class="collapse-item" href="modalidades.php">
                TO-DO<i class="fab fa-modx"></i>
                <span>Modalidades</span>
              </a>
              <a class="collapse-item" href="imagenes.php">
                TO-DO<i class="fas fa-images"></i>
                <span>Imágenes</span>
              </a>       



            </div>
          </div>
        </li>



        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCompeticion" aria-expanded="true" aria-controls="collapseCompeticion">
            <i class="fas fa-fw fa-flag-checkered"></i>
            <span>Competición</span>
          </a>
          <div id="collapseCompeticion" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Configurar competición</h6>
              <a class="collapse-item" href="fases.php">
                <i class="fas fa-fw fa-signal"></i>
                <span>Fases</span>
              </a> 
              <a class="collapse-item" href="paneles_jueces.php">
                <i class="fa fa-fw fa-balance-scale-right"></i>
                <span>Paneles jueces</span>
              </a> 

              <!-- Nav Item - Pages Collapse Menu -->
        <!-- <li class="nav-item">
          <a class="nav-link collapsed" href="competiciones.php">
            <i class="fas fa-fw fa-flag"></i>
            <span>Competiciones</span>
          </a>
        </li> -->
        



        <!-- Heading -->
        <div class="sidebar-heading">
          Interface
        </div>

        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span>Components</span>
          </a>
          <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Custom Components:</h6>
              <a class="collapse-item" href="buttons.html">Buttons</a>
              <a class="collapse-item" href="cards.html">Cards</a>
            </div>
          </div>
        </li>

        <!-- Nav Item - Utilities Collapse Menu -->
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Utilities</span>
          </a>
          <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Custom Utilities:</h6>
              <a class="collapse-item" href="utilities-color.html">Colors</a>
              <a class="collapse-item" href="utilities-border.html">Borders</a>
              <a class="collapse-item" href="utilities-animation.html">Animations</a>
              <a class="collapse-item" href="utilities-other.html">Other</a>
            </div>
          </div>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
          Addons
        </div>

        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Pages</span>
          </a>
          <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Login Screens:</h6>
              <a class="collapse-item" href="login.html">Login</a>
              <a class="collapse-item" href="register.html">Register</a>
              <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
              <div class="collapse-divider"></div>
              <h6 class="collapse-header">Other Pages:</h6>
              <a class="collapse-item" href="404.html">404 Page</a>
              <a class="collapse-item" href="blank.html">Blank Page</a>
            </div>
          </div>
        </li>

        <!-- Nav Item - Charts -->
        <li class="nav-item">
          <a class="nav-link" href="charts.html">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Charts</span></a>
          </li>

          <!-- Nav Item - Tables -->
          <li class="nav-item">
            <a class="nav-link" href="tables.html">
              <i class="fas fa-fw fa-table"></i>
              <span>Tables</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
              <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

          </ul>
          <!-- End of Sidebar -->










          <!-- Scroll to Top Button-->
          <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
          </a>


