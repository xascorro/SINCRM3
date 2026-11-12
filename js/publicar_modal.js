/**
 * SINCRM v4 - Modal Inteligente de Publicación Oficial en la Web y Notificaciones
 * Soporta selección de destinatarios: Modo Pruebas, Clubes Participantes y Jueces
 */

function abrirModalPublicar(tipo, tituloDoc, esActualizacion, idCompeticionOverride) {
    const idComp = idCompeticionOverride || (typeof idCompeticionActiva !== 'undefined' ? idCompeticionActiva : null);
    
    if (!idComp) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se ha detectado el identificador de la competición.'
        });
        return;
    }

    const accionTexto = esActualizacion ? 'Actualizar y Re-publicar' : 'Publicar en la Web';
    const filenamePreview = `${idComp}-${tipo}.pdf`;

    // 1. Mostrar pantalla de carga rápida mientras obtenemos los destinatarios
    Swal.fire({
        title: 'Consultando participantes...',
        html: `
            <div class="flex flex-col items-center justify-center p-4">
                <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="text-xs text-slate-500 font-medium">Consultando clubes participantes y jueces asignados...</p>
            </div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false
    });

    // 2. Consultar destinatarios de la competición al backend
    fetch(`informes_publicar_code.php?action=get_destinatarios&id_competicion=${idComp}&ajax=1`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al conectar con el servidor HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Error al obtener los destinatarios de la competición.');
            }

            mostrarModalConfiguracion(tipo, tituloDoc, accionTexto, filenamePreview, idComp, data);
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: err.message,
                confirmButtonColor: '#2563eb'
            });
        });
}

function mostrarModalConfiguracion(tipo, tituloDoc, accionTexto, filenamePreview, idComp, data) {
    const totalClubes = data.clubes ? data.clubes.total_clubes : 0;
    const totalUsuariosClubes = data.clubes ? data.clubes.total_usuarios : 0;
    const clubesNombres = (data.clubes && data.clubes.clubes_nombres) ? data.clubes.clubes_nombres.join(', ') : 'Ninguno';
    const totalJueces = data.jueces ? data.jueces.total_jueces : 0;

    const emailPrueba = data.prueba ? data.prueba.email : 'xascorro@gmail.com';

    // Generar lista para el acordeón desplegable
    let emailsClubesHtml = '';
    if (data.clubes && data.clubes.destinatarios) {
        emailsClubesHtml = data.clubes.destinatarios.map(d => 
            `<li class="flex items-center justify-between py-1 border-b border-slate-100 last:border-0">
                <span class="font-semibold text-slate-700">${d.nombre} <span class="text-[10px] text-blue-600 font-bold">(${d.club_corto || d.club})</span></span>
                <span class="font-mono text-slate-500">${d.email}</span>
            </li>`
        ).join('');
    }

    let emailsJuecesHtml = '';
    if (data.jueces && data.jueces.destinatarios) {
        emailsJuecesHtml = data.jueces.destinatarios.map(j => 
            `<li class="flex items-center justify-between py-1 border-b border-slate-100 last:border-0">
                <span class="font-semibold text-slate-700">${j.nombre}</span>
                <span class="font-mono text-slate-500">${j.email}</span>
            </li>`
        ).join('');
    }

    Swal.fire({
        title: `<div class="text-left font-black text-slate-900 text-lg sm:text-xl flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs shadow-sm">
                        <i class="fas fa-cloud-arrow-up"></i>
                    </span>
                    <span>${accionTexto}</span>
                </div>`,
        html: `
            <div class="text-left font-sans text-xs space-y-4 pt-1">
                <!-- Ficha del Documento -->
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Documento</span>
                        <strong class="text-sm font-black text-slate-800">${tituloDoc}</strong>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Archivo Web & Adjunto</span>
                        <code class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-200/80">${filenamePreview}</code>
                    </div>
                </div>

                <p class="text-slate-600 font-medium leading-relaxed">
                    Selecciona a quién deseas remitir la notificación por correo electrónico junto con el PDF adjunto generado:
                </p>

                <!-- Grupo 1: Modo Pruebas -->
                <div class="p-3.5 bg-amber-50/50 rounded-2xl border border-amber-200/90 transition-all hover:bg-amber-50">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" id="chk_dest_prueba" checked onchange="actualizarContadorDestinatarios()" class="w-4 h-4 mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-xs">Modo Prueba (Superadmin)</span>
                                <span class="px-1.5 py-0.2 bg-amber-100 text-amber-800 text-[9px] font-black uppercase rounded">Pruebas</span>
                            </div>
                            <span class="text-[11px] text-slate-600 block mt-0.5">
                                Remite copia al email técnico: <strong class="font-mono text-slate-800">${emailPrueba}</strong>
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Grupo 2: Clubes que compiten -->
                <div class="p-3.5 bg-blue-50/40 rounded-2xl border border-blue-200/80 transition-all hover:bg-blue-50/70">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" id="chk_dest_clubes" checked onchange="actualizarContadorDestinatarios()" class="w-4 h-4 mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-xs">Clubes que compiten</span>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-black rounded-full tabular-nums">${totalUsuariosClubes} usuarios (${totalClubes} clubes)</span>
                            </div>
                            <span class="text-[11px] text-slate-600 block mt-0.5">
                                A todos los usuarios activos que representen a los clubes participantes en esta competición.
                            </span>
                            <div class="mt-1 text-[10px] text-slate-400 font-semibold truncate">
                                Clubes: <span class="text-slate-600">${clubesNombres}</span>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Grupo 3: Jueces que han participado (DESHABILITADO POR DEFECTO) -->
                <div class="p-3.5 bg-purple-50/40 rounded-2xl border border-purple-200/80 transition-all hover:bg-purple-50/70">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" id="chk_dest_jueces" onchange="actualizarContadorDestinatarios()" class="w-4 h-4 mt-0.5 rounded border-slate-300 text-purple-600 focus:ring-purple-500 cursor-pointer">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-xs">Jueces que han participado</span>
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-black rounded-full tabular-nums">${totalJueces} con email</span>
                                <span class="text-[9px] text-slate-400 italic">(Desmarcado por defecto)</span>
                            </div>
                            <span class="text-[11px] text-slate-600 block mt-0.5">
                                Jueces y controladores técnicos asignados a paneles u oficiales en esta competición.
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Acordeón Desplegable para transparencia -->
                <details class="bg-white rounded-xl border border-slate-200 text-[11px]">
                    <summary class="px-3 py-2 font-bold text-slate-600 cursor-pointer hover:bg-slate-50 rounded-xl select-none flex items-center justify-between">
                        <span><i class="fas fa-address-book text-slate-400 mr-1.5"></i> Ver desglose de emails destinatarios</span>
                        <i class="fas fa-chevron-down text-[9px] text-slate-400"></i>
                    </summary>
                    <div class="p-3 border-t border-slate-100 space-y-2 max-h-40 overflow-y-auto">
                        ${emailsClubesHtml ? `<div><span class="font-bold text-blue-700 text-[10px] uppercase block mb-1">Clubes:</span><ul class="space-y-0.5">${emailsClubesHtml}</ul></div>` : ''}
                        ${emailsJuecesHtml ? `<div><span class="font-bold text-purple-700 text-[10px] uppercase block mb-1">Jueces:</span><ul class="space-y-0.5">${emailsJuecesHtml}</ul></div>` : ''}
                    </div>
                </details>

                <!-- Resumen de Envío Pulse -->
                <div class="p-2.5 bg-slate-100/80 rounded-xl flex items-center justify-between text-[11px] text-slate-500 font-medium">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-paper-plane text-blue-600"></i> Remitente: <strong class="text-slate-700">sincrm@pedrodiaz.eu</strong> (Servidor Pulse)
                    </span>
                    <span id="lbl_dest_counter" class="font-black text-slate-800 tabular-nums"></span>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-cloud-arrow-up mr-1.5"></i> ' + accionTexto,
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-3xl p-6 sm:p-7 max-w-lg',
            confirmButton: 'rounded-xl text-xs font-black uppercase tracking-wider px-5 py-3 shadow-md active:scale-[0.98] transition-all',
            cancelButton: 'rounded-xl text-xs font-bold px-4 py-3 active:scale-[0.98] transition-all'
        },
        didOpen: () => {
            window._modalPublicarData = data;
            actualizarContadorDestinatarios();
        },
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const destPrueba = document.getElementById('chk_dest_prueba').checked ? '1' : '0';
            const destClubes = document.getElementById('chk_dest_clubes').checked ? '1' : '0';
            const destJueces = document.getElementById('chk_dest_jueces').checked ? '1' : '0';

            const formData = new FormData();
            formData.append('action', 'publicar');
            formData.append('ajax', '1');
            formData.append('id_competicion', idComp);
            formData.append('tipo', tipo);
            formData.append('dest_prueba', destPrueba);
            formData.append('dest_clubes', destClubes);
            formData.append('dest_jueces', destJueces);

            return fetch('informes_publicar_code.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en el servidor HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Fallo desconocido al publicar.');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Fallo en la publicación: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const info = result.value;
            const emailsCount = info.emails_enviados ? info.emails_enviados.length : 0;
            const listaEmails = info.emails_enviados ? info.emails_enviados.join('<br>') : '';

            Swal.fire({
                title: '¡Publicación Completada!',
                html: `
                    <div class="text-left font-sans text-xs space-y-3 pt-2">
                        <div class="p-3 bg-emerald-50 text-emerald-800 rounded-2xl border border-emerald-200">
                            <strong>${info.message}</strong>
                        </div>
                        <p class="text-slate-600">
                            Documento disponible en la web: <br>
                            <a href="${info.file}?v=${Date.now()}" target="_blank" class="text-blue-600 font-mono font-bold underline">
                                ${info.filename} (${info.file_size})
                            </a>
                        </p>
                        ${emailsCount > 0 ? `
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px]">
                                <span class="font-bold text-slate-700 block mb-1">
                                    <i class="fas fa-check-double text-emerald-600 mr-1"></i> Remitido por email a ${emailsCount} destinatario(s):
                                </span>
                                <div class="max-h-24 overflow-y-auto text-slate-500 font-mono space-y-0.5">
                                    ${listaEmails}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Entendido'
            }).then(() => {
                window.location.reload();
            });
        }
    });
}

