<?php

namespace App\Observers;

use App\Mail\LeaveRequestStatusChanged;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestSubmitted;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Mail;

class LeaveRequestObserver
{
    /**
     * Employee maakt aanvraag:
     * - Managers krijgen mail
     * - Audit: leave_request.created
     */
    public function created(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->loadMissing(['employee', 'leaveType']);

        // ✅ Mail naar managers: "nieuwe aanvraag"
        $managers = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'manager'))
            ->get();

        foreach ($managers as $manager) {
            // Prevent bounces in dev als er example.com of lege emails zijn
            if (!$manager->email || str_ends_with($manager->email, '@example.com')) {
                continue;
            }
            $manager->notify(new LeaveRequestSubmitted($leaveRequest));
        }

        // ✅ Audit log
        $audit = app(AuditLogger::class);
        $audit->log(
            action: 'leave_request.created',
            auditable: $leaveRequest,
            oldValues: null,
            newValues: $this->safePayload($leaveRequest),
            logType: 'audit',
            description: 'Leave request created'
        );
    }

    /**
     * Updates:
     * - Status change (approved/rejected/canceled) -> mail naar employee + specifieke audit action
     * - Andere field changes -> leave_request.updated
     */
    public function updated(LeaveRequest $leaveRequest): void
    {
        $audit = app(AuditLogger::class);

        // ========== STATUS CHANGE ==========
        if ($leaveRequest->wasChanged('status')) {
            $oldStatus = (string) $leaveRequest->getOriginal('status');
            $newStatus = (string) $leaveRequest->status;

            // Specifieke audit action voor bekende statussen
            $action = match ($newStatus) {
                LeaveRequest::STATUS_APPROVED => 'leave_request.approved',
                LeaveRequest::STATUS_REJECTED => 'leave_request.rejected',
                LeaveRequest::STATUS_CANCELED => 'leave_request.canceled',
                default => 'leave_request.status_changed',
            };

            $audit->log(
                action: $action,
                auditable: $leaveRequest,
                oldValues: ['status' => $oldStatus],
                newValues: array_merge(['status' => $newStatus], $this->safePayload($leaveRequest)),
                logType: 'audit',
                description: 'Leave request status changed'
            );

            // ✅ Mail naar employee (aanvrager) als status één van de 3 eindstatussen is
            if (in_array($leaveRequest->status, [
                LeaveRequest::STATUS_APPROVED,
                LeaveRequest::STATUS_REJECTED,
                LeaveRequest::STATUS_CANCELED,
            ], true)) {
                $leaveRequest->loadMissing('employee');

                $email = $leaveRequest->employee?->email;
                if ($email) {
                    // Prevent bounces in dev als employee example.com heeft
                    if (!str_ends_with($email, '@example.com')) {
                        Mail::to($email)->send(new LeaveRequestStatusChanged($leaveRequest));
                    }
                }
            }
        }

        // ========== OTHER FIELD CHANGES (zonder status/updated_at ruis) ==========
        $changes = $leaveRequest->getChanges();
        unset($changes['updated_at'], $changes['status']);

        if (!empty($changes)) {
            // old values alleen voor changed keys
            $old = [];
            foreach (array_keys($changes) as $key) {
                $old[$key] = $leaveRequest->getOriginal($key);
            }

            $audit->log(
                action: 'leave_request.updated',
                auditable: $leaveRequest,
                oldValues: $this->safeArray($old),
                newValues: $this->safeArray($changes),
                logType: 'audit',
                description: 'Leave request updated'
            );
        }
    }

    /**
     * Delete -> audit
     */
    public function deleted(LeaveRequest $leaveRequest): void
    {
        $audit = app(AuditLogger::class);

        $audit->log(
            action: 'leave_request.deleted',
            auditable: $leaveRequest,
            oldValues: $this->safePayload($leaveRequest),
            newValues: null,
            logType: 'audit',
            description: 'Leave request deleted'
        );
    }

    /**
     * Soft delete restore -> audit (alleen als je soft deletes gebruikt)
     */
    public function restored(LeaveRequest $leaveRequest): void
    {
        $audit = app(AuditLogger::class);

        $audit->log(
            action: 'leave_request.restored',
            auditable: $leaveRequest,
            oldValues: null,
            newValues: $this->safePayload($leaveRequest),
            logType: 'audit',
            description: 'Leave request restored'
        );
    }

    /**
     * Permanent delete -> audit (alleen als je soft deletes gebruikt)
     */
    public function forceDeleted(LeaveRequest $leaveRequest): void
    {
        $audit = app(AuditLogger::class);

        $audit->log(
            action: 'leave_request.force_deleted',
            auditable: $leaveRequest,
            oldValues: $this->safePayload($leaveRequest),
            newValues: null,
            logType: 'audit',
            description: 'Leave request permanently deleted'
        );
    }

    /**
     * Payload voor audit: inclusief reason + proof (metadata/path).
     * Let op: we loggen geen file binary/base64, alleen pad/metadata.
     */
    private function safePayload(LeaveRequest $leaveRequest): array
    {
        $data = $leaveRequest->toArray();

        // ✅ Reden loggen (beperk lengte)
        if (array_key_exists('reason', $data) && is_string($data['reason'])) {
            $data['reason'] = mb_substr($data['reason'], 0, 2000);
        }

        // ✅ Proof / attachment metadata of path loggen (pas aan als jouw kolomnamen anders zijn)
        // Veel voorkomende velden:
        // - proof_path / proof_file / proof_name / proof_mime / proof_size
        // - attachment_path / attachment_file / attachment_name / attachment_mime / attachment_size
        // - document_path / evidence_path
        // We laten ze staan als ze bestaan; geen extra bewerking nodig.

        // Als proof als JSON-string wordt opgeslagen (bv. proof_files)
        if (array_key_exists('proof_files', $data) && is_string($data['proof_files'])) {
            $decoded = json_decode($data['proof_files'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['proof_files'] = $decoded;
            }
        }

        return $data;
    }

    /**
     * Zelfde sanitization voor old/new diffs.
     */
    private function safeArray(array $data): array
    {
        if (array_key_exists('reason', $data) && is_string($data['reason'])) {
            $data['reason'] = mb_substr($data['reason'], 0, 2000);
        }

        if (array_key_exists('proof_files', $data) && is_string($data['proof_files'])) {
            $decoded = json_decode($data['proof_files'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['proof_files'] = $decoded;
            }
        }

        return $data;
    }
}
