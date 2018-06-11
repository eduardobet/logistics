<?php

namespace Logistics\Traits;

trait UserBranch
{
    public function currentBranch()
    {
        return $this->currentBranches()->first();
    }

    public function currentBranches()
    {
        return cache()->rememberForever("employee.branches", function () {
            return $this->branches->where('status', 'A');
        });
    }
}