function actualizarContadorDestinatarios() {
    const data = window._modalPublicarData;
    if (!data) return;

    const chkPrueba = document.getElementById('chk_dest_prueba');
    const chkClubes = document.getElementById('chk_dest_clubes');
    const chkJueces = document.getElementById('chk_dest_jueces');
    const lbl = document.getElementById('lbl_dest_counter');
    if (!lbl) return;

    let emailsSet = new Set();

    if (chkPrueba && chkPrueba.checked && data.prueba && data.prueba.email) {
        emailsSet.add(data.prueba.email.toLowerCase());
    }

    if (chkClubes && chkClubes.checked && data.clubes && data.clubes.destinatarios) {
        data.clubes.destinatarios.forEach(d => {
            if (d.email) emailsSet.add(d.email.toLowerCase());
        });
    }

    if (chkJueces && chkJueces.checked && data.jueces && data.jueces.destinatarios) {
        data.jueces.destinatarios.forEach(j => {
            if (j.email) emailsSet.add(j.email.toLowerCase());
        });
    }

    lbl.innerHTML = `Destinatarios: <strong>${emailsSet.size}</strong>`;
}

// Para usar desde cualquier sitio pasando directamente tipo y título
function abrirModalPublicarWeb(tipo, tituloDoc) {
    abrirModalPublicar(tipo, tituloDoc, false);
}
