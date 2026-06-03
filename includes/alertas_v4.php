<?php if(isset($_SESSION['no_acceso'])): ?>
    <div class="mb-6 flex items-center p-4 text-rose-800 rounded-[1.5rem] bg-rose-50 border border-rose-100 animate-fade-in shadow-sm" role="alert">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center mr-3 shadow-sm"><i class="fas fa-lock"></i></div>
        <div class="text-sm font-bold"><?php echo $_SESSION['no_acceso']; unset($_SESSION['no_acceso']); ?></div>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['correcto'])): ?>
    <div class="mb-6 flex items-center p-4 text-emerald-800 rounded-[1.5rem] bg-emerald-50 border border-emerald-100 animate-fade-in shadow-sm" role="alert">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center mr-3 shadow-sm"><i class="fas fa-check-circle"></i></div>
        <div class="text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></div>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['estado']) || isset($_SESSION['error'])): ?>
    <?php 
    $msg = $_SESSION['estado'] ?? $_SESSION['error'];
    $key = isset($_SESSION['estado']) ? 'estado' : 'error';
    ?>
    <div class="mb-6 flex items-center p-4 text-rose-800 rounded-[1.5rem] bg-rose-50 border border-rose-100 animate-fade-in shadow-sm" role="alert">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center mr-3 shadow-sm"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="text-sm font-bold"><?php echo $msg; unset($_SESSION[$key]); ?></div>
    </div>
<?php endif; ?>
