<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicParticipantsController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search', '');

        // Subquery: última asignación nominal por programa (gestion*100+mes más alto)
        $latestAssignment = DB::table('monthly_assignments as ma')
            ->join(
                DB::raw('(
                    SELECT program_id, MAX(gestion * 100 + mes) as max_period
                    FROM monthly_assignments
                    GROUP BY program_id
                ) as latest_ma'),
                function ($join) {
                    $join->on('ma.program_id', '=', 'latest_ma.program_id')
                         ->whereRaw('(ma.gestion * 100 + ma.mes) = latest_ma.max_period');
                }
            )
            ->select('ma.id as monthly_assignment_id', 'ma.program_id as ma_program_id');

        $rows = DB::table('inscription_program as ip')
            ->join('inscriptions as i', 'i.id', '=', 'ip.inscription_id')
            ->join('programs as p', 'p.id', '=', 'ip.program_id')
            ->leftJoinSub($latestAssignment, 'lma', function ($join) {
                $join->on('lma.ma_program_id', '=', 'ip.program_id');
            })
            ->leftJoin('assignment_details as ad', function ($join) {
                $join->on('ad.monthly_assignment_id', '=', 'lma.monthly_assignment_id')
                     ->on('ad.inscription_id', '=', 'i.id');
            })
            ->where('i.external_inscription_status', '!=', 'Preinscrito')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('i.full_name', 'like', "%{$search}%")
                       ->orWhere('i.name', 'like', "%{$search}%")
                       ->orWhere('i.ci', 'like', "%{$search}%");
                });
            })
            ->select(
                'i.id as inscription_id',
                'i.full_name',
                'i.name',
                'i.paternal_surname',
                'i.maternal_surname',
                'i.ci',
                'i.extension',
                'i.participant_status',
                'i.participant_justification',
                'i.graduation_procedure_type',
                // Req. inscripción
                'i.has_degree_title',
                'i.has_academic_diploma',
                'i.has_identity_card',
                'i.has_birth_certificate',
                // Req. titulación
                'i.has_legalized_degree_title',
                'i.legalized_degree_title_date',
                'i.legalized_degree_title_obs',
                'i.has_legalized_academic_diploma',
                'i.legalized_academic_diploma_date',
                'i.legalized_academic_diploma_obs',
                'i.has_identity_card_graduation',
                'i.identity_card_graduation_date',
                'i.identity_card_graduation_obs',
                'i.has_birth_certificate_original',
                'i.birth_certificate_original_date',
                'i.birth_certificate_original_obs',
                'i.has_photos',
                'i.photos_date',
                'i.photos_obs',
                // Trabajo final - Diplomado
                'i.has_monograph_elaboration',
                'i.monograph_elaboration_date',
                'i.monograph_elaboration_obs',
                'i.has_monograph_received',
                'i.monograph_received_date',
                'i.monograph_received_obs',
                // Trabajo final - Maestría
                'i.has_degree_work_presentation',
                'i.degree_work_presentation_date',
                'i.degree_work_presentation_obs',
                'i.has_tutor_approval_report',
                'i.tutor_approval_report_date',
                'i.tutor_approval_report_obs',
                'i.has_pre_defense',
                'i.pre_defense_obs',
                'i.has_defense',
                'i.defense_obs',
                'i.has_defense_accounting_status',
                'i.defense_accounting_status_obs',
                // Estado de titulación
                'i.has_graduation_procedure',
                'i.graduation_procedure_date',
                'i.graduation_procedure_obs',
                'i.has_graduation_received',
                'i.graduation_received_date',
                'i.graduation_received_obs',
                'i.has_documents_delivered',
                'i.documents_delivered_date',
                'i.documents_delivered_obs',
                'i.has_diplomas_delivered',
                'i.diplomas_delivered_date',
                'i.diplomas_delivered_obs',
                // Programa
                'p.id as program_id',
                'p.name as program_name',
                'p.status as program_status',
                'p.code as program_code',
                // Estado contable desde asignación nominal (columnas reales)
                'ad.cuotas_retrasadas_importe',
                'ad.cuotas_retrasadas_cobrado',
                'ad.cuota_vigente_importe',
                'ad.cuota_vigente_cobrado',
                'ad.excluido as ad_excluido',
            )
            ->orderBy('i.paternal_surname')
            ->orderBy('i.name')
            ->orderBy('p.name')
            ->paginate(25)
            ->withQueryString();

        return view('academic.participants.index', compact('rows', 'search'));
    }
}
