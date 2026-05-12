    </div> <!-- /.container-fluid -->
</div> <!-- #content -->

<!-- Footer v4 (Prerelease) Premium -->
<footer class="bg-white/80 backdrop-blur-md py-12 border-t border-slate-200 mt-auto font-lexend">
    <div class="max-w-7xl mx-auto px-8">
        <div class="flex flex-col items-center gap-6">
            
            <!-- Enlaces Rápidos -->
            <div class="flex items-center gap-8">
                <a href="soporte.php" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-blue-600 transition-colors">Soporte Técnico</a>
                <div class="w-1.5 h-1.5 rounded-full bg-slate-200"></div>
                <a href="privacidad.php" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-blue-600 transition-colors">Privacidad</a>
            </div>

            <!-- Firma y Versión -->
            <div class="flex flex-col items-center gap-2">
                <p class="text-sm font-medium text-slate-500 flex items-center gap-2">
                    Crafted with <i class="fas fa-heart text-red-500 animate-pulse text-[10px]"></i> by 
                    <span class="font-black text-slate-800 tracking-tighter">Pedro Díaz</span>
                </p>
                <div class="flex items-center gap-3 mt-1">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-300">
                        © <?php echo date('Y'); ?> FNRM · 
                    </p>
                    <span class="px-2 py-0.5 bg-slate-50 text-slate-400 text-[8px] font-black rounded border border-slate-100 uppercase tracking-widest shadow-sm">
                        <?php 
                        include_once(dirname(__DIR__).'/lib/my_functions.php');
                        echo getVersion(); 
                        ?>
                    </span>
                    <?php 
                    $vDetails = getVersionDetails();
                    if(isset($vDetails['codename'])): ?>
                        <span class="text-[8px] font-bold text-blue-300 italic">"<?php echo $vDetails['codename']; ?>"</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Branding sutil -->
            <div class="pt-4 opacity-10 hover:opacity-50 transition-opacity duration-700">
                <p class="text-[10px] font-black italic tracking-tighter text-slate-400">SINCRM v<?php echo $vDetails['major'] ?? '4'; ?> Framework</p>
            </div>

        </div>
    </div>
</footer>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('service-worker.js')
                .then(reg => console.log('Service Worker registrado', reg.scope))
                .catch(err => console.error('Error al registrar Service Worker', err));
        });
    }
</script>

</div> <!-- #content-wrapper -->

</div> <!-- #wrapper (Abierto en header.php) -->

</body>
</html>
