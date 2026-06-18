@extends('layouts.app')

@push('styles')
<style>
.modal-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 100%;
    padding: 0.4rem 1.75rem 0.4rem 0.6rem;
    font-size: 0.8rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    outline: none;
    box-sizing: border-box;
    background-color: #fff;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%239ca3af' stroke-width='2.5' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    background-size: 0.7rem;
    cursor: pointer;
}
</style>
@endpush

@section('content')
<div x-data="marketingPrograms()" x-init="init()" class="max-w-full px-4 sm:px-6 md:px-8">

    {{-- Header --}}
    <div style="margin-bottom:1.5rem;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Oferta Académica</h1>
            <p class="mt-1 text-sm text-gray-600">Gestión y seguimiento de programas en oferta para marketing.</p>
        </div>
        <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
            <form method="GET" action="{{ route('marketing-programs.index') }}" style="display:flex;align-items:center;gap:0.5rem;">
                <label style="font-size:0.875rem;font-weight:500;color:#374151;">Gestión</label>
                <input name="gestion" type="number" min="2000" max="2100" value="{{ $gestion }}"
                       style="width:5rem;padding:0.375rem 0.75rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.875rem;outline:none;"
                       onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                <button type="submit" style="padding:0.375rem 1rem;background:#4f46e5;color:#fff;border:none;border-radius:0.375rem;font-size:0.875rem;font-weight:500;cursor:pointer;"
                        onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">Aplicar</button>
            </form>
            @can('marketing_program.edit')
            <button @click="openCreate()"
                    style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.375rem 1rem;background:#16a34a;color:#fff;border:none;border-radius:0.375rem;font-size:0.875rem;font-weight:500;cursor:pointer;"
                    onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Programa
            </button>
            @endcan
        </div>
    </div>

    {{-- Tabla --}}
    <div style="overflow-x:auto;border-radius:0.5rem;border:1px solid #e5e7eb;">
        <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:0.8rem;">
            <thead>
                <tr>
                    <th style="position:sticky;left:0;z-index:20;background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:center;white-space:nowrap;min-width:70px;border-right:1px solid #374151;border-bottom:2px solid #374151;">ACCIONES</th>
                    <th style="position:sticky;left:70px;z-index:20;background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:nowrap;min-width:130px;border-right:1px solid #374151;border-bottom:2px solid #374151;">ESTADO</th>
                    <th style="position:sticky;left:200px;z-index:20;background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:normal;word-break:break-word;min-width:200px;border-right:1px solid #374151;border-bottom:2px solid #374151;">NOMBRE DEL PROGRAMA</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:nowrap;min-width:130px;border-bottom:2px solid #374151;">CERTIFICACIÓN</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:normal;word-break:break-word;min-width:140px;border-bottom:2px solid #374151;">EQUIPO RESPONSABLE</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:nowrap;min-width:140px;border-bottom:2px solid #374151;">DÍAS DE CLASES</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:nowrap;min-width:80px;border-bottom:2px solid #374151;">ÁREA</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:center;white-space:normal;word-break:break-word;min-width:100px;border-bottom:2px solid #374151;">PLANTEL DOCENTE</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:normal;word-break:break-word;min-width:120px;border-bottom:2px solid #374151;">LANZAMIENTO DE CAMPAÑAS</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:normal;word-break:break-word;min-width:130px;border-bottom:2px solid #374151;">WEBINAR / MASTERCLASS</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:normal;word-break:break-word;min-width:120px;border-bottom:2px solid #374151;">INICIO INAUGURACIÓN</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:nowrap;min-width:100px;border-bottom:2px solid #374151;">MÓDULO 0</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:left;white-space:nowrap;min-width:100px;border-bottom:2px solid #374151;">MÓDULO 1</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:center;white-space:normal;word-break:break-word;min-width:80px;border-bottom:2px solid #374151;">TOTAL INSCRITOS</th>
                    <th style="background:#1f2937;color:#fff;padding:0.6rem 0.75rem;text-align:center;white-space:normal;word-break:break-word;min-width:90px;border-bottom:2px solid #374151;">INSCRITOS COMPLETOS</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="programs.length === 0">
                    <tr>
                        <td colspan="15" style="text-align:center;padding:2rem;color:#6b7280;font-style:italic;">
                            No hay programas registrados para esta gestión.
                        </td>
                    </tr>
                </template>
                <template x-for="prog in programs" :key="prog.id">
                    <tr :style="rowStyle(prog.status)" style="border-bottom:1px solid #e5e7eb;">
                        {{-- Acciones --}}
                        <td :style="stickyCell(prog.status, 0, 0)" style="padding:0.5rem 0.75rem;border-right:1px solid #e5e7eb;text-align:center;">
                            @can('marketing_program.edit')
                            <div style="display:inline-flex;align-items:center;gap:0.25rem;">
                                <button @click="openEdit(prog)" title="Editar" style="color:#6b7280;background:none;border:none;cursor:pointer;padding:0.125rem;">
                                    <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="confirmDelete(prog)" title="Eliminar" style="color:#ef4444;background:none;border:none;cursor:pointer;padding:0.125rem;">
                                    <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                            @endcan
                        </td>
                        {{-- Estado --}}
                        <td :style="stickyCell(prog.status, 1, 70)" style="padding:0.5rem 0.75rem;border-right:1px solid #e5e7eb;">
                            <span :style="statusBadge(prog.status)" style="display:inline-block;padding:0.2rem 0.5rem;border-radius:9999px;font-size:0.7rem;font-weight:600;white-space:nowrap;" x-text="statusLabel(prog.status)"></span>
                        </td>
                        {{-- Nombre --}}
                        <td :style="stickyCell(prog.status, 1, 200)" style="padding:0.5rem 0.75rem;font-weight:600;border-right:1px solid #e5e7eb;" x-text="prog.name"></td>
                        {{-- Certificación --}}
                        <td style="padding:0.5rem 0.75rem;" x-text="prog.certification || '—'"></td>
                        {{-- Equipo --}}
                        <td style="padding:0.5rem 0.75rem;" x-text="prog.marketing_team_name || '—'"></td>
                        {{-- Días --}}
                        <td style="padding:0.5rem 0.75rem;">
                            <span x-text="prog.class_days && prog.class_days.length ? prog.class_days.map(d => capitalize(d)).join(', ') : '—'"></span>
                        </td>
                        {{-- Área --}}
                        <td style="padding:0.5rem 0.75rem;" x-text="prog.area || '—'"></td>
                        {{-- Plantel docente --}}
                        <td style="padding:0.5rem 0.75rem;text-align:center;">
                            <template x-if="prog.has_teacher_panel && prog.teacher_panel_drive_id">
                                <button @click="openFilePreview(prog.teacher_panel_drive_id, prog.name)"
                                        style="display:inline-flex;align-items:center;gap:0.25rem;color:#16a34a;font-size:0.75rem;background:none;border:none;cursor:pointer;padding:0;">
                                    <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Ver
                                </button>
                            </template>
                            <template x-if="prog.has_teacher_panel && !prog.teacher_panel_drive_link">
                                <span style="color:#16a34a;font-weight:600;">✓</span>
                            </template>
                            <template x-if="!prog.has_teacher_panel">
                                <span style="color:#9ca3af;">—</span>
                            </template>
                        </td>
                        {{-- Lanzamiento campañas --}}
                        <td style="padding:0.5rem 0.75rem;" x-text="fmtDate(prog.campaign_launch_date)"></td>
                        {{-- Webinars --}}
                        <td style="padding:0.5rem 0.75rem;">
                            <template x-if="prog.webinars && prog.webinars.length">
                                <div style="display:flex;flex-direction:column;gap:0.2rem;">
                                    <template x-for="w in prog.webinars" :key="w.id">
                                        <div style="display:flex;align-items:center;gap:0.25rem;font-size:0.75rem;">
                                            <span x-text="fmtDate(w.date)"></span>
                                            <template x-if="w.link">
                                                <a :href="w.link" target="_blank" style="color:#4f46e5;" title="Enlace webinar">
                                                    <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </a>
                                            </template>
                                            <template x-if="w.drive_file_link">
                                                <a :href="w.drive_file_link" target="_blank" style="color:#16a34a;" title="Archivo">
                                                    <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!prog.webinars || !prog.webinars.length">
                                <span style="color:#9ca3af;">—</span>
                            </template>
                        </td>
                        {{-- Inauguración --}}
                        <td style="padding:0.5rem 0.75rem;" x-text="fmtDate(prog.inauguration_date)"></td>
                        {{-- Módulo 0 --}}
                        <td style="padding:0.5rem 0.75rem;" x-text="fmtDate(prog.module_0_date)"></td>
                        {{-- Módulo 1 --}}
                        <td style="padding:0.5rem 0.75rem;" x-text="fmtDate(prog.module_1_date)"></td>
                        {{-- Total inscritos --}}
                        <td style="padding:0.5rem 0.75rem;text-align:center;font-weight:600;" x-text="prog.total_inscritos"></td>
                        {{-- Inscritos completos --}}
                        <td style="padding:0.5rem 0.75rem;text-align:center;font-weight:600;" x-text="prog.inscritos_completos"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- ── MODAL CREAR / EDITAR ─────────────────────────────────────────── --}}
    <div x-show="modal.open" x-transition.opacity style="display:none;position:fixed;inset:0;z-index:50;overflow-y:auto;background:rgba(0,0,0,0.5);">
        <div style="min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:2rem 1rem;">
            <div @click.outside="modal.open = false"
                 style="background:#fff;border-radius:0.75rem;width:100%;max-width:700px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);">

                {{-- Header modal --}}
                <div style="background:#1f2937;padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
                    <h2 style="font-size:1rem;font-weight:700;color:#fff;" x-text="modal.editId ? 'Editar Programa' : 'Nuevo Programa'"></h2>
                    <button @click="modal.open = false" style="color:#9ca3af;background:none;border:none;cursor:pointer;">
                        <svg style="width:1.25rem;height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">

                    {{-- Estado + Nombre --}}
                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Estado *</label>
                            <select x-model="modal.status" class="modal-select"
                                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                                <option value="en_oferta">En Oferta</option>
                                <option value="en_desarrollo">En Desarrollo</option>
                                <option value="en_conflicto">En Conflicto</option>
                                <option value="pendiente_lanzamiento">Pendiente de Lanzamiento</option>
                                <option value="grupo_cerrado">Grupo Cerrado</option>
                                <option value="sin_exito">Programa sin Éxito</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Nombre del Programa *</label>
                            <input x-model="modal.name" type="text" placeholder="Nombre del programa"
                                   style="width:100%;padding:0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                    </div>

                    {{-- Certificación + Programa base --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Certificación</label>
                            <input x-model="modal.certification" type="text" placeholder="Ej: UNSXX, UPI…"
                                   style="width:100%;padding:0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div style="min-width:0;"
                             x-data="{
                                open: false,
                                search: '',
                                get filtered() {
                                    if (!this.search.trim()) return programsList;
                                    const q = this.search.toLowerCase();
                                    return programsList.filter(p => p.name.toLowerCase().includes(q));
                                },
                                get selectedLabel() {
                                    if (!modal.program_id) return '— Sin vincular —';
                                    const p = programsList.find(p => p.id == modal.program_id);
                                    return p ? p.name : '— Sin vincular —';
                                },
                                select(id) {
                                    modal.program_id = id;
                                    this.open = false;
                                    this.search = '';
                                },
                                openDropdown() {
                                    this.open = true;
                                    this.search = '';
                                    this.$nextTick(() => this.$refs.searchInput.focus());
                                }
                             }">
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Programa</label>
                            <div style="position:relative;">
                                {{-- Trigger --}}
                                <button type="button" @click="openDropdown()"
                                        style="appearance:none;width:100%;padding:0.4rem 1.75rem 0.4rem 0.6rem;font-size:0.8rem;border:1px solid #d1d5db;border-radius:0.375rem;outline:none;background:#fff;text-align:left;cursor:pointer;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;box-sizing:border-box;"
                                        x-text="selectedLabel"></button>
                                <svg style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);width:0.7rem;height:0.7rem;color:#9ca3af;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                                {{-- Dropdown --}}
                                <div x-show="open" @click.outside="open = false"
                                     style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;background:#fff;border:1px solid #d1d5db;border-radius:0.375rem;box-shadow:0 4px 16px rgba(0,0,0,0.12);margin-top:2px;">
                                    <div style="padding:0.4rem;">
                                        <input x-ref="searchInput" x-model="search" type="text" placeholder="Buscar programa…"
                                               style="width:100%;padding:0.35rem 0.5rem;border:1px solid #d1d5db;border-radius:0.25rem;font-size:0.78rem;outline:none;box-sizing:border-box;"
                                               onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                                    </div>
                                    <ul style="max-height:200px;overflow-y:auto;margin:0;padding:0 0 0.25rem 0;list-style:none;">
                                        <li @click="select('')"
                                            style="padding:0.4rem 0.75rem;font-size:0.78rem;cursor:pointer;color:#6b7280;"
                                            onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                                            — Sin vincular —
                                        </li>
                                        <template x-for="p in filtered" :key="p.id">
                                            <li @click="select(p.id)"
                                                :style="modal.program_id == p.id ? 'background:#eff6ff;font-weight:600;' : ''"
                                                style="padding:0.4rem 0.75rem;font-size:0.78rem;cursor:pointer;"
                                                onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background= modal.program_id == this.__x_for_key ? '#eff6ff' : 'transparent'"
                                                x-text="p.name"></li>
                                        </template>
                                        <template x-if="filtered.length === 0">
                                            <li style="padding:0.4rem 0.75rem;font-size:0.78rem;color:#9ca3af;font-style:italic;">Sin resultados.</li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Equipo + Días --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Equipo Responsable</label>
                            <select x-model="modal.marketing_team_id" class="modal-select"
                                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                                <option value="">— Sin asignar —</option>
                                @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Días de Clases</label>
                            <div style="display:flex;flex-wrap:wrap;gap:0.4rem;">
                                <template x-for="day in allDays" :key="day.val">
                                    <label style="display:inline-flex;align-items:center;gap:0.25rem;cursor:pointer;font-size:0.75rem;">
                                        <input type="checkbox" :value="day.val" x-model="modal.class_days" style="cursor:pointer;">
                                        <span x-text="day.label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Fechas fila 1 --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Lanzamiento de Campañas</label>
                            <input x-model="modal.campaign_launch_date" type="date"
                                   style="width:100%;padding:0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Inicio Inauguración</label>
                            <input x-model="modal.inauguration_date" type="date"
                                   style="width:100%;padding:0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                    </div>

                    {{-- Fechas fila 2 --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Módulo 0</label>
                            <input x-model="modal.module_0_date" type="date"
                                   style="width:100%;padding:0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.25rem;">Módulo 1</label>
                            <input x-model="modal.module_1_date" type="date"
                                   style="width:100%;padding:0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                    </div>

                    {{-- Plantel docente --}}
                    <div style="border:1px solid #e5e7eb;border-radius:0.5rem;padding:0.75rem;">
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.5rem;">Plantel Docente</label>
                        <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                            <div style="display:flex;align-items:center;gap:0.4rem;">
                                <span style="font-size:0.8rem;color:#6b7280;">¿Disponible?</span>
                                <template x-if="modal.has_teacher_panel">
                                    <span style="color:#16a34a;font-weight:700;font-size:0.85rem;">Sí</span>
                                </template>
                                <template x-if="!modal.has_teacher_panel">
                                    <span style="color:#9ca3af;font-size:0.85rem;">No</span>
                                </template>
                            </div>
                            <template x-if="modal.editId">
                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <label style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.35rem 0.75rem;background:#f3f4f6;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.75rem;cursor:pointer;">
                                        <svg style="width:0.875rem;height:0.875rem;color:#4f46e5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        <span x-text="modal.has_teacher_panel ? 'Reemplazar archivo' : 'Subir archivo'"></span>
                                        <input type="file" style="display:none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                               @change="uploadTeacherPanel($event)">
                                    </label>
                                    <template x-if="modal.has_teacher_panel && modal.teacher_panel_drive_id">
                                        <button @click="openFilePreview(modal.teacher_panel_drive_id, modal.name)"
                                                style="font-size:0.75rem;color:#4f46e5;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;">Ver archivo</button>
                                    </template>
                                    <template x-if="modal.has_teacher_panel">
                                        <button @click="deleteTeacherPanel()" style="font-size:0.75rem;color:#ef4444;background:none;border:none;cursor:pointer;">Eliminar</button>
                                    </template>
                                    <span x-show="modal.uploadingPanel" style="font-size:0.75rem;color:#6b7280;">Subiendo…</span>
                                </div>
                            </template>
                            <template x-if="!modal.editId">
                                <span style="font-size:0.75rem;color:#9ca3af;font-style:italic;">Guarda primero para subir el archivo.</span>
                            </template>
                        </div>
                    </div>

                    {{-- Webinars --}}
                    <div x-show="modal.editId" style="border:1px solid #e5e7eb;border-radius:0.5rem;padding:0.75rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                            <label style="font-size:0.8rem;font-weight:600;color:#374151;">Webinars / Masterclasses</label>
                            <button @click="addWebinar()"
                                    style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.75rem;color:#4f46e5;background:none;border:none;cursor:pointer;">
                                <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Añadir
                            </button>
                        </div>
                        <template x-if="!modal.webinars || modal.webinars.length === 0">
                            <p style="font-size:0.75rem;color:#9ca3af;font-style:italic;">Sin webinars registrados.</p>
                        </template>
                        <template x-for="(w, idx) in modal.webinars" :key="w.id || ('new_' + idx)">
                            <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:0.5rem;padding:0.5rem 0;border-bottom:1px solid #f3f4f6;">
                                <div>
                                    <label style="font-size:0.7rem;color:#6b7280;">Etiqueta</label>
                                    <input x-model="w.label" type="text" placeholder="Webinar 1"
                                           style="width:100px;padding:0.3rem 0.5rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.75rem;outline:none;"
                                           onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                                </div>
                                <div>
                                    <label style="font-size:0.7rem;color:#6b7280;">Fecha</label>
                                    <input x-model="w.date" type="date"
                                           style="padding:0.3rem 0.5rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.75rem;outline:none;"
                                           onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                                </div>
                                <div>
                                    <label style="font-size:0.7rem;color:#6b7280;">Enlace</label>
                                    <input x-model="w.link" type="url" placeholder="https://…"
                                           style="width:160px;padding:0.3rem 0.5rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.75rem;outline:none;"
                                           onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                                </div>
                                <div style="display:flex;align-items:flex-end;gap:0.375rem;">
                                    <template x-if="w.id">
                                        <label style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.3rem 0.5rem;background:#f3f4f6;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.7rem;cursor:pointer;">
                                            <svg style="width:0.75rem;height:0.75rem;color:#4f46e5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            <span x-text="w.drive_file_id ? 'Reemplazar' : 'Archivo'"></span>
                                            <input type="file" style="display:none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                   @change="uploadWebinarFile($event, w)">
                                        </label>
                                    </template>
                                    <template x-if="w.id && w.drive_file_link">
                                        <a :href="w.drive_file_link" target="_blank" style="font-size:0.7rem;color:#4f46e5;text-decoration:none;padding-bottom:0.3rem;">Ver</a>
                                    </template>
                                    <template x-if="w.id">
                                        <button @click="saveWebinar(w)" style="padding:0.3rem 0.5rem;background:#4f46e5;color:#fff;border:none;border-radius:0.375rem;font-size:0.7rem;cursor:pointer;">Guardar</button>
                                    </template>
                                    <template x-if="!w.id">
                                        <button @click="createWebinar(w, idx)" style="padding:0.3rem 0.5rem;background:#16a34a;color:#fff;border:none;border-radius:0.375rem;font-size:0.7rem;cursor:pointer;">Crear</button>
                                    </template>
                                    <button @click="removeWebinar(w, idx)" style="padding:0.3rem 0.5rem;background:none;border:none;color:#ef4444;cursor:pointer;">
                                        <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>

                {{-- Footer --}}
                <div style="padding:1rem 1.5rem;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:0.5rem;">
                    <button @click="modal.open = false"
                            style="padding:0.5rem 1.25rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.875rem;background:#fff;cursor:pointer;">
                        Cerrar
                    </button>
                    <button @click="saveProgram()" :disabled="modal.saving"
                            style="padding:0.5rem 1.25rem;background:#4f46e5;color:#fff;border:none;border-radius:0.375rem;font-size:0.875rem;font-weight:500;cursor:pointer;"
                            x-text="modal.saving ? 'Guardando…' : (modal.editId ? 'Actualizar' : 'Crear')">
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Previsualización Archivo --}}
    <div x-show="filePreview.open" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;overflow-y:auto;background:rgba(0,0,0,0.65);">
        <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;" @click.self="filePreview.open = false">
            <div style="display:flex;flex-direction:column;width:100%;max-width:800px;max-height:80vh;border-radius:0.75rem;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.5);">
                {{-- Header --}}
                <div style="background:#1f2937;padding:0.6rem 1rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                    <div style="display:flex;align-items:center;gap:0.5rem;min-width:0;">
                        <svg style="width:0.9rem;height:0.9rem;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span style="color:#e5e7eb;font-size:0.8rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="filePreview.label"></span>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.4rem;flex-shrink:0;">
                        <a :href="'/marketing-programs/file-preview?id=' + filePreview.driveId + '&download=1'"
                           style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.3rem 0.65rem;background:#4f46e5;color:#fff;border-radius:0.375rem;font-size:0.75rem;font-weight:500;text-decoration:none;">
                            <svg style="width:0.8rem;height:0.8rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Descargar
                        </a>
                        <button @click="filePreview.open = false" style="color:#9ca3af;background:none;border:none;cursor:pointer;padding:0.2rem;line-height:0;">
                            <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                {{-- Contenido --}}
                <div style="background:#f3f4f6;flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;min-height:0;">
                    <template x-if="filePreview.isImage">
                        <img :src="filePreview.driveId ? '/marketing-programs/file-preview?id=' + filePreview.driveId : ''"
                             style="max-width:100%;max-height:100%;object-fit:contain;display:block;">
                    </template>
                    <template x-if="!filePreview.isImage">
                        <iframe :src="filePreview.driveId ? '/marketing-programs/file-preview?id=' + filePreview.driveId : ''"
                                style="width:100%;height:100%;border:none;min-height:60vh;"
                                allow="autoplay"></iframe>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-transition.opacity
         :style="'position:fixed;bottom:1.5rem;right:1.5rem;z-index:100;padding:0.75rem 1.25rem;border-radius:0.5rem;font-size:0.875rem;font-weight:500;color:#fff;pointer-events:none;' + (toast.type === 'error' ? 'background:#ef4444;' : 'background:#16a34a;')"
         style="display:none;" x-text="toast.msg"></div>

