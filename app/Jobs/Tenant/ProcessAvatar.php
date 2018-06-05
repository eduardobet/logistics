<?php

namespace Logistics\Jobs\Tenant;

use Logistics\DB\User;
use Illuminate\Bus\Queueable;
use Intervention\Image\Facades\Image;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $employee;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $employee)
    {
        $this->employee = $employee;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->employee->avatar) {
            $imageContents = Storage::disk('public')->get($this->employee->avatar);
            
            list($width) = getimagesizefromstring($imageContents);
            
            if ($width > 200) {
                $image = Image::make($imageContents)
                ->resize(200, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->limitColors(255)
                ->encode();
                
                Storage::disk('public')->put($this->employee->avatar, (string)$image);
            }
        }
    }
}
