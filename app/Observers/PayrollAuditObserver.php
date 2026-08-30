<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\Payroll\PayrollAuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class PayrollAuditObserver
{
    public function __construct(
        private readonly PayrollAuditService $auditService
    ) {}

    public function created(Model $model): void
    {
        $this->auditService->recordModelChange(
            $model,
            'created',
            null,
            Arr::except($model->getAttributes(), ['created_at', 'updated_at'])
        );
    }

    public function updated(Model $model): void
    {
        $changes = Arr::except($model->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getOriginal($key);
        }

        $this->auditService->recordModelChange(
            $model,
            'updated',
            $old,
            $changes
        );
    }

    public function deleted(Model $model): void
    {
        $this->auditService->recordModelChange(
            $model,
            'deleted',
            Arr::except($model->getAttributes(), ['created_at', 'updated_at']),
            null
        );
    }
}