</div>

<script>
function marketingPrograms() {
    return {
        programs: @json($programs),
        programsList: @json($programs_list->map(fn($p) => ['id' => $p->id, 'name' => $p->name])),
        gestion:  {{ $gestion }},
        allDays: [
            {val:'lunes',label:'Lun'},{val:'martes',label:'Mar'},{val:'miércoles',label:'Mié'},
            {val:'jueves',label:'Jue'},{val:'viernes',label:'Vie'},{val:'sábado',label:'Sáb'},{val:'domingo',label:'Dom'},
        ],
        modal: { open:false, editId:null, saving:false, uploadingPanel:false,
                 status:'en_oferta', name:'', certification:'', program_id:'', marketing_team_id:'',
                 class_days:[], has_teacher_panel:false, teacher_panel_drive_id:null, teacher_panel_drive_link:null,
                 campaign_launch_date:'', inauguration_date:'', module_0_date:'', module_1_date:'',
                 webinars:[] },
        toast: { show:false, msg:'', type:'success' },
        filePreview: { open:false, driveId:null, label:'', isImage:false },

        init() {},

        async openFilePreview(driveId, label) {
            this.filePreview = { open:true, driveId, label: label || 'Plantel Docente', isImage:false };
            try {
                const res = await fetch(`/marketing-programs/file-preview?id=${driveId}&info=1`, { headers:{ 'Accept':'application/json' } });
                const data = await res.json();
                this.filePreview.isImage = (data.mimeType || '').startsWith('image/');
            } catch(e) {}
        },

        // ── Status helpers ──
        statusLabel(s) {
            return {en_oferta:'En Oferta',en_desarrollo:'En Desarrollo',en_conflicto:'En Conflicto',
                    pendiente_lanzamiento:'Pendiente de Lanzamiento',grupo_cerrado:'Grupo Cerrado',sin_exito:'Sin Éxito'}[s] ?? s;
        },
        statusBadge(s) {
            const map = {
                en_oferta:'background:#dcfce7;color:#15803d;',
                en_desarrollo:'background:#dbeafe;color:#1d4ed8;',
                en_conflicto:'background:#fee2e2;color:#b91c1c;',
                pendiente_lanzamiento:'background:#fef9c3;color:#854d0e;',
                grupo_cerrado:'background:#f3f4f6;color:#374151;',
                sin_exito:'background:#f1f5f9;color:#94a3b8;',
            };
            return map[s] ?? '';
        },
        rowStyle(s) {
            const map = {
                en_oferta:'background:#f0fdf4;',
                en_desarrollo:'background:#eff6ff;',
                en_conflicto:'background:#fef2f2;',
                pendiente_lanzamiento:'background:#fefce8;',
                grupo_cerrado:'background:#f9fafb;',
                sin_exito:'background:#f8fafc;',
            };
            return map[s] ?? '';
        },
        stickyCell(status, col, leftBase) {
            const bgMap = {
                en_oferta:'#f0fdf4', en_desarrollo:'#eff6ff', en_conflicto:'#fef2f2',
                pendiente_lanzamiento:'#fefce8', grupo_cerrado:'#f9fafb', sin_exito:'#f8fafc',
            };
            const bg = bgMap[status] ?? '#fff';
            const left = col === 0 ? '0px' : leftBase + 'px';
            return `position:sticky;left:${left};z-index:10;background:${bg};`;
        },

        // ── Dates ──
        fmtDate(d) {
            if (!d) return '—';
            const [y,m,day] = d.split('-');
            return `${day}-${m}-${y}`;
        },
        capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); },

        // ── Modal ──
        openCreate() {
            Object.assign(this.modal, {
                open:true, editId:null, saving:false, uploadingPanel:false,
                status:'en_oferta', name:'', certification:'', program_id:'', marketing_team_id:'',
                class_days:[], has_teacher_panel:false, teacher_panel_drive_id:null, teacher_panel_drive_link:null,
                campaign_launch_date:'', inauguration_date:'', module_0_date:'', module_1_date:'',
                webinars:[],
            });
        },
        openEdit(prog) {
            Object.assign(this.modal, {
                open:true, editId:prog.id, saving:false, uploadingPanel:false,
                status:prog.status, name:prog.name, certification:prog.certification || '',
                program_id:prog.program_id || '', marketing_team_id:prog.marketing_team_id || '',
                class_days:[...(prog.class_days || [])],
                has_teacher_panel:prog.has_teacher_panel,
                teacher_panel_drive_id:prog.teacher_panel_drive_id,
                teacher_panel_drive_link:prog.teacher_panel_drive_link,
                campaign_launch_date:prog.campaign_launch_date || '',
                inauguration_date:prog.inauguration_date || '',
                module_0_date:prog.module_0_date || '',
                module_1_date:prog.module_1_date || '',
                webinars: prog.webinars.map(w => ({...w})),
            });
        },

        async saveProgram() {
            if (!this.modal.name.trim()) { this.showToast('El nombre es requerido.','error'); return; }
            this.modal.saving = true;
            const body = {
                status:this.modal.status, name:this.modal.name, certification:this.modal.certification,
                program_id:this.modal.program_id || null, marketing_team_id:this.modal.marketing_team_id || null,
                class_days:this.modal.class_days,
                campaign_launch_date:this.modal.campaign_launch_date || null,
                inauguration_date:this.modal.inauguration_date || null,
                module_0_date:this.modal.module_0_date || null,
                module_1_date:this.modal.module_1_date || null,
                gestion:this.gestion,
            };
            try {
                let url, method;
                if (this.modal.editId) {
                    url = `/marketing-programs/${this.modal.editId}`;
                    method = 'PATCH';
                } else {
                    url = '/marketing-programs';
                    method = 'POST';
                }
                const res = await this.req(url, method, body);
                if (res.success) {
                    if (this.modal.editId) {
                        const idx = this.programs.findIndex(p => p.id === this.modal.editId);
                        if (idx > -1) this.programs[idx] = res.program;
                    } else {
                        this.programs.push(res.program);
                        this.modal.editId = res.program.id;
                    }
                    this.showToast('Guardado correctamente.');
                }
            } catch(e) { this.showToast('Error al guardar.','error'); }
            finally { this.modal.saving = false; }
        },

        confirmDelete(prog) {
            if (!confirm(`¿Eliminar "${prog.name}"?`)) return;
            this.req(`/marketing-programs/${prog.id}`, 'DELETE').then(r => {
                if (r.success) {
                    this.programs = this.programs.filter(p => p.id !== prog.id);
                    this.showToast('Programa eliminado.');
                }
            });
        },

        // ── Plantel docente ──
        async uploadTeacherPanel(event) {
            const file = event.target.files[0];
            if (!file || !this.modal.editId) return;
            this.modal.uploadingPanel = true;
            const fd = new FormData();
            fd.append('file', file);
            fd.append('_method', 'POST');
            try {
                const res = await fetch(`/marketing-programs/${this.modal.editId}/teacher-panel`, {
                    method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: fd,
                });
                const data = await res.json();
                if (data.success) {
                    this.modal.has_teacher_panel = true;
                    this.modal.teacher_panel_drive_id   = data.drive_id;
                    this.modal.teacher_panel_drive_link = data.drive_link;
                    const idx = this.programs.findIndex(p => p.id === this.modal.editId);
                    if (idx > -1) {
                        this.programs[idx].has_teacher_panel       = true;
                        this.programs[idx].teacher_panel_drive_id  = data.drive_id;
                        this.programs[idx].teacher_panel_drive_link= data.drive_link;
                    }
                    this.showToast('Archivo subido a Drive.');
                }
            } catch(e) { this.showToast('Error al subir archivo.','error'); }
            finally { this.modal.uploadingPanel = false; }
        },
        async deleteTeacherPanel() {
            if (!confirm('¿Eliminar el archivo de plantel docente?')) return;
            const res = await this.req(`/marketing-programs/${this.modal.editId}/teacher-panel`, 'DELETE');
            if (res.success) {
                this.modal.has_teacher_panel = false;
                this.modal.teacher_panel_drive_id = null;
                this.modal.teacher_panel_drive_link = null;
                const idx = this.programs.findIndex(p => p.id === this.modal.editId);
                if (idx > -1) { this.programs[idx].has_teacher_panel = false; }
                this.showToast('Archivo eliminado.');
            }
        },

        // ── Webinars ──
        addWebinar() {
            this.modal.webinars.push({ id:null, label:'', date:'', link:'', drive_file_id:null, drive_file_link:null });
        },
        async createWebinar(w, idx) {
            const res = await this.req(`/marketing-programs/${this.modal.editId}/webinars`, 'POST',
                { label:w.label, date:w.date||null, link:w.link||null });
            if (res.success) {
                this.modal.webinars[idx] = res.webinar;
                const pi = this.programs.findIndex(p => p.id === this.modal.editId);
                if (pi > -1) this.programs[pi].webinars.push(res.webinar);
                this.showToast('Webinar creado.');
            }
        },
        async saveWebinar(w) {
            const res = await this.req(`/marketing-programs/webinars/${w.id}`, 'POST',
                { _method:'PATCH', label:w.label, date:w.date||null, link:w.link||null });
            if (res.success) {
                const pi = this.programs.findIndex(p => p.id === this.modal.editId);
                if (pi > -1) {
                    const wi = this.programs[pi].webinars.findIndex(x => x.id === w.id);
                    if (wi > -1) this.programs[pi].webinars[wi] = res.webinar;
                }
                this.showToast('Webinar actualizado.');
            }
        },
        async removeWebinar(w, idx) {
            if (w.id) {
                if (!confirm('¿Eliminar este webinar?')) return;
                const res = await this.req(`/marketing-programs/webinars/${w.id}`, 'DELETE');
                if (!res.success) return;
                const pi = this.programs.findIndex(p => p.id === this.modal.editId);
                if (pi > -1) this.programs[pi].webinars = this.programs[pi].webinars.filter(x => x.id !== w.id);
            }
            this.modal.webinars.splice(idx, 1);
        },
        async uploadWebinarFile(event, w) {
            const file = event.target.files[0];
            if (!file || !w.id) return;
            const fd = new FormData();
            fd.append('file', file);
            fd.append('label', w.label || '');
            fd.append('date', w.date || '');
            fd.append('link', w.link || '');
            fd.append('_method', 'PATCH');
            try {
                const res = await fetch(`/marketing-programs/webinars/${w.id}`, {
                    method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: fd,
                });
                const data = await res.json();
                if (data.success) {
                    w.drive_file_id   = data.webinar.drive_file_id;
                    w.drive_file_link = data.webinar.drive_file_link;
                    this.showToast('Archivo subido a Drive.');
                }
            } catch(e) { this.showToast('Error al subir archivo.','error'); }
        },

        // ── Helpers ──
        async req(url, method, body = null) {
            const opts = {
                method, headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json' },
            };
            if (body) opts.body = JSON.stringify(body);
            const res = await fetch(url, opts);
            return res.json();
        },
        showToast(msg, type = 'success') {
            this.toast = { show:true, msg, type };
            setTimeout(() => this.toast.show = false, 3000);
        },
    };
}
</script>
@endsection
