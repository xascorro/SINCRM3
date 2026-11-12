<?php
$query_pen = "SELECT p.id, p.codigo, p.puntos, p.resumen, p.aplicable_a, p.id_paneles_tipo, 
                     COALESCE(pt.nombre, 'Rutina') as panel_nombre 
              FROM penalizaciones p 
              LEFT JOIN paneles_tipo pt ON p.id_paneles_tipo = pt.id 
              ORDER BY 
                CASE 
                    WHEN p.id_paneles_tipo = 0 THEN 1 
                    WHEN p.id_paneles_tipo = 1 THEN 2 
                    WHEN p.id_paneles_tipo = 2 THEN 3 
                    WHEN p.id_paneles_tipo = 6 THEN 4 
                    ELSE 5 
                END ASC, 
                ABS(p.puntos) DESC, 
                p.codigo ASC";

$query_run_pen = mysqli_query($connection, $query_pen);

$grupos = [
    0 => ['label' => '🏛️ Penalizaciones de Rutina / Juez Árbitro (Panel 0)', 'items' => []],
    1 => ['label' => '⚡ Penalizaciones de Elementos (Panel 1)', 'items' => []],
    2 => ['label' => '🎭 Penalizaciones de Impresión Artística (Panel 2)', 'items' => []],
    6 => ['label' => '🏊 Penalizaciones de Figuras (Panel 6)', 'items' => []],
];

if ($query_run_pen && mysqli_num_rows($query_run_pen) > 0) {
    while ($row = mysqli_fetch_assoc($query_run_pen)) {
        $pt_id = intval($row['id_paneles_tipo'] ?? 0);
        if (!isset($grupos[$pt_id])) {
            $grupos[$pt_id] = ['label' => '⚙️ ' . htmlspecialchars($row['panel_nombre']), 'items' => []];
        }
        $grupos[$pt_id]['items'][] = $row;
    }
}
?>

<div class="w-full">
    <label for="id_penalizacion" class="block text-[11px] font-black uppercase text-slate-500 tracking-wider mb-2 flex items-center justify-between">
        <span>Catálogo Oficial AQUA / RFEN</span>
        <span class="text-[10px] font-bold text-slate-400 lowercase italic">Se aplicará al guardar</span>
    </label>
    
    <div class="relative">
        <select tabindex="900" name="id_penalizacion" id="id_penalizacion" class="w-full bg-white border border-slate-200/90 rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 shadow-xs hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none appearance-none cursor-pointer">
            <option value="0" class="text-slate-400 font-normal">-- Sin penalización nueva (mantener actuales) --</option>
            <?php foreach ($grupos as $pt_id => $grupo): ?>
                <?php if (!empty($grupo['items'])): ?>
                    <optgroup label="<?php echo htmlspecialchars($grupo['label']); ?>" class="font-bold text-slate-900 bg-slate-50">
                        <?php foreach ($grupo['items'] as $item): ?>
                            <?php 
                                $pts = floatval($item['puntos']);
                                $pts_str = $pts == 0 ? 'Nota 0' : ($pts > 0 ? "+$pts pts" : "$pts pts");
                                $aplic = !empty($item['aplicable_a']) ? ' [' . $item['aplicable_a'] . ']' : '';
                            ?>
                            <option value="<?php echo $item['id']; ?>" class="bg-white text-slate-800 font-medium py-1">
                                <?php echo htmlspecialchars($item['codigo'] . ' (' . $pts_str . ') ' . $item['resumen'] . $aplic); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
            <i class="fa-solid fa-chevron-down text-xs"></i>
        </div>
    </div>
</div>
