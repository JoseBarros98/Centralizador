<?php

namespace App\Services;

use App\Models\AssignmentDetail;
use App\Models\Inscription;
use App\Models\MonthlyAssignment;
use App\Models\ParticipantQuota;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NominalAssignmentService
{
    /**
     * Genera la asignación mensual para un programa y período.
     */
    public function generate(Program $program, int $mes, int $gestion, int $userId): MonthlyAssignment
    {
        $existing = MonthlyAssignment::where('program_id', $program->id)
            ->where('mes', $mes)
            ->where('gestion', $gestion)
            ->first();

        if ($existing) {
            throw new \RuntimeException('Ya existe una asignación para este programa en el período seleccionado.');
        }

        $assignment = MonthlyAssignment::create([
            'program_id' => $program->id,
            'mes'        => $mes,
            'gestion'    => $gestion,
            'generado_por' => $userId,
            'estado'     => 'borrador',
        ]);

        $currentMonthStart = Carbon::createFromDate($gestion, $mes, 1)->startOfMonth();

        // Inscritos del programa: consulta directa al pivot + program_id directo (inscripciones locales)
        $pivotIds  = DB::table('inscription_program')
            ->where('program_id', $program->id)
            ->pluck('inscription_id');

        $directIds = Inscription::where('program_id', $program->id)->pluck('id');

        $allIds = $pivotIds->merge($directIds)->unique()->values();

        $inscriptions = Inscription::whereIn('id', $allIds)->get();

        foreach ($inscriptions as $inscription) {
            $data = $this->buildDetailData($assignment, $inscription, $currentMonthStart);
            AssignmentDetail::create($data);
        }

        return $assignment->load('details');
    }

    /**
     * Construye los datos de un detalle de asignación para un participante.
     */
    private function buildDetailData(MonthlyAssignment $assignment, Inscription $inscription, Carbon $currentMonthStart): array
    {
        $programId = $assignment->program_id;

        $allQuotas = ParticipantQuota::where('inscription_id', $inscription->id)
            ->where('program_id', $programId)
            ->orderBy('numero_cuota')
            ->get();

        // Detalles previos de este participante en este programa, ordenados cronológicamente
        $previousDetails = $this->getPreviousDetails($inscription->id, $programId, $assignment);

        [$retrasadaImporte, $retrasadaNumeros] = $this->calcularRetrasada(
            $allQuotas,
            $previousDetails,
            $currentMonthStart
        );

        [$vigenteNumero, $vigenteImporte] = $this->calcularVigente(
            $allQuotas,
            $previousDetails,
            $currentMonthStart
        );

        [$matriculaImporte, $certificacionImporte] = $this->calcularMatriculaCertificacion(
            $inscription->id,
            $programId,
            $previousDetails
        );

        $totalAsignacion = $retrasadaImporte + $vigenteImporte + $matriculaImporte + $certificacionImporte;

        return [
            'monthly_assignment_id'      => $assignment->id,
            'inscription_id'             => $inscription->id,
            'nombre_completo'            => $inscription->getFullName(),
            'plan_pago_nombre'           => $inscription->payment_plan ?? '',
            'telefono'                   => $inscription->phone ?? '',
            'cuotas_retrasadas_numeros'  => !empty($retrasadaNumeros) ? $retrasadaNumeros : null,
            'cuotas_retrasadas_importe'  => $retrasadaImporte,
            'cuotas_retrasadas_cobrado'  => 0,
            'cuota_vigente_numero'       => $vigenteNumero,
            'cuota_vigente_importe'      => $vigenteImporte,
            'cuota_vigente_cobrado'      => 0,
            'adelanto_numero_cuota'      => null,
            'adelanto_importe'           => 0,
            'matricula_importe'          => $matriculaImporte,
            'matricula_cobrado'          => 0,
            'certificacion_importe'      => $certificacionImporte,
            'certificacion_cobrado'      => 0,
            'total_asignacion'           => $totalAsignacion,
            'observaciones'              => null,
        ];
    }

    /**
     * Calcula el saldo acumulado de cuotas retrasadas.
     * Retorna [importe_total_retrasada, array_numeros_cuota].
     */
    private function calcularRetrasada(Collection $allQuotas, Collection $previousDetails, Carbon $currentMonthStart): array
    {
        $retrasadaImporte = 0.0;
        $retrasadaNumeros = [];

        if ($previousDetails->isEmpty()) {
            // Sin historial: verificar si hay cuotas vencidas antes del mes actual
            $pastDue = $allQuotas->filter(fn($q) =>
                Carbon::parse($q->fecha_vencimiento)->startOfMonth()->lt($currentMonthStart)
            );

            foreach ($pastDue as $quota) {
                $retrasadaImporte += (float) $quota->importe_final;
                $retrasadaNumeros[] = $quota->numero_cuota;
            }
        } else {
            $lastDetail  = $previousDetails->last();
            $lastMA      = $lastDetail->monthlyAssignment;
            $lastMonthStart = Carbon::createFromDate($lastMA->gestion, $lastMA->mes, 1)->startOfMonth();

            // Saldo retrasada del mes anterior
            $prevRetrasadaSaldo = max(0.0, (float)$lastDetail->cuotas_retrasadas_importe - (float)$lastDetail->cuotas_retrasadas_cobrado);
            $retrasadaImporte += $prevRetrasadaSaldo;
            $retrasadaNumeros  = $lastDetail->cuotas_retrasadas_numeros ?? [];

            // Si la cuota vigente anterior quedó con saldo, pasa a retrasada
            if ($lastDetail->cuota_vigente_numero !== null) {
                $prevVigenteSaldo = max(0.0, (float)$lastDetail->cuota_vigente_importe - (float)$lastDetail->cuota_vigente_cobrado);
                if ($prevVigenteSaldo > 0) {
                    $retrasadaImporte += $prevVigenteSaldo;
                    $retrasadaNumeros[] = $lastDetail->cuota_vigente_numero;
                }
            }

            // Cuotas que vencieron durante el hueco (meses sin asignación)
            $knownNums = array_unique(array_filter(array_merge(
                $retrasadaNumeros,
                [$lastDetail->cuota_vigente_numero]
            )));

            $gapQuotas = $allQuotas->filter(function ($q) use ($lastMonthStart, $currentMonthStart, $knownNums) {
                $venc = Carbon::parse($q->fecha_vencimiento)->startOfMonth();
                return $venc->gt($lastMonthStart)
                    && $venc->lt($currentMonthStart)
                    && !in_array($q->numero_cuota, $knownNums);
            });

            foreach ($gapQuotas as $gq) {
                $retrasadaImporte += (float) $gq->importe_final;
                $retrasadaNumeros[] = $gq->numero_cuota;
            }

            $retrasadaNumeros = array_values(array_unique($retrasadaNumeros));
            sort($retrasadaNumeros);
        }

        return [$retrasadaImporte, $retrasadaNumeros];
    }

    /**
     * Calcula la cuota vigente del mes actual, descontando adelantos aplicados.
     * Retorna [numero_cuota|null, importe].
     */
    private function calcularVigente(Collection $allQuotas, Collection $previousDetails, Carbon $currentMonthStart): array
    {
        $vigenteQuota = $allQuotas->first(function ($q) use ($currentMonthStart) {
            return Carbon::parse($q->fecha_vencimiento)->startOfMonth()->eq($currentMonthStart);
        });

        if (!$vigenteQuota) {
            return [null, 0.0];
        }

        $vigenteNumero  = $vigenteQuota->numero_cuota;
        $vigenteImporte = (float) $vigenteQuota->importe_final;

        // Restar adelantos ya aplicados a esta cuota en meses anteriores
        $advancePaid = $previousDetails->sum(function ($d) use ($vigenteNumero) {
            return $d->adelanto_numero_cuota == $vigenteNumero ? (float) $d->adelanto_importe : 0.0;
        });

        $vigenteImporte = max(0.0, $vigenteImporte - $advancePaid);

        return [$vigenteNumero, $vigenteImporte];
    }

    /**
     * Calcula los saldos pendientes de matrícula y certificación considerando pagos anteriores.
     * Retorna [matricula_importe, certificacion_importe].
     */
    private function calcularMatriculaCertificacion(int $inscriptionId, int $programId, Collection $previousDetails): array
    {
        // Obtener el plan de pago vinculado a las cuotas del participante
        $planQuota = ParticipantQuota::where('inscription_id', $inscriptionId)
            ->where('program_id', $programId)
            ->whereNotNull('payment_plan_id')
            ->with('paymentPlan')
            ->first();

        if (!$planQuota || !$planQuota->paymentPlan) {
            return [0.0, 0.0];
        }

        $plan = $planQuota->paymentPlan;

        $matriculaPaid      = $previousDetails->sum(fn($d) => (float) $d->matricula_cobrado);
        $certificacionPaid  = $previousDetails->sum(fn($d) => (float) $d->certificacion_cobrado);

        $matriculaImporte     = max(0.0, (float)$plan->matricula - $matriculaPaid);
        $certificacionImporte = max(0.0, (float)$plan->certificacion - $certificacionPaid);

        return [$matriculaImporte, $certificacionImporte];
    }

    /**
     * Obtiene los detalles previos de un participante en un programa, ordenados cronológicamente.
     */
    private function getPreviousDetails(int $inscriptionId, int $programId, MonthlyAssignment $current): Collection
    {
        return AssignmentDetail::whereHas('monthlyAssignment', fn($q) => $q->where('program_id', $programId))
            ->where('inscription_id', $inscriptionId)
            ->with('monthlyAssignment')
            ->get()
            ->filter(function ($d) use ($current) {
                $ma = $d->monthlyAssignment;
                return $ma->gestion < $current->gestion
                    || ($ma->gestion === $current->gestion && $ma->mes < $current->mes);
            })
            ->sortBy(fn($d) => $d->monthlyAssignment->gestion * 100 + $d->monthlyAssignment->mes)
            ->values();
    }
}
