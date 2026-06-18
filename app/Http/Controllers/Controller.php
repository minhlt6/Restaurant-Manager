<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function currentEmployee(): ?array
    {
        return session('employee');
    }

    protected function currentEmployeeId(): ?int
    {
        return $this->currentEmployee()['id'] ?? null;
    }

    protected function isManager(): bool
    {
        return (int) ($this->currentEmployee()['role'] ?? 0) === 1;
    }
}
